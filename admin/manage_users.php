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
// DELETE USER
// ========================================

if (isset($_POST['delete_user'])) {

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


    // Get user ID
    $id = filter_input(
        INPUT_POST,
        'user_id',
        FILTER_VALIDATE_INT
    );


    // Validate ID
    if (!$id || $id <= 0) {
        die("Invalid user ID.");
    }


    // Prevent admin from deleting themselves
    if ($id == $_SESSION['user_id']) {
        die("You cannot delete your own admin account.");
    }


    // ========================================
    // CHECK USER BEFORE DELETING
    // ========================================

    $check_stmt = mysqli_prepare(
        $conn,
        "SELECT id, role FROM users WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $check_stmt,
        "i",
        $id
    );

    mysqli_stmt_execute($check_stmt);

    $result = mysqli_stmt_get_result($check_stmt);

    $user = mysqli_fetch_assoc($result);

    mysqli_stmt_close($check_stmt);


    if (!$user) {
        die("User not found.");
    }


    // ========================================
    // PREVENT DELETING ANOTHER ADMIN
    // ========================================

    if ($user['role'] === "admin") {
        die("Admin accounts cannot be deleted from this page.");
    }


    // ========================================
    // DELETE USER SECURELY
    // ========================================

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM users WHERE id = ?"
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
        die("Unable to delete user.");
    }


    mysqli_stmt_close($stmt);


    // Redirect after successful deletion
    header("Location: manage_users.php");
    exit();
}


// ========================================
// GET ALL USERS
// ========================================

$users = mysqli_query(
    $conn,
    "SELECT id, name, email, role
     FROM users
     ORDER BY id DESC"
);


if (!$users) {
    die("Unable to load users.");
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Users</title>

<style>

body{
    font-family:Arial, Helvetica, sans-serif;
    background:#f4f6f9;
    margin:0;
}

.header{
    background:#0b5ed7;
    color:white;
    padding:20px;
}

.container{
    width:90%;
    margin:30px auto;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th,
td{
    padding:14px;
    border:1px solid #ddd;
    text-align:left;
}

th{
    background:#0b5ed7;
    color:white;
}

tr:nth-child(even){
    background:#f8f8f8;
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

.back{
    display:inline-block;
    margin-bottom:20px;
    text-decoration:none;
    background:#0b5ed7;
    color:white;
    padding:10px 18px;
    border-radius:5px;
}

.self{
    color:green;
    font-weight:bold;
}

.admin-label{
    color:#0b5ed7;
    font-weight:bold;
}

</style>

</head>

<body>

<div class="header">

<h2>Manage Users</h2>

</div>

<div class="container">

<a href="dashboard.php" class="back">

← Back to Dashboard

</a>

<table>

<tr>

<th>ID</th>

<th>Name</th>

<th>Email</th>

<th>Role</th>

<th>Action</th>

</tr>

<?php while ($row = mysqli_fetch_assoc($users)) { ?>

<tr>

<td>

<?php echo htmlspecialchars($row['id']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['email']); ?>

</td>

<td>

<?php echo htmlspecialchars(ucfirst($row['role'])); ?>

</td>

<td>

<?php

if ($row['id'] == $_SESSION['user_id']) {

?>

<span class="self">

Current User

</span>

<?php

} elseif ($row['role'] === "admin") {

?>

<span class="admin-label">

Admin Account

</span>

<?php

} else {

?>

<form
method="POST"
style="display:inline;"
onsubmit="return confirm('Delete this user?');"
>

<input
type="hidden"
name="user_id"
value="<?php echo htmlspecialchars($row['id']); ?>"

>

<input
type="hidden"
name="csrf_token"
value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"

>

<button
type="submit"
name="delete_user"
class="delete"

>

Delete

</button>

</form>

<?php

}

?>

</td>

</tr>

<?php } ?>

</table>

</div>

</body>

</html>
