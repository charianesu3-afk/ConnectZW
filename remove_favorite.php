<?php

session_start();

include 'includes/config.php';



if(!isset($_SESSION['user_id'])){


header("Location: login.php");

exit();


}



if(!isset($_GET['id'])){


header("Location: my_favorites.php");

exit();


}



$user_id=$_SESSION['user_id'];


$business_id=mysqli_real_escape_string(
$conn,
$_GET['id']
);





$sql="DELETE FROM favorites

WHERE user_id='$user_id'

AND business_id='$business_id'";





if(mysqli_query($conn,$sql)){



header("Location: my_favorites.php");

exit();



}else{



echo "Error removing favorite: ".mysqli_error($conn);



}



?>