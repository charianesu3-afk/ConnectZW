<?php

session_start();

include "../includes/config.php";

// ==========================================
// CREATE CSRF TOKEN
// ==========================================

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ==========================================
// CHECK BUSINESS ID
// ==========================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {

    header("Location: view_businesses.php");
    exit();

}

$business_id = (int) $_GET['id'];

// ==========================================
// GET BUSINESS
// ==========================================

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        businesses.*,
        categories.category_name
    FROM businesses
    LEFT JOIN categories
        ON businesses.category_id = categories.id
    WHERE businesses.id = ?
    "
);

mysqli_stmt_bind_param($stmt, "i", $business_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {

    mysqli_stmt_close($stmt);

    header("Location: view_businesses.php");
    exit();

}

$business = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// ==========================================
// UPDATE VIEWS
// ==========================================

$stmt = mysqli_prepare(
    $conn,
    "
    UPDATE businesses
    SET views = views + 1
    WHERE id = ?
    "
);

mysqli_stmt_bind_param($stmt, "i", $business_id);
mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

// ==========================================
// GET BUSINESS PHOTOS
// ==========================================

$gallery = [];

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT image
    FROM business_images
    WHERE business_id = ?
    ORDER BY id DESC
    "
);

mysqli_stmt_bind_param($stmt, "i", $business_id);
mysqli_stmt_execute($stmt);

$gallery_result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($gallery_result)) {

    $gallery[] = $row['image'];

}

mysqli_stmt_close($stmt);

// ==========================================
// MAIN IMAGE
// ==========================================

if (!empty($business['logo'])) {

    $main_image = "../uploads/" . $business['logo'];

} else {

    $main_image = "../assets/images/business-default.jpg";

}

// ==========================================
// WHATSAPP NUMBER
// ==========================================

$whatsapp = preg_replace(
    '/[^0-9]/',
    '',
    $business['phone']
);

if (substr($whatsapp, 0, 1) === "0") {

    $whatsapp = "263" . substr($whatsapp, 1);

}

// ==========================================
// GET REVIEWS
// ==========================================

$reviews = [];

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        reviews.comment,
        reviews.created_at,
        users.name
    FROM reviews
    JOIN users
        ON reviews.user_id = users.id
    WHERE reviews.business_id = ?
    ORDER BY reviews.created_at DESC
    "
);

mysqli_stmt_bind_param($stmt, "i", $business_id);
mysqli_stmt_execute($stmt);

$reviews_result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($reviews_result)) {

    $reviews[] = $row;

}

mysqli_stmt_close($stmt);

// ==========================================
// REVIEW SUBMISSION
// ==========================================

$review_message = "";

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['submit_review'])
) {

    // --------------------------------------
    // CHECK CSRF TOKEN
    // --------------------------------------

    if (
        !isset($_POST['csrf_token']) ||
        !isset($_SESSION['csrf_token']) ||
        !hash_equals(
            $_SESSION['csrf_token'],
            $_POST['csrf_token']
        )
    ) {

        die("Invalid security token.");

    }

    if (!isset($_SESSION['user_id'])) {

        $review_message =
            "Please log in to submit a review.";

    } else {

        $user_id = (int) $_SESSION['user_id'];

        $comment = trim(
            $_POST['comment'] ?? ""
        );

        if ($comment === "") {

            $review_message =
                "Please write a review.";

        } elseif (strlen($comment) < 3) {

            $review_message =
                "Your review is too short.";

        } elseif (strlen($comment) > 2000) {

            $review_message =
                "Your review is too long.";

        } else {

            // --------------------------------------
            // CHECK IF ALREADY REVIEWED
            // --------------------------------------

            $check_stmt = mysqli_prepare(
                $conn,
                "
                SELECT id
                FROM reviews
                WHERE user_id = ?
                AND business_id = ?
                LIMIT 1
                "
            );

            mysqli_stmt_bind_param(
                $check_stmt,
                "ii",
                $user_id,
                $business_id
            );

            mysqli_stmt_execute($check_stmt);

            $check_result =
                mysqli_stmt_get_result($check_stmt);

            $already_reviewed =
                mysqli_num_rows($check_result) > 0;

            mysqli_stmt_close($check_stmt);

            if ($already_reviewed) {

                $review_message =
                    "You have already reviewed this business.";

            } else {

                // --------------------------------------
                // INSERT REVIEW
                // --------------------------------------

                $insert_stmt = mysqli_prepare(
                    $conn,
                    "
                    INSERT INTO reviews
                    (user_id, business_id, comment)
                    VALUES (?, ?, ?)
                    "
                );

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "iis",
                    $user_id,
                    $business_id,
                    $comment
                );

                if (mysqli_stmt_execute($insert_stmt)) {

                    mysqli_stmt_close($insert_stmt);

                    header(
                        "Location: view_business.php?id="
                        . $business_id
                        . "&review=success"
                    );

                    exit();

                } else {

                    $review_message =
                        "Unable to submit your review.";

                    mysqli_stmt_close($insert_stmt);

                }

            }

        }

    }

}

