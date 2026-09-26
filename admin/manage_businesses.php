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
// DELETE BUSINESS
// ========================================

if (isset($_POST['delete_business'])) {

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


    // Validate business ID
    $id = filter_input(
        INPUT_POST,
        'business_id',
        FILTER_VALIDATE_INT
    );


    if (!$id || $id <= 0) {
        die("Invalid business ID.");
    }


    // ========================================
    // CHECK BUSINESS EXISTS
    // ========================================

    $check_stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM businesses WHERE id = ?"
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

    $business = mysqli_fetch_assoc($result);

    mysqli_stmt_close($check_stmt);


    if (!$business) {
        die("Business not found.");
    }


    // ========================================
    // DELETE RELATED RECORDS
    // ========================================

    mysqli_begin_transaction($conn);


    try {

        // Delete business images
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM business_images WHERE business_id = ?"
        );

        if (!$stmt) {
            throw new Exception("Unable to prepare image deletion.");
        }

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);


        // Delete reviews
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM reviews WHERE business_id = ?"
        );

        if (!$stmt) {
            throw new Exception("Unable to prepare review deletion.");
        }

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);


        // Delete favorites
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM favorites WHERE business_id = ?"
        );

        if (!$stmt) {
            throw new Exception("Unable to prepare favorite deletion.");
        }

        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);


        // Finally delete the business
        $stmt = mysqli_prepare(
            $conn,
            "DELETE FROM businesses WHERE id = ?"
        );

        if (!$stmt) {
            throw new Exception("Unable to prepare business deletion.");
        }

        mysqli_stmt_bind_param($stmt, "i", $id);

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Unable to delete business.");
        }

        mysqli_stmt_close($stmt);


        // Everything succeeded
        mysqli_commit($conn);


    } catch (Exception $e) {

        // Undo everything if something failed
        mysqli_rollback($conn);

        die("Unable to delete business.");
    }


    // Redirect after successful deletion
    header("Location: manage_businesses.php");
    exit();
}


// ========================================
// GET BUSINESSES
// ========================================

$query = mysqli_query(
    $conn,
    "
    SELECT
        businesses.*,
        users.name AS owner_name,
        categories.category_name
    FROM businesses
    LEFT JOIN users
        ON businesses.owner_id = users.id
    LEFT JOIN categories
        ON businesses.category_id = categories.id
    ORDER BY businesses.created_at DESC
    "
);


if (!$query) {
    die("Unable to load businesses.");
}

?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Businesses</title>

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
    width:95%;
    margin:30px auto;
}

.back{
    display:inline-block;
    margin-bottom:20px;
    background:#0b5ed7;
    color:white;
    padding:10px 18px;
    text-decoration:none;
    border-radius:5px;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
}

th,
td{
    border:1px solid #ddd;
    padding:12px;
    text-align:left;
}

th{
    background:#0b5ed7;
    color:white;
}

tr:nth-child(even){
    background:#f8f8f8;
}

.logo{
    width:60px;
    height:60px;
    object-fit:cover;
    border-radius:5px;
}

.delete{
    color:red;
    text-decoration:none;
    font-weight:bold;
    background:none;
    border:none;
    padding:0;
    cursor:pointer;
    font-size:16px;
}

</style>

</head>

<body>

<div class="header">

<h2>Manage Businesses</h2>

</div>

<div class="container">

<a href="dashboard.php" class="back">

← Back to Dashboard

</a>

<table>

<tr>

<th>Logo</th>

<th>Business</th>

<th>Owner</th>

<th>Category</th>

<th>Phone</th>

<th>Location</th>

<th>Views</th>

<th>Action</th>

</tr>

<?php while ($row = mysqli_fetch_assoc($query)) { ?>

<tr>

<td>

<?php

if (!empty($row['logo'])) {

?>

<img
src="../uploads/<?php echo htmlspecialchars($row['logo']); ?>"
class="logo"
alt="Business Logo">

<?php

} else {

echo "No Logo";

}

?>

</td>

<td>

<?php echo htmlspecialchars($row['business_name']); ?>

</td>

<td>

<?php echo htmlspecialchars($row['owner_name'] ?? 'Unknown'); ?>

</td>

<td>

<?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?>

</td>

<td>

<?php echo htmlspecialchars($row['phone'] ?? ''); ?>

</td>

<td>

<?php echo htmlspecialchars($row['location'] ?? ''); ?>

</td>

<td>

<?php echo htmlspecialchars($row['views'] ?? 0); ?>

</td>

<td>

<form
method="POST"
style="display:inline;"
onsubmit="return confirm('Delete this business? This will also remove its reviews, favorites and gallery images.');"
>

<input
type="hidden"
name="business_id"
value="<?php echo htmlspecialchars($row['id']); ?>"

>

<input
type="hidden"
name="csrf_token"
value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"

>

<button
type="submit"
name="delete_business"
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
