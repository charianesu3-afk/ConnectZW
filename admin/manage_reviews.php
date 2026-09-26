<?php

session_start();

include "../includes/config.php";


// ========================================
// PROTECT PAGE
// ========================================

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}


// ========================================
// CHECK ADMIN ROLE
// ========================================

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "admin") {
    header("Location: ../index.php");
    exit();
}


// ========================================
// CREATE CSRF TOKEN
// ========================================

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// ========================================
// DELETE REVIEW
// ========================================

if (isset($_POST['delete_review'])) {

    // Check CSRF token
    if (
        !isset($_POST['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {
        die("Invalid request.");
    }


    // Validate review ID
    $id = filter_input(
        INPUT_POST,
        'review_id',
        FILTER_VALIDATE_INT
    );


    if (!$id || $id <= 0) {
        die("Invalid review ID.");
    }


    // ========================================
    // CHECK REVIEW EXISTS
    // ========================================

    $check_stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM reviews WHERE id = ?"
    );


    if (!$check_stmt) {
        die("Database error.");
    }


    mysqli_stmt_bind_param(
        $check_stmt,
        "i",
        $id
    );


    mysqli_stmt_execute($check_stmt);

    $result = mysqli_stmt_get_result($check_stmt);

    $review = mysqli_fetch_assoc($result);

    mysqli_stmt_close($check_stmt);


    if (!$review) {
        die("Review not found.");
    }


    // ========================================
    // DELETE REVIEW SECURELY
    // ========================================

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM reviews WHERE id = ?"
    );


    if (!$stmt) {
        die("Database error.");
    }


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );


    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        die("Unable to delete review.");
    }


    mysqli_stmt_close($stmt);


    // Redirect after successful deletion
    header("Location: manage_reviews.php");
    exit();
}


// ========================================
// FETCH REVIEWS
// ========================================

$query = mysqli_query(
    $conn,
    "
    SELECT
        reviews.id,
        reviews.rating,
        reviews.comment,
        users.name,
        businesses.business_name
    FROM reviews
    LEFT JOIN users
        ON reviews.user_id = users.id
    LEFT JOIN businesses
        ON reviews.business_id = businesses.id
    ORDER BY reviews.id DESC
    "
);


if (!$query) {
    die("Unable to load reviews.");
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Reviews</title>

<link rel="stylesheet" href="admin.css">

<style>

table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    margin-top: 20px;
}

th,
td {
    padding: 12px;
    border: 1px solid #ddd;
    text-align: left;
}

th {
    background: #0b5ed7;
    color: white;
}

.delete {
    color: red;
    font-weight: bold;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    font-size: 15px;
}

</style>

</head>

<body>

<?php include "sidebar.php"; ?>

<div class="content">

<h2>Manage Reviews</h2>

<table>

<tr>

<th>ID</th>

<th>User</th>

<th>Business</th>

<th>Rating</th>

<th>Review</th>

<th>Action</th>

</tr>

<?php while ($row = mysqli_fetch_assoc($query)) { ?>

<tr>

<td>

<?php echo htmlspecialchars($row['id']); ?>

</td>

<td>

<?php

echo htmlspecialchars(
    $row['name'] ?? 'Unknown User'
);

?>

</td>

<td>

<?php

echo htmlspecialchars(
    $row['business_name'] ?? 'Unknown Business'
);

?>

</td>

<td>

<?php

echo htmlspecialchars(
    $row['rating']
);

?>/5

</td>

<td>

<?php

echo htmlspecialchars(
    $row['comment'] ?? ''
);

?>

</td>

<td>

<form
method="POST"
style="display:inline;"
onsubmit="return confirm('Delete this review?');"
>

<input
type="hidden"
name="review_id"
value="<?php echo htmlspecialchars($row['id']); ?>"

>

<input
type="hidden"
name="csrf_token"
value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"

>

<button
type="submit"
name="delete_review"
class="delete"

>

Delete

</button>

</form>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>

</html>
