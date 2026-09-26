<?php

session_start();

include 'includes/config.php';



if(!isset($_SESSION['user_id'])){


header("Location: login.php");

exit();


}




if(!isset($_GET['id'])){


header("Location: search.php");

exit();


}




$user_id=$_SESSION['user_id'];


$business_id=mysqli_real_escape_string(
$conn,
$_GET['id']
);






// Check if business exists


$check_business=mysqli_query(
$conn,

"SELECT id FROM businesses WHERE id='$business_id'"

);





if(mysqli_num_rows($check_business)==0){


die("Business not found.");

}





// Check existing favorite


$check_favorite=mysqli_query(
$conn,

"SELECT id FROM favorites

WHERE user_id='$user_id'

AND business_id='$business_id'"

);






if(mysqli_num_rows($check_favorite)==0){



mysqli_query(

$conn,

"INSERT INTO favorites

(user_id,business_id)

VALUES

('$user_id','$business_id')"

);



}





header("Location: business_profile.php?id=".$business_id);

exit();



?>