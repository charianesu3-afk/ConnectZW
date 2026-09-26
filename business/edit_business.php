<?php

session_start();

include "../includes/config.php";

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

$user_id = (int)$_SESSION['user_id'];

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
    die("Business not found.");
}

$id = (int)$_GET['id'];

$message = "";
$message_type = "";

// ==========================================
// GET BUSINESS
// ==========================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM businesses
     WHERE id = ?
     AND owner_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $id,
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    die("You do not have permission to edit this business.");
}

$business = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// ==========================================
// DEFAULT FORM VALUES
// ==========================================

$business_name = $business['business_name'];
$category_id   = $business['category_id'];
$phone         = $business['phone'];
$location      = $business['location'];
$description   = $business['description'];

// ==========================================
// UPDATE BUSINESS
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

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

    // --------------------------------------
    // GET FORM DATA
    // --------------------------------------

    $business_name = trim($_POST['business_name'] ?? '');
    $category_id   = (int)($_POST['category_id'] ?? 0);
    $phone         = trim($_POST['phone'] ?? '');
    $location      = trim($_POST['location'] ?? '');
    $description   = trim($_POST['description'] ?? '');

    // --------------------------------------
    // REQUIRED FIELDS
    // --------------------------------------

    if (
        $business_name === "" ||
        $category_id <= 0 ||
        $phone === "" ||
        $location === ""
    ) {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } else {

        // --------------------------------------
        // CHECK CATEGORY
        // --------------------------------------

        $category_stmt = mysqli_prepare(
            $conn,
            "SELECT id
             FROM categories
             WHERE id = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $category_stmt,
            "i",
            $category_id
        );

        mysqli_stmt_execute($category_stmt);

        $category_result = mysqli_stmt_get_result($category_stmt);

        if (mysqli_num_rows($category_result) === 0) {

            $message = "Please select a valid category.";
            $message_type = "error";

        } else {

            // --------------------------------------
            // UPDATE DATABASE
            // --------------------------------------

            $update_stmt = mysqli_prepare(
                $conn,
                "UPDATE businesses
                 SET
                    business_name = ?,
                    category_id = ?,
                    phone = ?,
                    location = ?,
                    description = ?
                 WHERE id = ?
                 AND owner_id = ?"
            );

            if ($update_stmt) {

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "sisssii",
                    $business_name,
                    $category_id,
                    $phone,
                    $location,
                    $description,
                    $id,
                    $user_id
                );

                if (mysqli_stmt_execute($update_stmt)) {

                    $message = "Business updated successfully.";
                    $message_type = "success";

                    // Update local data
                    $business['business_name'] = $business_name;
                    $business['category_id'] = $category_id;
                    $business['phone'] = $phone;
                    $business['location'] = $location;
                    $business['description'] = $description;

                } else {

                    $message = "Unable to update the business. Please try again.";
                    $message_type = "error";
                }

                mysqli_stmt_close($update_stmt);

            } else {

                $message = "A system error occurred.";
                $message_type = "error";
            }
        }

        mysqli_stmt_close($category_stmt);
    }
}

// ==========================================
// GET CATEGORIES
// ==========================================

$categories = mysqli_query(
    $conn,
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Business | ConnectZW</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<?php include "../includes/navbar.php"; ?>

<section class="search-page">

    <div class="container">

        <div class="card">

            <h1>Edit Business</h1>

            <p>
                Update your business information below.
            </p>

            <!-- MESSAGE -->

            <?php if ($message !== "") { ?>

                <div
                    style="
                        padding:15px;
                        margin:20px 0;
                        border-radius:10px;
                        <?php
                        if ($message_type === 'success') {
                            echo 'background:#dcfce7;color:#166534;';
                        } else {
                            echo 'background:#fee2e2;color:#991b1b;';
                        }
                        ?>
                    "
                >

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php } ?>

            <!-- FORM -->

            <form method="POST">

                <!-- CSRF TOKEN -->

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
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

                <!-- BUTTONS -->

                <button
                    type="submit"
                    name="update"
                >
                    Save Changes
                </button>

                <a
                    href="business_dashboard.php"
                    style="
                        display:inline-block;
                        margin-left:10px;
                    "
                >
                    Cancel
                </a>

            </form>

        </div>

    </div>

</section>

<?php include "../includes/footer.php"; ?>

</body>

</html>