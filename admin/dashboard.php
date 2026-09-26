<?php

session_start();

include "../includes/config.php";


/* =========================================
   ADMIN ACCESS PROTECTION
========================================= */

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}


/* =========================================
   GET ADMIN NAME
========================================= */

$admin_name = $_SESSION['name'] ?? "Administrator";


/* =========================================
   GET STATISTICS
========================================= */

$users_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM users"
);

$users = mysqli_fetch_assoc($users_result);
$total_users = $users['total'];


$businesses_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM businesses"
);

$businesses = mysqli_fetch_assoc($businesses_result);
$total_businesses = $businesses['total'];


$categories_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM categories"
);

$categories = mysqli_fetch_assoc($categories_result);
$total_categories = $categories['total'];


$reviews_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM reviews"
);

$reviews = mysqli_fetch_assoc($reviews_result);
$total_reviews = $reviews['total'];


/* =========================================
   GET MESSAGE STATISTICS
========================================= */

$messages_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total FROM contact_messages"
);

$messages = mysqli_fetch_assoc($messages_result);
$total_messages = $messages['total'];


$unread_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM contact_messages
     WHERE status = 'Unread'"
);

$unread = mysqli_fetch_assoc($unread_result);
$unread_messages = $unread['total'];


/* =========================================
   GET RECENT MESSAGES
========================================= */

$recent_messages = mysqli_query(
    $conn,
    "SELECT *
     FROM contact_messages
     ORDER BY created_at DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Dashboard | ConnectZW</title>

    <link
        rel="stylesheet"
        href="admin.css"
    >

    <style>

        .dashboard-container {
            padding: 30px;
        }

        .welcome-box {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .welcome-box h1 {
            margin-bottom: 8px;
        }

        .welcome-box p {
            color: #6b7280;
        }


        /* ================================
           STATISTICS
        ================================= */

        .stats-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(200px, 1fr));

            gap: 20px;

            margin-bottom: 30px;
        }


        .stat-card {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.06);

            transition: 0.2s;
        }


        .stat-card:hover {
            transform: translateY(-3px);
        }


        .stat-icon {
            font-size: 30px;
            margin-bottom: 10px;
        }


        .stat-card h3 {
            margin: 0;

            font-size: 30px;

            color: #111827;
        }


        .stat-card p {
            margin-top: 5px;

            color: #6b7280;
        }


        .unread-card {
            border-left: 5px solid #2563eb;
        }


        /* ================================
           RECENT MESSAGES
        ================================= */

        .recent-section {
            background: #ffffff;

            padding: 25px;

            border-radius: 12px;

            box-shadow:
                0 4px 15px rgba(0,0,0,0.06);
        }


        .recent-section h2 {
            margin-bottom: 20px;
        }


        .messages-table {
            width: 100%;

            border-collapse: collapse;

            overflow-x: auto;
        }


        .messages-table th,
        .messages-table td {

            padding: 14px;

            text-align: left;

            border-bottom:
                1px solid #e5e7eb;
        }


        .messages-table th {

            background: #f8fafc;

            color: #111827;
        }


        .messages-table tr:hover {

            background: #f8fafc;
        }


        .status {

            display: inline-block;

            padding: 5px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: 600;
        }


        .status-unread {

            background: #dbeafe;

            color: #1d4ed8;
        }


        .status-read {

            background: #dcfce7;

            color: #166534;
        }


        .view-all {

            display: inline-block;

            margin-top: 20px;

            background: #2563eb;

            color: white;

            padding: 10px 18px;

            border-radius: 7px;

            text-decoration: none;
        }


        .view-all:hover {

            background: #1d4ed8;
        }


        .no-messages {

            padding: 20px 0;

            color: #6b7280;
        }


        /* ================================
           MOBILE
        ================================= */

        @media (max-width: 700px) {

            .dashboard-container {
                padding: 20px;
            }

            .messages-table {
                display: block;

                overflow-x: auto;

                white-space: nowrap;
            }

        }

    </style>

</head>


<body>


<?php include "sidebar.php"; ?>


<div class="main-content">


    <div class="dashboard-container">


        <!-- =================================
             WELCOME
        ================================= -->

        <div class="welcome-box">

            <h1>
                Welcome, <?php
                echo htmlspecialchars($admin_name);
                ?> 👋
            </h1>

            <p>
                Here's an overview of what's happening
                on your ConnectZW platform.
            </p>

        </div>


        <!-- =================================
             STATISTICS
        ================================= -->

        <div class="stats-grid">


            <div class="stat-card">

                <div class="stat-icon">
                    👥
                </div>

                <h3>
                    <?php echo $total_users; ?>
                </h3>

                <p>
                    Total Users
                </p>

            </div>


            <div class="stat-card">

                <div class="stat-icon">
                    🏢
                </div>

                <h3>
                    <?php echo $total_businesses; ?>
                </h3>

                <p>
                    Businesses
                </p>

            </div>


            <div class="stat-card">

                <div class="stat-icon">
                    📂
                </div>

                <h3>
                    <?php echo $total_categories; ?>
                </h3>

                <p>
                    Categories
                </p>

            </div>


            <div class="stat-card">

                <div class="stat-icon">
                    ⭐
                </div>

                <h3>
                    <?php echo $total_reviews; ?>
                </h3>

                <p>
                    Reviews
                </p>

            </div>


            <div class="stat-card">

                <div class="stat-icon">
                    📩
                </div>

                <h3>
                    <?php echo $total_messages; ?>
                </h3>

                <p>
                    Total Messages
                </p>

            </div>


            <div class="stat-card unread-card">

                <div class="stat-icon">
                    🔵
                </div>

                <h3>
                    <?php echo $unread_messages; ?>
                </h3>

                <p>
                    Unread Messages
                </p>

            </div>


        </div>


        <!-- =================================
             RECENT MESSAGES
        ================================= -->

        <div class="recent-section">

            <h2>
                Recent Messages
            </h2>


            <?php if (mysqli_num_rows($recent_messages) > 0): ?>


                <div style="overflow-x:auto;">

                    <table class="messages-table">

                        <thead>

                            <tr>

                                <th>Name</th>

                                <th>Subject</th>

                                <th>Status</th>

                                <th>Date</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while (
                            $message = mysqli_fetch_assoc(
                                $recent_messages
                            )
                        ): ?>


                            <tr>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $message['name']
                                    );
                                    ?>
                                </td>


                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $message['subject']
                                    );
                                    ?>
                                </td>


                                <td>

                                    <?php
                                    if (
                                        $message['status']
                                        === 'Unread'
                                    ):
                                    ?>

                                        <span
                                            class="status status-unread"
                                        >
                                            Unread
                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="status status-read"
                                        >
                                            Read
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php
                                    echo date(
                                        "d M Y H:i",
                                        strtotime(
                                            $message['created_at']
                                        )
                                    );
                                    ?>

                                </td>

                            </tr>


                        <?php endwhile; ?>


                        </tbody>

                    </table>

                </div>


                <a
                    href="manage_messages.php"
                    class="view-all"
                >
                    View All Messages
                </a>


            <?php else: ?>


                <div class="no-messages">

                    📭 No messages have been received yet.

                </div>


            <?php endif; ?>


        </div>


    </div>


</div>


</body>

</html>