<?php

include 'includes/config.php';


$pageTitle = "Find Businesses | ConnectZW";

include 'includes/header.php';

include 'includes/navbar.php';


?>


<section class="search-page">


<div class="container">



<h1>

Find Trusted Businesses

</h1>


<p>

Search services, businesses and professionals around Zimbabwe.

</p>





<!-- SEARCH FILTER -->


<form method="GET" class="filter-box">



<input

type="text"

name="search"

placeholder="Business name or service..."

value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"

>





<input

type="text"

name="location"

placeholder="Location..."

value="<?php echo isset($_GET['location']) ? htmlspecialchars($_GET['location']) : ''; ?>"

>





<select name="category">


<option value="">

All Categories

</option>



<?php


$cat_query = mysqli_query(
$conn,
"SELECT * FROM categories ORDER BY category_name ASC"
);



while($cat=mysqli_fetch_assoc($cat_query)){


?>


<option

value="<?php echo $cat['id']; ?>"

<?php

if(isset($_GET['category']) && $_GET['category']==$cat['id']){

echo "selected";

}

?>

>


<?php echo htmlspecialchars($cat['category_name']); ?>


</option>


<?php } ?>


</select>




<button type="submit">

🔍 Search

</button>



</form>







<?php



$where=[];



if(!empty($_GET['search'])){


$search=mysqli_real_escape_string(
$conn,
$_GET['search']
);


$where[]="
(
business_name LIKE '%$search%'
OR description LIKE '%$search%'
)";


}





if(!empty($_GET['location'])){


$location=mysqli_real_escape_string(
$conn,
$_GET['location']
);


$where[]="location LIKE '%$location%'";


}




if(!empty($_GET['category'])){


$category=mysqli_real_escape_string(
$conn,
$_GET['category']
);


$where[]="category_id='$category'";


}




$sql="
SELECT businesses.*, categories.category_name

FROM businesses

LEFT JOIN categories

ON businesses.category_id = categories.id
";



if(count($where)>0){


$sql.=" WHERE ".implode(" AND ",$where);


}



$sql.=" ORDER BY created_at DESC";




$result=mysqli_query($conn,$sql);



?>





<h3>

<?php echo mysqli_num_rows($result); ?>

Businesses Found

</h3>





<div class="business-grid">



<?php



if(mysqli_num_rows($result)>0){



while($business=mysqli_fetch_assoc($result)){



?>





<div class="business-card">





<?php if(!empty($business['logo'])){ ?>


<img

src="uploads/<?php echo $business['logo']; ?>"

alt="Business Logo"

>



<?php }else{ ?>


<div class="no-logo">

🏪

</div>


<?php } ?>





<h3>

<?php echo htmlspecialchars($business['business_name']); ?>

</h3>





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

<?php

echo htmlspecialchars(
substr($business['description'],0,120)
);

if(strlen($business['description'])>120){

echo "...";

}

?>

</p>





<a
href="business_profile.php?id=<?php echo $business['id']; ?>"
class="btn"
>
View Profile
</a>

</div>





<?php


}


}else{


?>



<div class="empty-box">


<h2>

No Businesses Found

</h2>


<p>

Try another search location or category.

</p>


<a href="search.php" class="btn">

Reset Search

</a>


</div>




<?php


}



?>




</div>


</div>


</section>




<?php include 'includes/footer.php'; ?>