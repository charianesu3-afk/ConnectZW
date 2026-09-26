<?php

session_start();

include 'includes/config.php';



if(!isset($_SESSION['user_id'])){


header("Location: login.php");

exit();

}



$user_id=$_SESSION['user_id'];





$sql="

SELECT businesses.*

FROM favorites

JOIN businesses

ON favorites.business_id = businesses.id

WHERE favorites.user_id='$user_id'

";




$result=mysqli_query($conn,$sql);





include 'includes/header.php';

include 'includes/navbar.php';

?>





<section class="search-page">


<div class="container">





<h1>

❤️ My Favorite Businesses

</h1>



<p>

Businesses you saved for quick access.

</p>






<div class="business-grid">





<?php



if(mysqli_num_rows($result)>0){



while($business=mysqli_fetch_assoc($result)){



?>





<div class="business-card">






<?php if(!empty($business['logo'])){ ?>


<img src="uploads/<?php echo htmlspecialchars($business['logo']); ?>">



<?php }else{ ?>


<div class="no-logo">

🏪

</div>



<?php } ?>






<h3>


<a href="business_profile.php?id=<?php echo $business['id']; ?>">


<?php echo htmlspecialchars($business['business_name']); ?>


</a>


</h3>






<p>

📍

<?php echo htmlspecialchars($business['location']); ?>

</p>





<p>

📞

<?php echo htmlspecialchars($business['phone']); ?>

</p>







<a class="btn"

href="business_profile.php?id=<?php echo $business['id']; ?>">

View Profile

</a>







<a class="btn btn-danger"

href="remove_favorite.php?id=<?php echo $business['id']; ?>">

Remove

</a>






</div>






<?php



}



}else{



?>




<div class="empty-box">


<h2>

No Favorites Yet

</h2>


<p>

Save businesses you like and find them easily later.

</p>



<a href="search.php" class="btn">

Explore Businesses

</a>



</div>





<?php


}


?>





</div>



</div>


</section>






<?php include 'includes/footer.php'; ?>