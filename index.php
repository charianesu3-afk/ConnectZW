<?php

session_start();

include "includes/config.php";

/* =========================================
   DASHBOARD STATISTICS
========================================= */

$totalBusinesses = 0;
$totalCategories = 0;
$totalUsers = 0;
$totalReviews = 0;


/* Total Businesses */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM businesses"
);

if ($result) {
    $totalBusinesses = mysqli_fetch_assoc($result)['total'];
}


/* Total Categories */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM categories"
);

if ($result) {
    $totalCategories = mysqli_fetch_assoc($result)['total'];
}


/* Total Users */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);

if ($result) {
    $totalUsers = mysqli_fetch_assoc($result)['total'];
}


/* Total Reviews */
$result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM reviews"
);

if ($result) {
    $totalReviews = mysqli_fetch_assoc($result)['total'];
}


/* =========================================
   CATEGORIES
========================================= */

$categories = mysqli_query(
    $conn,
    "SELECT *
     FROM categories
     ORDER BY category_name ASC
     LIMIT 8"
);


/* =========================================
   LATEST BUSINESSES
========================================= */

$businesses = mysqli_query(
    $conn,
    "SELECT
        businesses.*,
        categories.category_name
     FROM businesses
     LEFT JOIN categories
        ON businesses.category_id = categories.id
     ORDER BY businesses.created_at DESC
     LIMIT 6"
);


$pageTitle = "ConnectZW | Find Trusted Businesses in Zimbabwe";

include "includes/header.php";
include "includes/navbar.php";

?>

<!-- =========================================
     HERO SECTION
========================================= -->

<section class="hero">

    <div class="container">

        <div class="hero-content">

            <div class="hero-text">

                <span class="hero-badge">
                    🇿🇼 Discover Zimbabwe's Local Businesses
                </span>

                <h1>
                    Find Trusted
                    <span>Businesses</span>
                    Near You
                </h1>

                <p>
                    Connect with trusted businesses and service providers
                    across Zimbabwe. Find what you need, discover new
                    businesses and connect with them easily.
                </p>


                <!-- SEARCH -->

                <form
                    action="business/view_businesses.php"
                    method="GET"
                    class="hero-search"
                >

                    <input
                        type="text"
                        name="search"
                        placeholder="What are you looking for?"
                    >

                    <button type="submit">
                        Search
                    </button>

                </form>


                <div class="hero-links">

                    <a href="business/view_businesses.php">
                        Explore Businesses →
                    </a>

                    <a href="business/add_business.php">
                        List Your Business →
                    </a>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================
     STATISTICS
========================================= -->

<section class="stats-section">

    <div class="container">

        <div class="stats-grid">

            <div class="stat-card">

                <h2>
                    <?php echo (int)$totalBusinesses; ?>
                </h2>

                <p>
                    Businesses
                </p>

            </div>


            <div class="stat-card">

                <h2>
                    <?php echo (int)$totalCategories; ?>
                </h2>

                <p>
                    Categories
                </p>

            </div>


            <div class="stat-card">

                <h2>
                    <?php echo (int)$totalUsers; ?>
                </h2>

                <p>
                    Users
                </p>

            </div>


            <div class="stat-card">

                <h2>
                    <?php echo (int)$totalReviews; ?>
                </h2>

                <p>
                    Reviews
                </p>

            </div>

        </div>

    </div>

</section>


<!-- =========================================
     CATEGORIES
========================================= -->

