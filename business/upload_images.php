<?php

session_start();

include '../includes/config.php';

// ==========================================
// CHECK LOGIN
// ==========================================

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// ==========================================
// ONLY BUSINESS USERS
// ==========================================

if ($_SESSION['role'] !== "business") {
    header("Location: ../index.php");
    exit();
}

$owner_id = (int)$_SESSION['user_id'];

// ==========================================
// CREATE CSRF TOKEN
// ==========================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==========================================
// CHECK BUSINESS ID
// ==========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid business.");
}

$business_id = (int)$_GET['id'];

$message = "";
$message_type = "";

// ==========================================
// CHECK BUSINESS OWNERSHIP
// ==========================================

$check = mysqli_prepare(
    $conn,
    "SELECT id, business_name
     FROM businesses
     WHERE id = ?
     AND owner_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $check,
    "ii",
    $business_id,
    $owner_id
);

mysqli_stmt_execute($check);

$result = mysqli_stmt_get_result($check);

if (mysqli_num_rows($result) === 0) {

    mysqli_stmt_close($check);

    die("Access denied.");
}

$business = mysqli_fetch_assoc($result);

mysqli_stmt_close($check);

// ==========================================
// UPLOAD IMAGE
// ==========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['upload'])
) {

    // --------------------------------------
    // CHECK CSRF TOKEN
    // --------------------------------------

    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {
        die("Invalid security token.");
    }

    if (
        !isset($_FILES['image']) ||
        $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE
    ) {

        $message = "Please select an image.";
        $message_type = "error";

    } else {

        $file = $_FILES['image'];

        // --------------------------------------
        // UPLOAD ERROR
        // --------------------------------------

        if ($file['error'] !== UPLOAD_ERR_OK) {

            $message = "There was a problem uploading the image.";
            $message_type = "error";

        // --------------------------------------
        // FILE SIZE
        // --------------------------------------

        } elseif ($file['size'] > 5 * 1024 * 1024) {

            $message = "Image must be less than 5MB.";
            $message_type = "error";

        } else {

            // --------------------------------------
            // CHECK ACTUAL IMAGE
            // --------------------------------------

            $image_info = @getimagesize($file['tmp_name']);

            if ($image_info === false) {

                $message = "Please upload a valid image.";
                $message_type = "error";

            } else {

                $allowed_types = [
                    IMAGETYPE_JPEG,
                    IMAGETYPE_PNG,
                    IMAGETYPE_WEBP
                ];

                if (!in_array($image_info[2], $allowed_types, true)) {

                    $message = "Only JPG, PNG and WEBP images are allowed.";
                    $message_type = "error";

                } else {

                    // --------------------------------------
                    // DETERMINE EXTENSION
                    // --------------------------------------

                    switch ($image_info[2]) {

                        case IMAGETYPE_JPEG:
                            $extension = "jpg";
                            break;

                        case IMAGETYPE_PNG:
                            $extension = "png";
                            break;

                        case IMAGETYPE_WEBP:
                            $extension = "webp";
                            break;

                        default:
                            $extension = "";
                    }

                    if ($extension === "") {

                        $message = "Invalid image format.";
                        $message_type = "error";

                    } else {

                        // --------------------------------------
                        // RANDOM FILE NAME
                        // --------------------------------------

                        try {

                            $new_name =
                                bin2hex(random_bytes(16))
                                . "." . $extension;

                        } catch (Exception $e) {

                            $message = "Could not create a secure file name.";
                            $message_type = "error";

                            $new_name = "";
                        }

                        if ($new_name !== "") {

                            $upload_path = "../uploads/" . $new_name;

                            // --------------------------------------
                            // MOVE IMAGE
                            // --------------------------------------

                            if (
                                move_uploaded_file(
                                    $file['tmp_name'],
                                    $upload_path
                                )
                            ) {

                                // --------------------------------------
                                // SAVE IMAGE IN DATABASE
                                // --------------------------------------

                                $stmt = mysqli_prepare(
                                    $conn,
                                    "INSERT INTO business_images
                                    (business_id, image)
                                    VALUES (?, ?)"
                                );

                                if ($stmt) {

                                    mysqli_stmt_bind_param(
                                        $stmt,
                                        "is",
                                        $business_id,
                                        $new_name
                                    );

                                    if (mysqli_stmt_execute($stmt)) {

                                        mysqli_stmt_close($stmt);

                                        header(
                                            "Location: upload_images.php?id="
                                            . $business_id
                                            . "&success=1"
                                        );

                                        exit();

                                    } else {

                                        if (file_exists($upload_path)) {
                                            unlink($upload_path);
                                        }

                                        $message =
                                            "Could not save image information.";

                                        $message_type = "error";
                                    }

                                    mysqli_stmt_close($stmt);

                                } else {

                                    if (file_exists($upload_path)) {
                                        unlink($upload_path);
                                    }

                                    $message =
                                        "Could not prepare image information.";

                                    $message_type = "error";
                                }

                            } else {

                                $message = "Failed to upload image.";
                                $message_type = "error";
                            }
                        }
                    }
                }
            }
        }
    }
}

