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
// ADD CATEGORY
// ========================================

if (isset($_POST['add'])) {

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


    // Get and clean category name
    $category = trim($_POST['category_name'] ?? '');


    // Validate category name
    if ($category === '') {
        die("Category name cannot be empty.");
    }


    if (strlen($category) > 100) {
        die("Category name is too long.");
    }


    // ========================================
    // CHECK FOR DUPLICATE CATEGORY
    // ========================================

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM categories
         WHERE LOWER(category_name) = LOWER(?)"
    );


    if (!$stmt) {
        die("Database error.");
    }


    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $category
    );


    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $existing = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if ($existing) {
        die("This category already exists.");
    }


    // ========================================
    // INSERT CATEGORY
    // ========================================

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO categories (category_name)
         VALUES (?)"
    );


    if (!$stmt) {
        die("Database error.");
    }


    mysqli_stmt_bind_param(
        $stmt,
        "s",
        $category
    );


    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        die("Unable to add category.");
    }


    mysqli_stmt_close($stmt);


    header("Location: manage_categories.php");
    exit();
}


// ========================================
// DELETE CATEGORY
// ========================================

if (isset($_POST['delete_category'])) {

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


    // Validate category ID
    $id = filter_input(
        INPUT_POST,
        'category_id',
        FILTER_VALIDATE_INT
    );


    if (!$id || $id <= 0) {
        die("Invalid category ID.");
    }


    // ========================================
    // CHECK CATEGORY EXISTS
    // ========================================

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM categories WHERE id = ?"
    );


    if (!$stmt) {
        die("Database error.");
    }


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );


    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $category_exists = mysqli_fetch_assoc($result);

    mysqli_stmt_close($stmt);


    if (!$category_exists) {
        die("Category not found.");
    }


    // ========================================
    // CHECK IF CATEGORY IS BEING USED
    // ========================================

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id
         FROM businesses
         WHERE category_id = ?
         LIMIT 1"
    );


    if (!$stmt) {
        die("Database error.");
    }


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );


    mysqli_stmt_execute($stmt);

    $check = mysqli_stmt_get_result($stmt);

    mysqli_stmt_close($stmt);


    // ========================================
    // DO NOT DELETE USED CATEGORY
    // ========================================

    if (mysqli_num_rows($check) > 0) {

        die(
            "This category cannot be deleted because one or more businesses are using it."
        );
    }


    // ========================================
    // DELETE CATEGORY
    // ========================================

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM categories WHERE id = ?"
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
        die("Unable to delete category.");
    }


    mysqli_stmt_close($stmt);


    header("Location: manage_categories.php");
    exit();
}


// ========================================
// GET CATEGORIES
// ========================================

$categories = mysqli_query(
    $conn,
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);


if (!$categories) {
    die("Unable to load categories.");
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Categories</title>

<style>

body{
    margin:0;
    font-family:Arial, Helvetica, sans-serif;
    background:#f4f6f9;
}

.header{
    background:#0b5ed7;
    color:white;
    padding:20px;
}

.container{
    width:80%;
    margin:30px auto;
}

.back{
    display:inline-block;
    margin-bottom:20px;
    padding:10px 18px;
    background:#0b5ed7;
    color:white;
    text-decoration:none;
    border-radius:5px;
}

form{
    background:white;
    padding:20px;
    margin-bottom:25px;
    box-shadow:0 2px 10px rgba(0,0,0,.1);
}

input[type="text"]{
    width:70%;
    padding:12px;
    box-sizing:border-box;
}

button{
    padding:12px 20px;
    background:#0b5ed7;
    color:white;
    border:none;
    cursor:pointer;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th,
td{
    padding:12px;
    border:1px solid #ddd;
}

th{
    background:#0b5ed7;
    color:white;
}

.delete{
    color:red;
    font-weight:bold;
    background:none;
    border:none;
    padding:0;
    cursor:pointer;
    font-size:15px;
}

</style>

</head>

<body>

<div class="header">

<h2>Manage Categories</h2>

</div>

<div class="container">

<a class="back" href="dashboard.php">

← Back to Dashboard

</a>

<!-- ADD CATEGORY -->

<form method="POST">

<input
type="text"
name="category_name"
placeholder="Enter Category Name"
maxlength="100"
required

>

<input
type="hidden"
name="csrf_token"
value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"

>

<button
type="submit"
name="add"

>

Add Category

</button>

</form>

<!-- CATEGORIES TABLE -->

<table>

<tr>

<th>ID</th>

<th>Category Name</th>

<th>Action</th>

</tr>

<?php while ($row = mysqli_fetch_assoc($categories)) { ?>

<tr>

<td>

<?php echo htmlspecialchars($row['id']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['category_name']); ?>

</td>

<td>

<form
method="POST"
style="display:inline;"
onsubmit="return confirm('Delete this category?');"
>

<input
type="hidden"
name="category_id"
value="<?php echo htmlspecialchars($row['id']); ?>"

>

<input
type="hidden"
name="csrf_token"
value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"

>

<button
type="submit"
name="delete_category"
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
