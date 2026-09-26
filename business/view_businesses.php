```php
<?php

session_start();

include "../includes/config.php";


/* =========================================
   SEARCH & FILTER
========================================= */

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';

$category = isset($_GET['category'])
    ? (int)$_GET['category']
    : 0;


/* =========================================
   GET CATEGORIES
========================================= */

$categories = mysqli_query(
    $conn,
    "SELECT *
     FROM categories
     ORDER BY category_name ASC"
);


/* =========================================
   GET BUSINESSES
========================================= */

$sql = "
    SELECT
        businesses.*,
        categories.category_name
    FROM businesses

    LEFT JOIN categories
        ON businesses.category_id = categories.id

    WHERE 1=1
";


/* Search */

if ($search !== '') {

    $safeSearch = mysqli_real_escape_string(
        $conn,
        $search
    );

    $sql .= "
        AND (
            businesses.business_name LIKE '%$safeSearch%'
            OR businesses.description LIKE '%$safeSearch%'
            OR businesses.location LIKE '%$safeSearch%'
            OR categories.category_name LIKE '%$safeSearch%'
        )
    ";
}


/* Category */

if ($category > 0) {

    $sql .= "
        AND businesses.category_id = $category
    ";
}


/* Latest first */

$sql .= "
    ORDER BY businesses.created_at DESC
";


$businesses = mysqli_query(
    $conn,
    $sql
);


$pageTitle = "Explore Businesses | ConnectZW";

include "../includes/header.php";
include "../includes/navbar.php";

?>


<!-- =========================================
     PAGE HEADER
========================================= -->

<section class="search-page">

    <div class="container">


        <div class="section-heading">

            <span class="section-label">
                CONNECTZW DIRECTORY
            </span>

            <h2>
                Explore Businesses
            </h2>

            <p>
                Discover trusted businesses and services
                across Zimbabwe.
            </p>

        </div>


        <!-- =========================================
             SEARCH & FILTER
        ========================================== -->

        <form
            method="GET"
            action="view_businesses.php"
            class="filter-box"
        >


            <!-- SEARCH -->

            <div>

                <input
                    type="text"
                    name="search"
                    placeholder="Search businesses, services or locations..."
                    value="<?php echo htmlspecialchars(
                        $search,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>"
                >

            </div>


            <!-- CATEGORY -->

            <div>

                <select name="category">

                    <option value="0">
                        All Categories
                    </option>


                    <?php if (
                        $categories &&
                        mysqli_num_rows($categories) > 0
                    ) { ?>

                        <?php while (
                            $cat = mysqli_fetch_assoc($categories)
                        ) { ?>

                            <option
                                value="<?php echo (int)$cat['id']; ?>"
                                <?php
                                echo (
                                    $category === (int)$cat['id']
                                )
                                    ? 'selected'
                                    : '';
                                ?>
                            >

                                <?php echo htmlspecialchars(
                                    $cat['category_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </option>

                        <?php } ?>

                    <?php } ?>

                </select>

            </div>


            <!-- BUTTON -->

            <div>

                <button type="submit">
                    Search Businesses
                </button>

            </div>


        </form>


        <!-- =========================================
             RESULTS
        ========================================== -->

        <div class="business-grid">


            <?php if (
                $businesses &&
                mysqli_num_rows($businesses) > 0
            ) { ?>


                <?php while (
                    $business = mysqli_fetch_assoc($businesses)
                ) { ?>


                    <?php

                    /*
                     * Use the business logo if available.
                     * Otherwise use the default image.
                     */

                    if (!empty($business['logo'])) {

                        $businessImage =
                            "../uploads/" .
                            $business['logo'];

                    } elseif (!empty($business['image'])) {

                        /*
                         * Compatibility with older records
                         */

                        $businessImage =
                            "../uploads/" .
                            $business['image'];

                    } else {

                        $businessImage =
                            "../assets/images/business-default.jpg";

                    }

                    ?>


                    <div class="business-card">


                        <!-- IMAGE -->

                        <div class="business-image">

                            <img
                                src="<?php echo htmlspecialchars(
                                    $businessImage,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                                alt="<?php echo htmlspecialchars(
                                    $business['business_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                            >


                            <!-- CATEGORY -->

                            <span class="business-category">

                                <?php echo htmlspecialchars(
                                    $business['category_name']
                                    ?? 'Business',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </span>

                        </div>


                        <!-- CONTENT -->

                        <div class="business-content">


                            <h3>

                                <?php echo htmlspecialchars(
                                    $business['business_name'],
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>

                            </h3>


                            <?php if (
                                !empty($business['location'])
                            ) { ?>

                                <p class="business-location">

                                    📍

                                    <?php echo htmlspecialchars(
                                        $business['location'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>

                                </p>

                            <?php } ?>


                            <?php if (
                                !empty($business['description'])
                            ) { ?>

                                <p class="business-views">

                                    <?php

                                    $description =
                                        $business['description'];

                                    echo htmlspecialchars(
                                        mb_substr(
                                            $description,
                                            0,
                                            100
                                        ),
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );

                                    if (
                                        mb_strlen($description) > 100
                                    ) {
                                        echo "...";
                                    }

                                    ?>

                                </p>

                            <?php } ?>


                            <a
                                href="view_business.php?id=<?php echo (int)$business['id']; ?>"
                                class="business-button"
                            >

                                View Business →

                            </a>


                        </div>


                    </div>


                <?php } ?>


            <?php } else { ?>


                <!-- NO RESULTS -->

                <div
                    style="
                        grid-column:1/-1;
                        text-align:center;
                        padding:60px 20px;
                        background:white;
                        border-radius:16px;
                        border:1px solid #E5E7EB;
                    "
                >

                    <div
                        style="
                            font-size:55px;
                            margin-bottom:15px;
                        "
                    >
                        🔎
                    </div>


                    <h3>
                        No businesses found
                    </h3>


                    <p
                        style="
                            color:#6B7280;
                            margin:10px 0 20px;
                        "
                    >
                        Try another business name,
                        service or category.
                    </p>


                    <a
                        href="view_businesses.php"
                        class="business-button"
                    >
                        View All Businesses
                    </a>

                </div>


            <?php } ?>


        </div>


    </div>

</section>


<?php include "../includes/footer.php"; ?>
```
