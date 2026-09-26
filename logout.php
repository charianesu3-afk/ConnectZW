<?php

session_start();


// Clear all session variables

$_SESSION = array();


// Destroy the session

session_destroy();


// Redirect user back to login

header("Location: login.php");

exit();

?>