<?php

include 'includes/config.php';

$pageTitle = "Categories | ConnectZW";

include 'includes/header.php';
include 'includes/navbar.php';

?>

<section class="search-page">

<div class="container">

<h1>Business Categories</h1>

<p>
Browse businesses and services by category.
</p>

<br>

<div class="business-grid">

<?php

$query = mysqli_query(
    $conn,
    "
    SELECT 
        categories.id,
        categories.category_name,
        COUNT(businesses.id) AS business_count

    FROM categories

    LEFT JOIN businesses
    ON categories.id = businesses.category_id

    GROUP BY categories.id, categories.category_name

    ORDER BY categories.category_name ASC
    "
);

if(mysqli_num_rows($query) > 0){

    while($category = mysqli_fetch_assoc($query)){

?>

<div class="card">

<h2>
📂 <?php echo htmlspecialchars($category['category_name']); ?>
</h2>

<p>

<?php echo $category['business_count']; ?>

<?php

if($category['business_count'] == 1){

    echo " business";

}else{

    echo " businesses";

}

?>

</p>

<br>

<a
href="search.php?category=<?php echo $category['id']; ?>"
class="btn"
>

View Businesses

</a>

</div>

<?php

    }

}else{

?>

<div class="empty-box">

<h2>No Categories Available</h2>

<p>
There are currently no business categories available.
</p>

</div>

<?php

}

?>

</div>

</div>

</section>

<?php include 'includes/footer.php'; ?>