// ==========================================
// REVIEW SUCCESS MESSAGE
// ==========================================

if (
    isset($_GET['review']) &&
    $_GET['review'] === 'success'
) {

    $review_message =
        "Your review was posted successfully.";

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

        <?php
        echo htmlspecialchars(
            $business['business_name']
        );
        ?>

        - ConnectZW

    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .business-profile {
            padding: 50px 20px;
        }

        .profile-card {
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .profile-main-image {
            width: 100%;
            max-height: 450px;
            object-fit: cover;
            border-radius: 15px;
            margin-bottom: 25px;
        }

        .profile-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 25px;
        }

        .favorite-form {
            display: inline-block;
            margin: 0;
        }

        .gallery-grid {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .gallery-grid img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
        }

        .reviews-section {
            margin-top: 30px;
        }

        .review-form {
            background: #ffffff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
            margin-bottom: 30px;
        }

        .review-form textarea {
            width: 100%;
            min-height: 130px;
            padding: 15px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            resize: vertical;
            font-family: inherit;
            box-sizing: border-box;
            margin-bottom: 15px;
        }

        .review-card {
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            margin-bottom: 15px;
        }

        .review-card h3 {
            margin-top: 0;
        }

        .review-date {
            color: #6b7280;
            font-size: 13px;
        }

        .review-message {
            padding: 12px 15px;
            background: #eff6ff;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .no-reviews {
            color: #6b7280;
        }

        @media (max-width: 600px) {

            .business-profile {
                padding: 30px 15px;
            }

            .profile-card {
                padding: 20px;
            }

            .profile-buttons {
                flex-direction: column;
            }

            .profile-buttons .btn,
            .favorite-form,
            .favorite-form .btn {
                width: 100%;
            }

        }

    </style>

</head>

<body>

<?php include "../includes/navbar.php"; ?>

<section class="business-profile">

    <div class="container">

        <!-- ==========================================
             BUSINESS PROFILE
        =========================================== -->

        <div class="profile-card">

            <!-- MAIN IMAGE -->

            <img
                src="<?php echo htmlspecialchars($main_image); ?>"
                alt="<?php echo htmlspecialchars($business['business_name']); ?>"
                class="profile-main-image"
            >

            <!-- BUSINESS NAME -->

            <h1>

                <?php
                echo htmlspecialchars(
                    $business['business_name']
                );
                ?>

            </h1>

            <!-- CATEGORY -->

            <p>

                <strong>Category:</strong>

                <?php
                echo htmlspecialchars(
                    $business['category_name'] ?? 'Business'
                );
                ?>

            </p>

            <!-- LOCATION -->

            <p>

                <strong>Location:</strong>

                <?php
                echo htmlspecialchars(
                    $business['location']
                );
                ?>

            </p>

            <!-- PHONE -->

            <p>

                <strong>Phone:</strong>

                <?php
                echo htmlspecialchars(
                    $business['phone']
                );
                ?>

            </p>

            <!-- DESCRIPTION -->

            <p style="line-height:1.7;">

                <?php

                if (!empty($business['description'])) {

                    echo nl2br(
                        htmlspecialchars(
                            $business['description']
                        )
                    );

                } else {

                    echo "No description available.";

                }

                ?>

            </p>

            <!-- BUTTONS -->

            <div class="profile-buttons">

                <!-- CALL -->

                <a
                    class="btn"
                    href="tel:<?php echo htmlspecialchars($business['phone']); ?>"
                >
                    📞 Call Business
                </a>

                <!-- WHATSAPP -->

                <?php if (!empty($whatsapp)): ?>

                    <a
                        class="btn"
                        href="https://wa.me/<?php echo htmlspecialchars($whatsapp); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        💬 WhatsApp
                    </a>

                <?php endif; ?>

                <!-- FAVORITE -->

                <?php if (isset($_SESSION['user_id'])): ?>

                    <form
                        method="POST"
                        action="../customer/add_favorite.php"
                        class="favorite-form"
                    >

                        <input
                            type="hidden"
                            name="business_id"
                            value="<?php echo $business_id; ?>"
                        >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php
                            echo htmlspecialchars(
                                $_SESSION['csrf_token']
                            );
                            ?>"
                        >

                        <button
                            type="submit"
                            class="btn"
                        >
                            ❤️ Save Business
                        </button>

                    </form>

                <?php endif; ?>

                <!-- BACK -->

                <a
                    class="btn"
                    href="view_businesses.php"
                >
                    ← Back to Businesses
                </a>

            </div>

            <!-- VIEWS -->

            <p
                style="
                    margin-top:20px;
                    color:#6b7280;
                "
            >

                👁️

                <?php
                echo (int)($business['views'] ?? 0);
                ?>

                views

            </p>

        </div>

        <!-- ==========================================
             BUSINESS PHOTOS
        =========================================== -->

        <?php if (!empty($gallery)): ?>

            <div
                class="profile-card"
                style="margin-top:30px;"
            >

                <h2>
                    📸 Business Photos
                </h2>

                <div class="gallery-grid">

                    <?php foreach ($gallery as $image): ?>

                        <img
                            src="../uploads/<?php
                            echo htmlspecialchars($image);
                            ?>"
                            alt="Business photo"
                        >

                    <?php endforeach; ?>

                </div>

            </div>

        <?php endif; ?>

        <!-- ==========================================
             REVIEWS
        =========================================== -->

        <div class="reviews-section">

            <!-- REVIEW FORM -->

            <?php if (isset($_SESSION['user_id'])): ?>

                <div class="review-form">

                    <h2>
                        ⭐ Leave a Review
                    </h2>

                    <?php if (!empty($review_message)): ?>

                        <div class="review-message">

                            <?php
                            echo htmlspecialchars(
                                $review_message
                            );
                            ?>

                        </div>

                    <?php endif; ?>

                    <form method="POST">

                        <!-- CSRF TOKEN -->

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php
                            echo htmlspecialchars(
                                $_SESSION['csrf_token']
                            );
                            ?>"
                        >

                        <textarea
                            name="comment"
                            maxlength="2000"
                            placeholder="Share your experience with this business..."
                            required
                        ></textarea>

                        <button
                            type="submit"
                            name="submit_review"
                            class="btn"
                        >
                            Post Review
                        </button>

                    </form>

                </div>

            <?php endif; ?>

            <!-- REVIEW LIST -->

            <div class="profile-card">

                <h2>
                    ⭐ Customer Reviews
                </h2>

                <?php if (!empty($reviews)): ?>

                    <?php foreach ($reviews as $review): ?>

                        <div class="review-card">

                            <h3>

                                <?php
                                echo htmlspecialchars(
                                    $review['name']
                                );
                                ?>

                            </h3>

                            <p>

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $review['comment']
                                    )
                                );
                                ?>

                            </p>

                            <div class="review-date">

                                <?php
                                echo htmlspecialchars(
                                    date(
                                        "d M Y, H:i",
                                        strtotime(
                                            $review['created_at']
                                        )
                                    )
                                );
                                ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p class="no-reviews">

                        No reviews yet.
                        Be the first to review this business.

                    </p>

                <?php endif; ?>

            </div>

        </div>

    </div>

</section>

<?php include "../includes/footer.php"; ?>

</body>

</html>