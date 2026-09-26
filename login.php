```php
<?php

session_start();

include 'includes/config.php';

$error = "";


/* =========================================
   LOGIN PROCESS
========================================= */

if (isset($_POST['login'])) {

    // Get submitted values safely
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if ($email === '' || $password === '') {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /* =========================================
           FIND USER
        ========================================= */

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id, name, email, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if ($stmt) {

            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) === 1) {

                $user = mysqli_fetch_assoc($result);

                $passwordValid = false;
                $oldPlainTextPassword = false;


                /* =========================================
                   CHECK PASSWORD
                ========================================= */

                // Check secure hashed password
                if (password_verify($password, $user['password'])) {

                    $passwordValid = true;

                }
                // Temporary support for old plain-text passwords
                elseif ($password === $user['password']) {

                    $passwordValid = true;
                    $oldPlainTextPassword = true;
                }


                /* =========================================
                   PASSWORD VALID
                ========================================= */

                if ($passwordValid) {


                    /* =========================================
                       CONVERT OLD PASSWORD TO HASH
                    ========================================= */

                    if ($oldPlainTextPassword) {

                        $newHash = password_hash(
                            $password,
                            PASSWORD_DEFAULT
                        );

                        $update = mysqli_prepare(
                            $conn,
                            "UPDATE users
                             SET password = ?
                             WHERE id = ?"
                        );

                        if ($update) {

                            mysqli_stmt_bind_param(
                                $update,
                                "si",
                                $newHash,
                                $user['id']
                            );

                            mysqli_stmt_execute($update);

                            mysqli_stmt_close($update);
                        }
                    }


                    /* =========================================
                       PREVENT SESSION FIXATION
                    ========================================= */

                    session_regenerate_id(true);


                    /* =========================================
                       CREATE USER SESSION
                    ========================================= */

                    $_SESSION['user_id'] = (int)$user['id'];

                    $_SESSION['name'] = $user['name'];

                    $_SESSION['role'] = $user['role'];


                    /* =========================================
                       REDIRECT BY ROLE
                    ========================================= */

                    if ($user['role'] === "business") {

                        header("Location: business/business_dashboard.php");
                        exit();

                    } elseif ($user['role'] === "admin") {

                        header("Location: admin/dashboard.php");
                        exit();

                    } else {

                        header("Location: index.php");
                        exit();
                    }


                } else {

                    $error = "Invalid email or password.";
                }

            } else {

                $error = "Invalid email or password.";
            }

            mysqli_stmt_close($stmt);

        } else {

            $error = "Unable to process login. Please try again.";
        }
    }
}


$pageTitle = "Login | ConnectZW";

include 'includes/header.php';
include 'includes/navbar.php';

?>


<section class="search-page">

    <div class="container">

        <div class="card">

            <h1>Login</h1>


            <?php if ($error !== "") { ?>

                <p>
                    <?php echo htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </p>

            <?php } ?>


            <form method="POST">

                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    required
                    autocomplete="email"
                >


                <br><br>


                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                >


                <br><br>


                <button type="submit" name="login">
                    Login
                </button>

            </form>

        </div>

    </div>

</section>


<?php include 'includes/footer.php'; ?>
```
