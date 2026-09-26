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
   GET FAVORITE BUSINESSES
========================= */

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        businesses.id,
        businesses.business_name,
        businesses.location,
        businesses.logo,
        businesses.description
    FROM favorites
    JOIN businesses
        ON favorites.business_id = businesses.id
    WHERE favorites.user_id = ?
    ORDER BY favorites.created_at DESC
    "
);


if (!$stmt) {

    die("Unable to load your favorite businesses.");

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

    <title>My Favorites | ConnectZW</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .favorites-page {
            padding: 60px 20px;
            min-height: 70vh;
        }

        .favorites-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .favorites-header h1 {
            margin-bottom: 10px;
        }

        .favorites-header p {
            color: #6b7280;
        }

        .favorite-card {
            background: #ffffff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: 0.3s;
        }

        .favorite-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .favorite-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            display: block;
        }

        .favorite-content {
            padding: 20px;
        }

        .favorite-content h2 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .favorite-location {
            color: #6b7280;
            margin-bottom: 12px;
        }

        .favorite-description {
            color: #4b5563;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .favorite-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .empty-favorites {
            text-align: center;
            background: #ffffff;
            padding: 50px 20px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
        }

        .empty-favorites h2 {
            margin-bottom: 10px;
        }

        .empty-favorites p {
            color: #6b7280;
            margin-bottom: 25px;
        }

        @media (max-width: 600px) {

            .favorites-page {
                padding: 40px 15px;
            }

            .favorite-image {
                height: 180px;
            }

        }

    </style>

</head>


<body>


<?php include "../includes/navbar.php"; ?>


<section class="favorites-page">

    <div class="container">


        <!-- PAGE HEADER -->

        <div class="favorites-header">

            <h1>❤️ My Favorites</h1>

            <p>
                Businesses you have saved on ConnectZW.
            </p>

        </div>


        <!-- FAVORITES -->

        <?php if (mysqli_num_rows($query) > 0): ?>

            <div class="business-grid">

                <?php while ($business = mysqli_fetch_assoc($query)): ?>

                    <div class="favorite-card">


                        <!-- BUSINESS IMAGE -->

                        <?php

                        $image = "../assets/images/business-default.jpg";

                        if (!empty($business['logo'])) {

                            $image = "../uploads/" . basename($business['logo']);

                        }

                        ?>


                        <img
                            src="<?php echo htmlspecialchars($image); ?>"
                            alt="<?php echo htmlspecialchars($business['business_name']); ?>"
                            class="favorite-image"
                        >


                        <!-- BUSINESS INFORMATION -->

                        <div class="favorite-content">


                            <h2>
                                <?php
                                echo htmlspecialchars(
                                    $business['business_name']
                                );
                                ?>
                            </h2>


                            <p class="favorite-location">

                                📍

                                <?php
                                echo htmlspecialchars(
                                    $business['location']
                                );
                                ?>

                            </p>


                            <?php if (!empty($business['description'])): ?>

                                <p class="favorite-description">

                                    <?php

                                    $description = $business['description'];

                                    if (strlen($description) > 120) {

                                        $description =
                                            substr($description, 0, 120) . "...";

                                    }

                                    echo htmlspecialchars($description);

                                    ?>

                                </p>

                            <?php endif; ?>


                            <div class="favorite-actions">

                                <a
                                    class="btn"
                                    href="../business/view_business.php?id=<?php echo (int)$business['id']; ?>"
                                >
                                    View Business
                                </a>

                            </div>


                        </div>


                    </div>

                <?php endwhile; ?>

            </div>


        <?php else: ?>


            <!-- NO FAVORITES -->

            <div class="empty-favorites">

                <h2>
                    You haven't saved any businesses yet.
                </h2>

                <p>
                    Explore ConnectZW and save businesses
                    you want to find again easily.
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