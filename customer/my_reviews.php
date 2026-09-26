<?php

session_start();

include "../includes/config.php";


/* =========================
   CHECK LOGIN
========================= */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../login.php");
    exit();

}

$user_id = (int) $_SESSION['user_id'];


/* =========================
   GET USER REVIEWS
========================= */

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        reviews.id,
        reviews.comment,
        reviews.created_at,
        businesses.id AS business_id,
        businesses.business_name
    FROM reviews
    JOIN businesses
        ON reviews.business_id = businesses.id
    WHERE reviews.user_id = ?
    ORDER BY reviews.created_at DESC
    "
);


/* =========================
   CHECK QUERY
========================= */

if (!$stmt) {

    die("Unable to load your reviews.");

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$query = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Reviews | ConnectZW</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


    <style>

        .reviews-page {
            padding: 60px 20px;
            min-height: 70vh;
        }

        .reviews-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .reviews-header h1 {
            margin-bottom: 10px;
        }

        .reviews-header p {
            color: #6b7280;
        }

        .review-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: 0.3s;
        }

        .review-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .review-business {
            margin-top: 0;
            margin-bottom: 15px;
        }

        .review-business a {
            color: #2563EB;
            text-decoration: none;
        }

        .review-business a:hover {
            text-decoration: underline;
        }

        .review-text {
            color: #374151;
            line-height: 1.7;
            margin-bottom: 20px;
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .review-date {
            color: #6b7280;
            font-size: 14px;
        }

        .empty-reviews {
            text-align: center;
            background: #ffffff;
            padding: 50px 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .empty-reviews h2 {
            margin-bottom: 10px;
        }

        .empty-reviews p {
            color: #6b7280;
            margin-bottom: 25px;
        }

        @media (max-width: 600px) {

            .reviews-page {
                padding: 40px 15px;
            }

        }

    </style>

</head>


<body>


<?php include "../includes/navbar.php"; ?>


<section class="reviews-page">

    <div class="container">


        <!-- HEADER -->

        <div class="reviews-header">

            <h1>⭐ My Reviews</h1>

            <p>
                Reviews you have shared on ConnectZW.
            </p>

        </div>


        <!-- REVIEWS -->

        <?php if (mysqli_num_rows($query) > 0): ?>

            <div class="business-grid">

                <?php while ($review = mysqli_fetch_assoc($query)): ?>

                    <div class="review-card">


                        <!-- BUSINESS -->

                        <h2 class="review-business">

                            <a
                                href="../business/view_business.php?id=<?php echo (int)$review['business_id']; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $review['business_name']
                                );
                                ?>

                            </a>

                        </h2>


                        <!-- REVIEW -->

                        <p class="review-text">

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $review['comment']
                                )
                            );

                            ?>

                        </p>


                        <!-- DATE -->

                        <div class="review-date">

                            Posted:

                            <?php

                            $review_date = strtotime(
                                $review['created_at']
                            );

                            if ($review_date !== false) {

                                echo htmlspecialchars(
                                    date(
                                        "d M Y, H:i",
                                        $review_date
                                    )
                                );

                            } else {

                                echo "Date unavailable";

                            }

                            ?>

                        </div>


                    </div>

                <?php endwhile; ?>

            </div>


        <?php else: ?>


            <!-- EMPTY STATE -->

            <div class="empty-reviews">

                <h2>
                    You haven't written any reviews yet.
                </h2>

                <p>
                    Visit a business profile and share your experience.
                </p>

                <a
                    href="../business/view_businesses.php"
                    class="btn"
                >
                    Explore Businesses
                </a>

            </div>


        <?php endif; ?>


    </div>

</section>


<?php include "../includes/footer.php"; ?>


</body>

</html>


<?php

mysqli_stmt_close($stmt);

?>