<?php

session_start();

include "../includes/config.php";

// ==========================================
// CHECK LOGIN
// ==========================================

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// ==========================================
// CUSTOMER USER ID
// ==========================================

$user_id = $_SESSION['user_id'];

// ==========================================
// GET USER DETAILS
// ==========================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, name, email, role
     FROM users
     WHERE id = ?
     LIMIT 1"
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {

    mysqli_stmt_close($stmt);

    session_destroy();

    header("Location: ../login.php");
    exit();
}

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// ==========================================
// GET FAVORITE COUNT
// ==========================================

$favorite_count = 0;

$favorite_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*)
     AS total
     FROM favorites
     WHERE user_id = ?"
);

if ($favorite_stmt) {

    mysqli_stmt_bind_param(
        $favorite_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($favorite_stmt);

    $favorite_result = mysqli_stmt_get_result(
        $favorite_stmt
    );

    $favorite_data = mysqli_fetch_assoc(
        $favorite_result
    );

    $favorite_count = (int)$favorite_data['total'];

    mysqli_stmt_close($favorite_stmt);
}

// ==========================================
// GET REVIEW COUNT
// ==========================================

$review_count = 0;

$review_stmt = mysqli_prepare(
    $conn,
    "SELECT COUNT(*)
     AS total
     FROM reviews
     WHERE user_id = ?"
);

if ($review_stmt) {

    mysqli_stmt_bind_param(
        $review_stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($review_stmt);

    $review_result = mysqli_stmt_get_result(
        $review_stmt
    );

    $review_data = mysqli_fetch_assoc(
        $review_result
    );

    $review_count = (int)$review_data['total'];

    mysqli_stmt_close($review_stmt);
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Customer Dashboard | ConnectZW
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .customer-dashboard {
            padding: 50px 20px;
        }

        .welcome-card {
            background: linear-gradient(
                135deg,
                #2563EB,
                #1D4ED8
            );
            color: white;
            padding: 35px;
            border-radius: 18px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.20);
        }

        .welcome-card h1 {
            margin: 0 0 10px;
        }

        .welcome-card p {
            margin: 0;
            opacity: 0.9;
        }

        .customer-stats {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            text-align: center;
        }

        .stat-card .icon {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .stat-card h2 {
            margin: 5px 0;
            font-size: 30px;
        }

        .stat-card p {
            color: #6b7280;
            margin: 0;
        }

        .customer-actions {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
        }

        .action-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .action-card h2 {
            margin-top: 0;
        }

        .action-card p {
            color: #6b7280;
            line-height: 1.6;
        }

        .dashboard-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 11px 18px;
            background: #2563EB;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }

        .dashboard-btn:hover {
            background: #1D4ED8;
        }

        .secondary-btn {
            background: #f3f4f6;
            color: #111827;
        }

        .secondary-btn:hover {
            background: #e5e7eb;
        }

    </style>

</head>

<body>

<?php include "../includes/navbar.php"; ?>


<section class="customer-dashboard">

    <div class="container">

        <!-- ==================================
             WELCOME
        =================================== -->

        <div class="welcome-card">

            <h1>
                Welcome,
                <?php echo htmlspecialchars($user['name']); ?>
                👋
            </h1>

            <p>
                Discover businesses, save your favorites
                and share your experiences on ConnectZW.
            </p>

        </div>


        <!-- ==================================
             STATS
        =================================== -->

        <div class="customer-stats">

            <div class="stat-card">

                <div class="icon">
                    ❤️
                </div>

                <h2>
                    <?php echo $favorite_count; ?>
                </h2>

                <p>
                    Saved Businesses
                </p>

            </div>


            <div class="stat-card">

                <div class="icon">
                    ⭐
                </div>

                <h2>
                    <?php echo $review_count; ?>
                </h2>

                <p>
                    Reviews
                </p>

            </div>

        </div>


        <!-- ==================================
             ACTIONS
        =================================== -->

        <div class="customer-actions">


            <!-- FIND BUSINESSES -->

            <div class="action-card">

                <h2>
                    🔎 Find Businesses
                </h2>

                <p>
                    Explore businesses and services
                    available on ConnectZW.
                </p>

                <a
                    href="../business/view_businesses.php"
                    class="dashboard-btn"
                >
                    Explore Businesses
                </a>

            </div>


            <!-- FAVORITES -->

            <div class="action-card">

                <h2>
                    ❤️ My Favorites
                </h2>

                <p>
                    View the businesses you have saved
                    for easy access later.
                </p>

                <a
                    href="my_favorites.php"
                    class="dashboard-btn"
                >
                    View Favorites
                </a>

            </div>


            <!-- REVIEWS -->

            <div class="action-card">

                <h2>
                    ⭐ My Reviews
                </h2>

                <p>
                    View the reviews you have written
                    about businesses.
                </p>

                <a
                    href="my_reviews.php"
                    class="dashboard-btn"
                >
                    View Reviews
                </a>

            </div>


            <!-- HOME -->

            <div class="action-card">

                <h2>
                    🏠 ConnectZW Home
                </h2>

                <p>
                    Return to the ConnectZW homepage
                    and continue exploring.
                </p>

                <a
                    href="../index.php"
                    class="dashboard-btn secondary-btn"
                >
                    Back to Home
                </a>

            </div>


        </div>

    </div>

</section>


<?php include "../includes/footer.php"; ?>

</body>

</html>