<?php

session_start();

include 'includes/config.php';


/* =========================
   CHECK BUSINESS ID
========================= */

if(!isset($_GET['id']) || !is_numeric($_GET['id'])){

    header("Location: search.php");
    exit();

}


$id = intval($_GET['id']);



/* =========================
   UPDATE BUSINESS VIEWS
========================= */

mysqli_query(
    $conn,
    "UPDATE businesses 
     SET views = views + 1 
     WHERE id='$id'"
);




/* =========================
   GET BUSINESS DETAILS
========================= */


$business_query = mysqli_query(
    $conn,
    "SELECT businesses.*, 
    categories.category_name
    FROM businesses
    LEFT JOIN categories 
    ON businesses.category_id = categories.id
    WHERE businesses.id='$id'"
);



if(mysqli_num_rows($business_query)==0){

    header("Location: search.php");
    exit();

}


$business = mysqli_fetch_assoc($business_query);





/* =========================
   RATINGS
========================= */


$rating_query = mysqli_query(
    $conn,
    "SELECT 
    AVG(rating) AS average_rating,
    COUNT(*) AS total_reviews
    FROM reviews
    WHERE business_id='$id'"
);


$rating = mysqli_fetch_assoc($rating_query);



?>


<?php

$pageTitle = $business['business_name']." | ConnectZW";

include 'includes/header.php';

include 'includes/navbar.php';

?>



<section class="search-page">


<div class="container">



<a href="search.php" class="btn">

← Back to Businesses

</a>

<br><br>




<div class="business-card">



<!-- LOGO -->

<?php if(!empty($business['logo'])){ ?>


<img 
src="uploads/<?php echo $business['logo']; ?>"
class="business-logo"
>


<?php }else{ ?>


<div class="no-logo">

🏪

</div>


<?php } ?>





<h1>

<?php echo htmlspecialchars($business['business_name']); ?>

</h1>




<?php if(!empty($business['category_name'])){ ?>

<p>

📂 
<?php echo htmlspecialchars($business['category_name']); ?>

</p>

<?php } ?>





<p>

📍 
<?php echo htmlspecialchars($business['location']); ?>

</p>



<p>

📞 
<?php echo htmlspecialchars($business['phone']); ?>

</p>




<p>

👁 
<?php echo $business['views']; ?> views

</p>




<!-- RATING -->

<div>


<h3>

⭐ Rating:

<?php


if($rating['total_reviews'] > 0){


echo round($rating['average_rating'],1);

echo " / 5 (".
$rating['total_reviews'].
" reviews)";


}else{


echo "No reviews yet";


}


?>

</h3>


</div>






<!-- ACTION BUTTONS -->


<div class="profile-buttons">


<a 
class="btn"
href="tel:<?php echo $business['phone']; ?>"
>

📞 Call

</a>





<a 
class="btn"
target="_blank"
href="https://wa.me/263<?php echo substr($business['phone'],1); ?>"
>

💬 WhatsApp

</a>





<?php if(isset($_SESSION['user_id'])){ ?>


<a 
class="btn"
href="add_favorite.php?id=<?php echo $id; ?>"
>

❤️ Save

</a>


<?php } ?>


</div>




<hr>




<h2>

About This Business

</h2>



<p>

<?php echo htmlspecialchars($business['description']); ?>

</p>




</div>







<!-- GALLERY -->

<div class="business-card">


<h2>

Business Gallery

</h2>



<div class="business-grid">


<?php


$image_query=mysqli_query(
$conn,
"SELECT * FROM business_images 
WHERE business_id='$id'"
);



if(mysqli_num_rows($image_query)>0){



while($image=mysqli_fetch_assoc($image_query)){



?>


<img 
src="uploads/<?php echo $image['image']; ?>"
class="gallery-image"
>



<?php


}


}else{


echo "<p>No images uploaded yet.</p>";


}


?>

</div>



</div>







<!-- REVIEWS -->

<div class="business-card">


<h2>

Customer Reviews

</h2>

<?php if(isset($_SESSION['user_id'])){ ?>

<hr>

<h3>
Leave a Review
</h3>


<form method="POST" action="add_review.php">



<input 
type="hidden"
name="business_id"
value="<?php echo $id; ?>"
>




<label>

Rating

</label>


<select name="rating" required>


<option value="5">
⭐⭐⭐⭐⭐ 5 Stars
</option>


<option value="4">
⭐⭐⭐⭐ 4 Stars
</option>


<option value="3">
⭐⭐⭐ 3 Stars
</option>


<option value="2">
⭐⭐ 2 Stars
</option>


<option value="1">
⭐ 1 Star
</option>


</select>


<br><br>



<label>

Comment

</label>


<textarea

name="comment"

placeholder="Share your experience..."

required>

</textarea>



<br><br>



<button type="submit">

Submit Review

</button>



</form>



<?php }else{ ?>


<p>

<a href="login.php">

Login

</a>

to leave a review.

</p>



<?php } ?>


<?php


$review_query=mysqli_query(
$conn,

"SELECT reviews.*, users.name

FROM reviews

JOIN users

ON reviews.user_id = users.id

WHERE reviews.business_id='$id'

ORDER BY reviews.created_at DESC"

);



if(mysqli_num_rows($review_query)>0){



while($review=mysqli_fetch_assoc($review_query)){



?>


<div class="review">

<p>

👤 
<?php echo htmlspecialchars($review['name']); ?>

</p>
<h4>

⭐ <?php echo $review['rating']; ?>/5

</h4>



<p>

<?php echo htmlspecialchars($review['comment']); ?>

</p>




<?php if(isset($_SESSION['user_id']) 
&& $_SESSION['user_id']==$review['user_id']){ ?>


<a 
class="btn btn-danger"

href="delete_review.php?id=<?php echo $review['id']; ?>"

onclick="return confirm('Delete this review?');"

>

Delete Review

</a>



<?php } ?>


</div>



<?php


}


}else{


echo "<p>No reviews available yet.</p>";


}



?>



</div>







<!-- SIMILAR BUSINESSES -->


<div class="business-card">


<h2>

More Businesses

</h2>



<div class="business-grid">


<?php



$similar=mysqli_query(
$conn,
"SELECT *
FROM businesses
WHERE category_id='{$business['category_id']}'
AND id!='$id'
LIMIT 3"
);



while($item=mysqli_fetch_assoc($similar)){



?>


<div class="card">


<h3>

<?php echo htmlspecialchars($item['business_name']); ?>

</h3>


<p>

📍 <?php echo htmlspecialchars($item['location']); ?>

</p>


<a 
href="business_profile.php?id=<?php echo $item['id']; ?>"
class="btn"
>

View

</a>


</div>


<?php } ?>


</div>



</div>



</div>


</section>




<?php include 'includes/footer.php'; ?>