// ==========================================
// DELETE IMAGE
// ==========================================

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['delete_image'])
) {

    // --------------------------------------
    // CHECK CSRF TOKEN
    // --------------------------------------

    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {
        die("Invalid security token.");
    }

    $image_id = (int)($_POST['image_id'] ?? 0);

    if ($image_id <= 0) {

        $message = "Invalid image.";
        $message_type = "error";

    } else {

        // --------------------------------------
        // GET IMAGE
        // --------------------------------------

        $image_stmt = mysqli_prepare(
            $conn,
            "SELECT bi.image
             FROM business_images bi
             INNER JOIN businesses b
                 ON bi.business_id = b.id
             WHERE bi.id = ?
             AND bi.business_id = ?
             AND b.owner_id = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $image_stmt,
            "iii",
            $image_id,
            $business_id,
            $owner_id
        );

        mysqli_stmt_execute($image_stmt);

        $image_result = mysqli_stmt_get_result($image_stmt);

        if (mysqli_num_rows($image_result) === 0) {

            $message = "Image not found.";
            $message_type = "error";

        } else {

            $image_data = mysqli_fetch_assoc($image_result);

            $image_name = $image_data['image'];

            // --------------------------------------
            // DELETE DATABASE RECORD
            // --------------------------------------

            $delete_stmt = mysqli_prepare(
                $conn,
                "DELETE FROM business_images
                 WHERE id = ?
                 AND business_id = ?"
            );

            mysqli_stmt_bind_param(
                $delete_stmt,
                "ii",
                $image_id,
                $business_id
            );

            if (mysqli_stmt_execute($delete_stmt)) {

                // Delete physical image
                $image_path = "../uploads/" . $image_name;

                if (file_exists($image_path)) {
                    unlink($image_path);
                }

                $message = "Image deleted successfully.";
                $message_type = "success";

            } else {

                $message = "Could not delete the image.";
                $message_type = "error";
            }

            mysqli_stmt_close($delete_stmt);
        }

        mysqli_stmt_close($image_stmt);
    }
}

// ==========================================
// SUCCESS MESSAGE
// ==========================================

if (isset($_GET['success'])) {
    $message = "Image uploaded successfully.";
    $message_type = "success";
}

// ==========================================
// FETCH BUSINESS IMAGES
// ==========================================

$images_query = mysqli_prepare(
    $conn,
    "SELECT id, image
     FROM business_images
     WHERE business_id = ?
     ORDER BY id DESC"
);

mysqli_stmt_bind_param(
    $images_query,
    "i",
    $business_id
);

mysqli_stmt_execute($images_query);

