<?php

session_start();

include '../includes/config.php';


/* =========================================
   LOGIN PROTECTION
========================================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}


/* =========================================
   BUSINESS ROLE PROTECTION
========================================= */

if (
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== "business"
) {

    header("Location: ../index.php");
    exit();

}


$owner_id = (int) $_SESSION['user_id'];

$owner_name = $_SESSION['name'] ?? "Business Owner";


/* =========================================
   CSRF TOKEN
========================================= */

if (!isset($_SESSION['csrf_token'])) {

    $_SESSION['csrf_token'] =
        bin2hex(random_bytes(32));

}

$csrf_token = $_SESSION['csrf_token'];


/* =========================================
   BUSINESS COUNT
========================================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total
     FROM businesses
     WHERE owner_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $owner_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$business_count = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================================
   REVIEWS COUNT
========================================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*) AS total_reviews
     FROM reviews
     INNER JOIN businesses
        ON reviews.business_id = businesses.id
     WHERE businesses.owner_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $owner_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$review_count = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================================
   TOTAL PROFILE VIEWS
========================================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT COALESCE(SUM(views), 0) AS total_views
     FROM businesses
     WHERE owner_id = ?"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $owner_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$views = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/* =========================================
   GET OWNER BUSINESSES
========================================= */

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM businesses
     WHERE owner_id = ?
     ORDER BY created_at DESC"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $owner_id
);

mysqli_stmt_execute($stmt);

$businesses_result = mysqli_stmt_get_result($stmt);


/* =========================================
   PAGE
========================================= */

$pageTitle = "Business Dashboard | ConnectZW";

include '../includes/header.php';
include '../includes/navbar.php';

?>


<section class="search-page">

    <div class="container">


        <!-- =================================
             WELCOME
        ================================== -->

        <h1>
            🏪 Business Dashboard
        </h1>


        <p>

            Welcome,

            <strong>

                <?php
                echo htmlspecialchars(
                    $owner_name,
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>

            </strong>

        </p>


        <p>
            Manage your ConnectZW business listings,
            photos and customer activity.
        </p>


        <br>


        <!-- =================================
             ADD BUSINESS
        ================================== -->

        <a
            href="add_business.php"
            class="btn"
        >
            ➕ Add New Business
        </a>


        <br><br>


        <!-- =================================
             STATISTICS
        ================================== -->

        <div class="features">


            <!-- BUSINESSES -->

            <div class="card">

                <h2>

                    🏪

                    <?php
                    echo (int) $business_count['total'];
                    ?>

                </h2>

                <p>
                    Businesses
                </p>

            </div>


            <!-- VIEWS -->

            <div class="card">

                <h2>

                    👁

                    <?php
                    echo (int) $views['total_views'];
                    ?>

                </h2>

                <p>
                    Profile Views
                </p>

            </div>


            <!-- REVIEWS -->

            <div class="card">

                <h2>

                    ⭐

                    <?php
                    echo (int) $review_count['total_reviews'];
                    ?>

                </h2>

                <p>
                    Customer Reviews
                </p>

            </div>


        </div>


        <br><br>


        <!-- =================================
             YOUR BUSINESSES
        ================================== -->

        <h2>
            Your Businesses
        </h2>


        <br>


        <div class="business-grid">


            <?php if (mysqli_num_rows($businesses_result) > 0): ?>


                <?php while (
                    $business = mysqli_fetch_assoc(
                        $businesses_result
                    )
                ): ?>


                    <div class="business-card">


                        <!-- =================================
                             BUSINESS IMAGE
                        ================================== -->

                        <?php

                        if (!empty($business['logo'])) {

                            $business_image =
                                "../uploads/" .
                                $business['logo'];

                        } elseif (!empty($business['image'])) {

                            $business_image =
                                "../uploads/" .
                                $business['image'];

                        } else {

                            $business_image =
                                "../assets/images/business-default.jpg";

                        }

                        ?>


                        <img
                            src="<?php
                            echo htmlspecialchars(
                                $business_image,
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>"
                            alt="<?php
                            echo htmlspecialchars(
                                $business['business_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>"
                            style="
                                width:100%;
                                height:200px;
                                object-fit:cover;
                                border-radius:12px;
                            "
                        >


                        <br><br>


                        <!-- =================================
                             BUSINESS NAME
                        ================================== -->

                        <h3>

                            🏪

                            <?php
                            echo htmlspecialchars(
                                $business['business_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>

                        </h3>


                        <!-- LOCATION -->

                        <p>

                            📍

                            <?php
                            echo htmlspecialchars(
                                $business['location'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>

                        </p>


                        <!-- PHONE -->

                        <p>

                            📞

                            <?php
                            echo htmlspecialchars(
                                $business['phone'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>

                        </p>


                        <!-- VIEWS -->

                        <p>

                            👁

                            <?php
                            echo (int) (
                                $business['views'] ?? 0
                            );
                            ?>

                            views

                        </p>


                        <br>


                        <!-- =================================
                             VIEW
                        ================================== -->

                        <a
                            class="btn"
                            href="view_business.php?id=<?php
                            echo (int) $business['id'];
                            ?>"
                        >

                            👁 View Listing

                        </a>


                        <br><br>


                        <!-- =================================
                             EDIT
                        ================================== -->

                        <a
                            class="btn"
                            href="edit_business.php?id=<?php
                            echo (int) $business['id'];
                            ?>"
                        >

                            ✏️ Edit Business

                        </a>


                        <br><br>


                        <!-- =================================
                             GALLERY
                        ================================== -->

                        <a
                            class="btn"
                            href="upload_images.php?id=<?php
                            echo (int) $business['id'];
                            ?>"
                        >

                            🖼️ Manage Gallery

                        </a>


                        <br><br>


                        <!-- =================================
                             DELETE
                        ================================== -->

                        <form
                            method="POST"
                            action="delete_business.php"
                            style="display:inline;"
                            onsubmit="
                                return confirm(
                                    'Are you sure you want to permanently delete this business?'
                                );
                            "
                        >

                            <input
                                type="hidden"
                                name="business_id"
                                value="<?php
                                echo (int) $business['id'];
                                ?>"
                            >


                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php
                                echo htmlspecialchars(
                                    $csrf_token,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>"
                            >


                            <button
                                type="submit"
                                name="delete_business"
                                style="
                                    background:#dc2626;
                                    color:white;
                                    border:none;
                                    padding:10px 15px;
                                    border-radius:8px;
                                    cursor:pointer;
                                    font-size:15px;
                                "
                            >

                                🗑️ Delete Business

                            </button>

                        </form>


                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <!-- =================================
                     NO BUSINESSES
                ================================== -->

                <div class="card">


                    <h2>
                        No Businesses Yet
                    </h2>


                    <p>
                        You have not added a business
                        to ConnectZW yet.
                    </p>


                    <br>


                    <a
                        href="add_business.php"
                        class="btn"
                    >

                        ➕ Add Your First Business

                    </a>


                </div>


            <?php endif; ?>


        </div>


    </div>

</section>


<?php

mysqli_stmt_close($stmt);

include '../includes/footer.php';

?>