<section class="categories-section">

    <div class="container">

        <div class="section-heading">

            <span class="section-label">
                EXPLORE
            </span>

            <h2>
                Explore Categories
            </h2>

            <p>
                Find the right businesses and services for your needs.
            </p>

        </div>


        <div class="category-grid">

            <?php if ($categories && mysqli_num_rows($categories) > 0) { ?>

                <?php while ($category = mysqli_fetch_assoc($categories)) { ?>

                    <?php

                    /*
                     * Convert category name to lowercase
                     * so we can match it with the correct image.
                     */

                    $categoryName = strtolower(
                        trim($category['category_name'])
                    );


                    /*
                     * Default category image
                     */

                    $categoryImage = "services.jpg";


                    /*
                     * ELECTRICIAN
                     */

                    if (
                        strpos($categoryName, "electric") !== false
                    ) {

                        $categoryImage = "electrician.jpg";


                    /*
                     * GROCERY / SHOPS
                     */

                    } elseif (
                        strpos($categoryName, "grocery") !== false ||
                        strpos($categoryName, "supermarket") !== false ||
                        strpos($categoryName, "shop") !== false
                    ) {

                        $categoryImage = "grocery.jpg";


                    /*
                     * MECHANIC / AUTOMOTIVE
                     */

                    } elseif (
                        strpos($categoryName, "mechanic") !== false ||
                        strpos($categoryName, "automotive") !== false ||
                        strpos($categoryName, "auto") !== false ||
                        strpos($categoryName, "car") !== false
                    ) {

                        $categoryImage = "mechanic.jpg";


                    /*
                     * PLUMBER
                     */

                    } elseif (
                        strpos($categoryName, "plumb") !== false
                    ) {

                        $categoryImage = "plumbers.jpg";


                    /*
                     * RESTAURANT / FOOD
                     */

                    } elseif (
                        strpos($categoryName, "restaurant") !== false ||
                        strpos($categoryName, "food") !== false ||
                        strpos($categoryName, "cafe") !== false
                    ) {

                        $categoryImage = "restaurants.jpg";


                    /*
                     * TRANSPORT / DELIVERY
                     */

                    } elseif (
                        strpos($categoryName, "transport") !== false ||
                        strpos($categoryName, "delivery") !== false ||
                        strpos($categoryName, "logistic") !== false
                    ) {

                        $categoryImage = "transport and delivery.jpg";


                    /*
                     * SALON / BEAUTY
                     */

                    } elseif (
                        strpos($categoryName, "salon") !== false ||
                        strpos($categoryName, "saloon") !== false ||
                        strpos($categoryName, "beauty") !== false
                    ) {

                        $categoryImage = "saloon.jpg";


                    /*
                     * DEFAULT
                     */

                    } else {

                        $categoryImage = "services.jpg";

                    }

                    ?>


                    <a
                        href="business/view_businesses.php?category=<?php echo (int)$category['id']; ?>"
                        class="category-card"
                    >

                        <div class="category-image">

                            <img
                                src="assets/images/<?php echo htmlspecialchars(
                                    $categoryImage,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                                alt="<?php echo htmlspecialchars(
                                    $category['category_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                            >

                        </div>


                        <div class="category-content">

                            <h3>
                                <?php echo htmlspecialchars(
                                    $category['category_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </h3>

                        </div>

                    </a>

                <?php } ?>

            <?php } else { ?>

                <p>
                    No categories available yet.
                </p>

            <?php } ?>

        </div>

    </div>

</section>


<!-- =========================================
     DISCOVER CONNECTZW
========================================= -->

<section class="discover-section">

    <div class="container">

        <div class="discover-content">

            <div class="discover-text">

                <span class="section-label">
                    CONNECTZW
                </span>

                <h2>
                    Discover Businesses
                    That Work For You
                </h2>

                <p>
                    Whether you are looking for a mechanic, electrician,
                    restaurant, plumber, salon or delivery service,
                    ConnectZW makes it easier to discover businesses
                    around you.
                </p>

                <a
                    href="business/view_businesses.php"
                    class="btn-primary"
                >
                    Explore Businesses
                </a>

            </div>


            <div class="discover-image">

                <img
                    src="assets/images/services.jpg"
                    alt="Local services"
                >

            </div>

        </div>

    </div>

</section>


<!-- =========================================
     LATEST BUSINESSES
========================================= -->

<section class="businesses-section">

    <div class="container">

        <div class="section-heading">

            <span class="section-label">
                DISCOVER
            </span>

            <h2>
                Latest Businesses
            </h2>

            <p>
                Discover businesses recently added to ConnectZW.
            </p>

        </div>


        <div class="business-grid">

            <?php if ($businesses && mysqli_num_rows($businesses) > 0) { ?>

                <?php while ($business = mysqli_fetch_assoc($businesses)) { ?>

                    <?php

                    $logo = !empty($business['logo'])
                        ? "uploads/" . $business['logo']
                        : "assets/images/business-default.jpg";

                    ?>

                    <div class="business-card">

                        <div class="business-image">

                            <img
                                src="<?php echo htmlspecialchars(
                                    $logo,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                                alt="<?php echo htmlspecialchars(
                                    $business['business_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                            >

                        </div>


                        <div class="business-content">

                            <span class="business-category">

                                <?php echo htmlspecialchars(
                                    $business['category_name'] ?? 'Business',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </span>


                            <h3>

                                <?php echo htmlspecialchars(
                                    $business['business_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </h3>


                            <?php if (!empty($business['location'])) { ?>

                                <p>
                                    📍
                                    <?php echo htmlspecialchars(
                                        $business['location'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </p>

                            <?php } ?>


                            <a
                                href="business/view_business.php?id=<?php echo (int)$business['id']; ?>"
                            >
                                View Business →
                            </a>

                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <p>
                    No businesses have been added yet.
                </p>

            <?php } ?>

        </div>


        <div class="section-button">

            <a
                href="business/view_businesses.php"
                class="btn-primary"
            >
                View All Businesses
            </a>

        </div>

    </div>

</section>


<!-- =========================================
     WHY CONNECTZW
========================================= -->

<section class="why-section">

    <div class="container">

        <div class="section-heading">

            <span class="section-label">
                WHY CONNECTZW
            </span>

            <h2>
                Why Use ConnectZW?
            </h2>

            <p>
                A simple way to discover and connect with local businesses.
            </p>

        </div>


        <div class="why-grid">


            <!-- TRUSTED BUSINESSES -->

            <div class="why-card">

                <div class="why-image">

                    <img
                        src="assets/images/trusted-business.jpg"
                        alt="Trusted business"
                    >

                </div>


                <div class="why-content">

                    <h3>
                        Trusted Businesses
                    </h3>

                    <p>
                        Discover businesses and service providers
                        in one convenient place.
                    </p>

                </div>

            </div>


            <!-- CUSTOMER REVIEWS -->

            <div class="why-card">

                <div class="why-image">

                    <img
                        src="assets/images/customer-reviews.jpg"
                        alt="Customer reviews"
                    >

                </div>


                <div class="why-content">

                    <h3>
                        Customer Reviews
                    </h3>

                    <p>
                        Read reviews and experiences from other customers
                        before choosing a business.
                    </p>

                </div>

            </div>


            <!-- GROW BUSINESS -->

            <div class="why-card">

                <div class="why-image">

                    <img
                        src="assets/images/grow-business.jpg"
                        alt="Business growth"
                    >

                </div>


                <div class="why-content">

                    <h3>
                        Grow Your Business
                    </h3>

                    <p>
                        Business owners can showcase their services
                        and reach more customers.
                    </p>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================
     HOW CONNECTZW WORKS
========================================= -->

<section class="how-section">

    <div class="container">

        <div class="section-heading">

            <span class="section-label">
                HOW IT WORKS
            </span>

            <h2>
                How ConnectZW Works
            </h2>

            <p>
                Finding the right business is simple.
            </p>

        </div>


        <div class="steps-grid">


            <!-- STEP 1 -->

            <div class="step-card">

                <div class="step-number">
                    01
                </div>

                <h3>
                    Search
                </h3>

                <p>
                    Search for the business or service you need.
                </p>

            </div>


            <!-- STEP 2 -->

            <div class="step-card">

                <div class="step-number">
                    02
                </div>

                <h3>
                    Discover
                </h3>

                <p>
                    Explore business profiles, services and reviews.
                </p>

            </div>


            <!-- STEP 3 -->

            <div class="step-card">

                <div class="step-number">
                    03
                </div>

                <h3>
                    Connect
                </h3>

                <p>
                    Contact the business directly and get the service
                    you need.
                </p>

            </div>

        </div>

    </div>

</section>


<!-- =========================================
     BUSINESS CTA
========================================= -->

<section class="business-cta">

    <div class="cta-background"></div>

    <div class="container">

        <div class="cta-content">

            <h2>
                Have a Business?
            </h2>

            <p>
                Join ConnectZW and make it easier for customers
                to discover your business.
            </p>

            <a
                href="business/add_business.php"
                class="btn-primary"
            >
                Add Your Business
            </a>

        </div>

    </div>

</section>


<?php include "includes/footer.php"; ?>
```
