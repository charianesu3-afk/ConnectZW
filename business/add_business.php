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

$owner_id = $_SESSION['user_id'];
$message = "";
$message_type = "";

$business_name = "";
$phone = "";
$location = "";
$description = "";
$category_id = "";

// ==========================================
// ADD BUSINESS
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_business'])) {

    // --------------------------------------
    // GET FORM DATA
    // --------------------------------------

    $business_name = trim($_POST['business_name'] ?? '');
    $category_id   = intval($_POST['category_id'] ?? 0);
    $phone         = trim($_POST['phone'] ?? '');
    $location      = trim($_POST['location'] ?? '');
    $description   = trim($_POST['description'] ?? '');

    // --------------------------------------
    // REQUIRED FIELD VALIDATION
    // --------------------------------------

    if ($business_name === "" || $category_id <= 0 || $phone === "" || $location === "") {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } else {

        // --------------------------------------
        // CHECK CATEGORY EXISTS
        // --------------------------------------

        $category_stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM categories WHERE id = ? LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $category_stmt,
            "i",
            $category_id
        );

        mysqli_stmt_execute($category_stmt);

        $category_result = mysqli_stmt_get_result($category_stmt);

        if (mysqli_num_rows($category_result) === 0) {

            $message = "Please select a valid business category.";
            $message_type = "error";

        } else {

            // --------------------------------------
            // IMAGE VARIABLES
            // --------------------------------------

            $logo = "";
            $upload_success = true;

            // --------------------------------------
            // IMAGE UPLOAD
            // --------------------------------------

            if (
                isset($_FILES['logo']) &&
                $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE
            ) {

                $file = $_FILES['logo'];

                // Check upload error
                if ($file['error'] !== UPLOAD_ERR_OK) {

                    $message = "There was a problem uploading the image.";
                    $message_type = "error";
                    $upload_success = false;

                // Maximum 2MB
                } elseif ($file['size'] > 2 * 1024 * 1024) {

                    $message = "Logo must be less than 2MB.";
                    $message_type = "error";
                    $upload_success = false;

                } else {

                    // --------------------------------------
                    // CHECK ACTUAL IMAGE TYPE
                    // --------------------------------------

                    $image_info = @getimagesize($file['tmp_name']);

                    if ($image_info === false) {

                        $message = "Please upload a valid image.";
                        $message_type = "error";
                        $upload_success = false;

                    } else {

                        $allowed_types = [
                            IMAGETYPE_JPEG,
                            IMAGETYPE_PNG,
                            IMAGETYPE_WEBP
                        ];

                        if (!in_array($image_info[2], $allowed_types, true)) {

                            $message = "Only JPG, PNG and WEBP images are allowed.";
                            $message_type = "error";
                            $upload_success = false;

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
                                $upload_success = false;

                            } else {

                                // --------------------------------------
                                // RANDOM FILE NAME
                                // --------------------------------------

                                try {

                                    $new_name =
                                        bin2hex(random_bytes(16))
                                        . "."
                                        . $extension;

                                } catch (Exception $e) {

                                    $message = "Could not generate a secure image name.";
                                    $message_type = "error";
                                    $upload_success = false;
                                }

                                if ($upload_success) {

                                    $upload_path = "../uploads/" . $new_name;

                                    if (
                                        move_uploaded_file(
                                            $file['tmp_name'],
                                            $upload_path
                                        )
                                    ) {

                                        $logo = $new_name;

                                    } else {

                                        $message = "Failed to save the uploaded image.";
                                        $message_type = "error";
                                        $upload_success = false;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // --------------------------------------
            // INSERT BUSINESS
            // --------------------------------------

            if ($upload_success && $message === "") {

                $sql = "
                    INSERT INTO businesses
                    (
                        owner_id,
                        business_name,
                        category_id,
                        phone,
                        location,
                        description,
                        logo
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?)
                ";

                $stmt = mysqli_prepare($conn, $sql);

                if ($stmt) {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "isissss",
                        $owner_id,
                        $business_name,
                        $category_id,
                        $phone,
                        $location,
                        $description,
                        $logo
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        mysqli_stmt_close($stmt);
                        mysqli_stmt_close($category_stmt);

                        header("Location: business_dashboard.php");
                        exit();

                    } else {

                        // Delete uploaded image if database insert fails
                        if (
                            $logo !== "" &&
                            file_exists("../uploads/" . $logo)
                        ) {
                            unlink("../uploads/" . $logo);
                        }

                        $message = "Unable to create your business profile. Please try again.";
                        $message_type = "error";
                    }

                    mysqli_stmt_close($stmt);

                } else {

                    // Delete uploaded image if statement preparation fails
                    if (
                        $logo !== "" &&
                        file_exists("../uploads/" . $logo)
                    ) {
                        unlink("../uploads/" . $logo);
                    }

                    $message = "A system error occurred. Please try again.";
                    $message_type = "error";
                }
            }
        }

        mysqli_stmt_close($category_stmt);
    }
}

// ==========================================
// HEADER
// ==========================================

include '../includes/header.php';
include '../includes/navbar.php';

?>

<section class="search-page">

    <div class="container">

        <div class="card">

            <h1>Register Your Business</h1>

            <p>
                Create your ConnectZW business profile and help customers
                discover your business.
            </p>

            <?php if ($message !== "") { ?>

                <div
                    style="
                        padding:15px;
                        margin:20px 0;
                        border-radius:10px;
                        background:#fee2e2;
                        color:#991b1b;
                    "
                >
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php } ?>

            <form
                method="POST"
                enctype="multipart/form-data"
            >

                <!-- BUSINESS NAME -->

                <label for="business_name">
                    Business Name
                </label>

                <input
                    type="text"
                    id="business_name"
                    name="business_name"
                    value="<?php echo htmlspecialchars($business_name); ?>"
                    placeholder="Enter your business name"
                    maxlength="150"
                    required
                >

                <br><br>


                <!-- CATEGORY -->

                <label for="category_id">
                    Business Category
                </label>

                <select
                    id="category_id"
                    name="category_id"
                    required
                >

                    <option value="">
                        Select a category
                    </option>

                    <?php

                    $categories = mysqli_query(
                        $conn,
                        "SELECT id, category_name
                         FROM categories
                         ORDER BY category_name ASC"
                    );

                    if ($categories) {

                        while ($cat = mysqli_fetch_assoc($categories)) {

                    ?>

                        <option
                            value="<?php echo (int)$cat['id']; ?>"
                            <?php
                            echo (
                                $category_id == $cat['id']
                            ) ? 'selected' : '';
                            ?>
                        >
                            <?php
                            echo htmlspecialchars(
                                $cat['category_name']
                            );
                            ?>
                        </option>

                    <?php

                        }

                    }

                    ?>

                </select>

                <br><br>


                <!-- PHONE -->

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="<?php echo htmlspecialchars($phone); ?>"
                    placeholder="e.g. 0771234567"
                    maxlength="30"
                    required
                >

                <br><br>


                <!-- LOCATION -->

                <label for="location">
                    Business Location
                </label>

                <input
                    type="text"
                    id="location"
                    name="location"
                    value="<?php echo htmlspecialchars($location); ?>"
                    placeholder="e.g. Kadoma, Zimbabwe"
                    maxlength="200"
                    required
                >

                <br><br>


                <!-- DESCRIPTION -->

                <label for="description">
                    Business Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    maxlength="2000"
                    placeholder="Tell customers about your business..."
                ><?php echo htmlspecialchars($description); ?></textarea>

                <br><br>


                <!-- LOGO -->

                <label for="logo">
                    Business Logo / Main Image
                </label>

                <input
                    type="file"
                    id="logo"
                    name="logo"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    JPG, PNG or WEBP. Maximum size: 2MB.
                </small>

                <br><br>


                <!-- SUBMIT -->

                <button
                    type="submit"
                    name="add_business"
                >
                    Create Business Profile
                </button>

            </form>

        </div>

    </div>

</section>

<?php include '../includes/footer.php'; ?>