$images_result = mysqli_stmt_get_result($images_query);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Business Photos | ConnectZW
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .upload-container {
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
        }

        .upload-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .upload-box h1 {
            margin-bottom: 10px;
        }

        .upload-description {
            color: #6b7280;
            margin-bottom: 25px;
        }

        .message {
            padding: 14px 16px;
            margin: 20px 0;
            border-radius: 10px;
        }

        .message.success {
            background: #dcfce7;
            color: #166534;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .upload-form {
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 35px;
        }

        .upload-form input[type="file"] {
            width: 100%;
            margin: 10px 0 15px;
        }

        .upload-form small {
            display: block;
            color: #6b7280;
            margin-bottom: 15px;
        }

        .gallery {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .gallery-item {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0,0,0,0.10);
        }

        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .gallery-actions {
            padding: 12px;
        }

        .delete-button {
            width: 100%;
            background: #dc2626;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
        }

        .delete-button:hover {
            background: #b91c1c;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
        }

        @media (max-width: 600px) {

            .upload-container {
                margin: 20px auto;
                padding: 10px;
            }

            .upload-box {
                padding: 20px;
            }

        }

    </style>

</head>

<body>

<?php include '../includes/navbar.php'; ?>

<section class="search-page">

    <div class="upload-container">

        <div class="upload-box">

            <a
                href="business_dashboard.php"
                class="back-link"
            >
                ← Back to Dashboard
            </a>

            <h1>
                Business Photos
            </h1>

            <p class="upload-description">

                Manage photos for
                <strong>
                    <?php
                    echo htmlspecialchars(
                        $business['business_name']
                    );
                    ?>
                </strong>

            </p>

            <!-- MESSAGE -->

            <?php if ($message !== "") { ?>

                <div
                    class="message
                    <?php
                    echo $message_type === 'error'
                        ? 'error'
                        : 'success';
                    ?>"
                >

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php } ?>

            <!-- UPLOAD FORM -->

            <div class="upload-form">

                <h2>
                    Add a Business Photo
                </h2>

                <p>
                    Upload photos that help customers see
                    your business, products or services.
                </p>

                <form
                    method="POST"
                    enctype="multipart/form-data"
                >

                    <!-- CSRF TOKEN -->

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php
                        echo htmlspecialchars(
                            $_SESSION['csrf_token']
                        );
                        ?>"
                    >

                    <label for="image">
                        Select Photo
                    </label>

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                        required
                    >

                    <small>
                        JPG, PNG or WEBP. Maximum size: 5MB.
                    </small>

                    <button
                        type="submit"
                        name="upload"
                    >
                        Upload Photo
                    </button>

                </form>

            </div>

            <!-- GALLERY -->

            <h2>
                Your Business Photos
            </h2>

            <div class="gallery">

                <?php if (mysqli_num_rows($images_result) > 0) { ?>

                    <?php while (
                        $image = mysqli_fetch_assoc($images_result)
                    ) { ?>

                        <div class="gallery-item">

                            <img
                                src="../uploads/<?php
                                echo htmlspecialchars(
                                    $image['image']
                                );
                                ?>"
                                alt="Business Photo"
                            >

                            <div class="gallery-actions">

                                <form
                                    method="POST"
                                    onsubmit="
                                        return confirm(
                                            'Are you sure you want to delete this photo?'
                                        );
                                    "
                                >

                                    <!-- CSRF TOKEN -->

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php
                                        echo htmlspecialchars(
                                            $_SESSION['csrf_token']
                                        );
                                        ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="image_id"
                                        value="<?php
                                        echo (int)$image['id'];
                                        ?>"
                                    >

                                    <button
                                        type="submit"
                                        name="delete_image"
                                        class="delete-button"
                                    >
                                        Delete Photo
                                    </button>

                                </form>

                            </div>

                        </div>

                    <?php } ?>

                <?php } else { ?>

                    <p>
                        You have not uploaded any business
                        photos yet.
                    </p>

                <?php } ?>

            </div>

        </div>

    </div>

</section>

<?php include '../includes/footer.php'; ?>

</body>

</html>