<?php

session_start();

$pageTitle = "Contact ConnectZW | Get In Touch";

include 'includes/config.php';


/* =========================================
   CSRF TOKEN
========================================= */

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


/* =========================================
   FORM VARIABLES
========================================= */

$success = "";
$error = "";

$name = "";
$email = "";
$subject = "";
$message = "";


/* =========================================
   HANDLE CONTACT FORM
========================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* =========================================
       VERIFY CSRF TOKEN
    ========================================= */

    $csrf_token = $_POST['csrf_token'] ?? "";

    if (
        empty($csrf_token) ||
        !hash_equals($_SESSION['csrf_token'], $csrf_token)
    ) {

        $error = "Invalid form submission. Please refresh the page and try again.";

    } else {

        /* =========================================
           GET FORM DATA
        ========================================= */

        $name = trim($_POST["name"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $subject = trim($_POST["subject"] ?? "");
        $message = trim($_POST["message"] ?? "");


        /* =========================================
           VALIDATION
        ========================================= */

        if (
            $name === "" ||
            $email === "" ||
            $subject === "" ||
            $message === ""
        ) {

            $error = "Please complete all fields.";

        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $error = "Please enter a valid email address.";

        } elseif (strlen($name) > 100) {

            $error = "Your name is too long.";

        } elseif (strlen($email) > 150) {

            $error = "Your email address is too long.";

        } elseif (strlen($subject) > 200) {

            $error = "Your subject is too long.";

        } elseif (strlen($message) > 5000) {

            $error = "Your message is too long. Please keep it under 5000 characters.";

        } else {

            /* =========================================
               SAVE MESSAGE
            ========================================= */

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO contact_messages
                (name, email, subject, message)
                VALUES (?, ?, ?, ?)"
            );

            if ($stmt) {

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssss",
                    $name,
                    $email,
                    $subject,
                    $message
                );

                if (mysqli_stmt_execute($stmt)) {

                    $success = "Your message has been sent successfully. We will get back to you soon.";

                    /* Clear form */
                    $name = "";
                    $email = "";
                    $subject = "";
                    $message = "";

                    /*
                     * Generate a new token after successful submission.
                     */
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

                } else {

                    $error = "Something went wrong while sending your message. Please try again.";
                }

                mysqli_stmt_close($stmt);

            } else {

                $error = "Unable to process your message at the moment.";
            }
        }
    }
}


include 'includes/header.php';
include 'includes/navbar.php';

?>


<!-- =========================================
     CONTACT HERO
========================================= -->

<section class="hero">

    <div class="container">

        <h1>Contact ConnectZW</h1>

        <p>
            We are here to help customers and businesses
            connect better.
        </p>

    </div>

</section>


<!-- =========================================
     CONTACT INFORMATION
========================================= -->

<section class="categories">

    <div class="container">

        <div class="features">

            <div class="card">

                <h3>📧 Email Support</h3>

                <p>
                    support@connectzw.co.zw
                </p>

                <p>
                    For questions, account support
                    and business assistance.
                </p>

            </div>


            <div class="card">

                <h3>📞 Phone Support</h3>

                <p>
                    +263 771782348
                </p>

                <p>
                    Available for business registration
                    assistance.
                </p>

            </div>


            <div class="card">

                <h3>📍 Location</h3>

                <p>
                    Zimbabwe
                </p>

                <p>
                    Connecting businesses nationwide.
                </p>

            </div>

        </div>

    </div>

</section>


<!-- =========================================
     CONTACT FORM
========================================= -->

<section class="why-section">

    <div class="container">

        <h2>Send Us A Message</h2>

        <p>
            Have a question, suggestion or need help?
            Send us a message and we'll get back to you.
        </p>


        <?php if ($success !== ""): ?>

            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form
            class="filter-box"
            method="POST"
            action="contact.php"
        >

            <!-- CSRF PROTECTION -->
            <input
                type="hidden"
                name="csrf_token"
                value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"
            >


            <input
                type="text"
                name="name"
                placeholder="Your Name"
                value="<?php echo htmlspecialchars($name); ?>"
                maxlength="100"
                required
            >


            <input
                type="email"
                name="email"
                placeholder="Your Email"
                value="<?php echo htmlspecialchars($email); ?>"
                maxlength="150"
                required
            >


            <input
                type="text"
                name="subject"
                placeholder="Subject"
                value="<?php echo htmlspecialchars($subject); ?>"
                maxlength="200"
                required
            >


            <textarea
                name="message"
                placeholder="Write your message here..."
                maxlength="5000"
                required
            ><?php echo htmlspecialchars($message); ?></textarea>


            <button type="submit" class="btn">
                Send Message
            </button>

        </form>

    </div>

</section>


<!-- =========================================
     BUSINESS CTA
========================================= -->

<section class="cta">

    <div class="container">

        <h2>Are You A Business Owner?</h2>

        <p>
            Join ConnectZW and make your business
            easier to discover.
        </p>

        <a href="register.php" class="btn">
            Register Your Business
        </a>

    </div>

</section>


<?php include 'includes/footer.php'; ?>