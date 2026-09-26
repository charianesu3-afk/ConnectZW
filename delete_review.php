<?php

session_start();

include 'includes/config.php';



if(!isset($_SESSION['user_id'])){


header("Location: login.php");

exit();

}



if(!isset($_GET['id'])){


header("Location: index.php");

exit();

}



$review_id=mysqli_real_escape_string(
$conn,
$_GET['id']
);



$user_id=$_SESSION['user_id'];




// Get review details

$review_query=mysqli_query(
$conn,

"SELECT *

FROM reviews

WHERE id='$review_id'

AND user_id='$user_id'"

);





if(mysqli_num_rows($review_query)==0){


die("Review not found or you do not have permission.");

}



$review=mysqli_fetch_assoc($review_query);

$business_id=$review['business_id'];





// Delete review


$delete=mysqli_query(
$conn,

"DELETE FROM reviews

WHERE id='$review_id'

AND user_id='$user_id'"

);





if($delete){



header("Location: business_profile.php?id=".$business_id);

exit();



}else{


echo "Error deleting review.";

}



?>