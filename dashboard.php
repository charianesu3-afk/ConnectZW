<?php
session_start();

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
?>

<section class="hero">

<h1>Welcome, <?php echo $_SESSION['name']; ?>!</h1>

<p>You are logged in as a <strong><?php echo $_SESSION['role']; ?></strong>.</p>

<p>Welcome to your ConnectZW Dashboard.</p>

</section>

<?php
include 'includes/footer.php';
?>