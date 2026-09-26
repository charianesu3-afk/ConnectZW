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

// ==========================================
// ONLY POST REQUESTS
// ==========================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: business_dashboard.php");
    exit();
}

// ==========================================
// CHECK CSRF TOKEN
// ==========================================

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

// ==========================================
// CHECK BUSINESS ID
// ==========================================

if (
    !isset($_POST['business_id']) ||
    !is_numeric($_POST['business_id'])
) {
    header("Location: business_dashboard.php");
    exit();
}

$business_id = intval($_POST['business_id']);
$owner_id = $_SESSION['user_id'];


// ==========================================
// GET BUSINESS + VERIFY OWNERSHIP
// ==========================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, logo
     FROM businesses
     WHERE id = ?
     AND owner_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $business_id,
    $owner_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {

    mysqli_stmt_close($stmt);

    header("Location: business_dashboard.php");
    exit();
}

$business = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


// ==========================================
// GET BUSINESS GALLERY IMAGES
// ==========================================

$image_files = [];

$image_stmt = mysqli_prepare(
    $conn,
    "SELECT image
     FROM business_images
     WHERE business_id = ?"
);

mysqli_stmt_bind_param(
    $image_stmt,
    "i",
    $business_id
);

mysqli_stmt_execute($image_stmt);

$image_result = mysqli_stmt_get_result($image_stmt);

while ($image = mysqli_fetch_assoc($image_result)) {

    if (!empty($image['image'])) {
        $image_files[] = basename($image['image']);
    }
}

mysqli_stmt_close($image_stmt);


// ==========================================
// START DATABASE TRANSACTION
// ==========================================

mysqli_begin_transaction($conn);

try {

    // ======================================
    // DELETE BUSINESS IMAGES
    // ======================================

    $delete_images = mysqli_prepare(
        $conn,
        "DELETE FROM business_images
         WHERE business_id = ?"
    );

    if (!$delete_images) {
        throw new Exception(
            "Could not prepare image deletion."
        );
    }

    mysqli_stmt_bind_param(
        $delete_images,
        "i",
        $business_id
    );

    if (!mysqli_stmt_execute($delete_images)) {
        mysqli_stmt_close($delete_images);

        throw new Exception(
            "Could not delete business images."
        );
    }

    mysqli_stmt_close($delete_images);


    // ======================================
    // DELETE REVIEWS
    // ======================================

    $delete_reviews = mysqli_prepare(
        $conn,
        "DELETE FROM reviews
         WHERE business_id = ?"
    );

    if (!$delete_reviews) {
        throw new Exception(
            "Could not prepare review deletion."
        );
    }

    mysqli_stmt_bind_param(
        $delete_reviews,
        "i",
        $business_id
    );

    if (!mysqli_stmt_execute($delete_reviews)) {
        mysqli_stmt_close($delete_reviews);

        throw new Exception(
            "Could not delete business reviews."
        );
    }

    mysqli_stmt_close($delete_reviews);


    // ======================================
    // DELETE FAVORITES
    // ======================================

    $delete_favorites = mysqli_prepare(
        $conn,
        "DELETE FROM favorites
         WHERE business_id = ?"
    );

    if (!$delete_favorites) {
        throw new Exception(
            "Could not prepare favorite deletion."
        );
    }

    mysqli_stmt_bind_param(
        $delete_favorites,
        "i",
        $business_id
    );

    if (!mysqli_stmt_execute($delete_favorites)) {
        mysqli_stmt_close($delete_favorites);

        throw new Exception(
            "Could not delete business favorites."
        );
    }

    mysqli_stmt_close($delete_favorites);


    // ======================================
    // DELETE BUSINESS
    // ======================================

    $delete_business = mysqli_prepare(
        $conn,
        "DELETE FROM businesses
         WHERE id = ?
         AND owner_id = ?"
    );

    if (!$delete_business) {
        throw new Exception(
            "Could not prepare business deletion."
        );
    }

    mysqli_stmt_bind_param(
        $delete_business,
        "ii",
        $business_id,
        $owner_id
    );

    if (!mysqli_stmt_execute($delete_business)) {
        mysqli_stmt_close($delete_business);

        throw new Exception(
            "Could not delete the business."
        );
    }

    // Make sure a business was actually deleted
    if (mysqli_stmt_affected_rows($delete_business) !== 1) {

        mysqli_stmt_close($delete_business);

        throw new Exception(
            "Business could not be deleted."
        );
    }

    mysqli_stmt_close($delete_business);


    // ======================================
    // COMMIT
    // ======================================

    mysqli_commit($conn);


    // ======================================
    // DELETE GALLERY FILES
    // ======================================

    foreach ($image_files as $image_name) {

        $image_path = "../uploads/" . $image_name;

        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }


    // ======================================
    // DELETE BUSINESS LOGO
    // ======================================

    if (!empty($business['logo'])) {

        $logo_path =
            "../uploads/" .
            basename($business['logo']);

        if (file_exists($logo_path)) {
            unlink($logo_path);
        }
    }


    // ======================================
    // REDIRECT
    // ======================================

    header(
        "Location: business_dashboard.php?deleted=1"
    );

    exit();


} catch (Exception $e) {

    // ======================================
    // ROLLBACK IF SOMETHING FAILS
    // ======================================

    mysqli_rollback($conn);

    header(
        "Location: business_dashboard.php?error=delete_failed"
    );

    exit();
}

?>