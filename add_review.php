<?php

session_start();

include 'includes/config.php';



if(!isset($_SESSION['user_id'])){


header("Location: login.php");

exit();

}




if(!isset($_POST['business_id'])){


header("Location: search.php");

exit();

}



$user_id=$_SESSION['user_id'];


$business_id=mysqli_real_escape_string(
$conn,
$_POST['business_id']
);



$rating=mysqli_real_escape_string(
$conn,
$_POST['rating']
);



$comment=mysqli_real_escape_string(
$conn,
$_POST['comment']
);






if($rating < 1 || $rating > 5){


die("Invalid rating.");

}



if($comment==""){


die("Please write a review.");

}






$sql="INSERT INTO reviews

(business_id,user_id,rating,comment)

VALUES

('$business_id',
'$user_id',
'$rating',
'$comment')";





if(mysqli_query($conn,$sql)){


header("Location: business_profile.php?id=".$business_id);

exit();



}else{


echo "Error adding review: ".mysqli_error($conn);



}



?>