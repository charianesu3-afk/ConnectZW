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

$user_id = (int) $_SESSION['user_id'];

// ==========================================
// CREATE CSRF TOKEN
// ==========================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==========================================
// ONLY ALLOW POST
// ==========================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../business/view_businesses.php");
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
    header("Location: ../business/view_businesses.php");
    exit();
}

$business_id = (int) $_POST['business_id'];

if ($business_id <= 0) {
    header("Location: ../business/view_businesses.php");
    exit();
}

// ==========================================
// CHECK BUSINESS EXISTS
// ==========================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM businesses
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $business_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {

    mysqli_stmt_close($stmt);

    header("Location: ../business/view_businesses.php");
    exit();
}

mysqli_stmt_close($stmt);

// ==========================================
// CHECK IF ALREADY FAVORITED
// ==========================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT id
     FROM favorites
     WHERE user_id = ?
     AND business_id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $user_id,
    $business_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$already_favorite = mysqli_num_rows($result) > 0;

mysqli_stmt_close($stmt);

// ==========================================
// ADD FAVORITE
// ==========================================

if (!$already_favorite) {

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO favorites
        (user_id, business_id)
        VALUES (?, ?)"
    );

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "ii",
            $user_id,
            $business_id
        );

        mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
    }
}

// ==========================================
// RETURN TO BUSINESS PROFILE
// ==========================================

header(
    "Location: ../business/view_business.php?id=" . $business_id
);

exit();

?>