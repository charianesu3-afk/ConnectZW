<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>

<nav class="navbar">

    <div class="nav-container">


        <!-- BRAND -->

        <div class="nav-brand">

            <a href="/connectzw/index.php">
                🌍 ConnectZW
            </a>

        </div>


        <!-- MOBILE BUTTON -->

        <button
            class="menu-toggle"
            type="button"
            onclick="toggleMenu()"
            aria-label="Open navigation menu"
        >
            ☰
        </button>


        <!-- LINKS -->

        <div class="nav-links" id="navLinks">


            <!-- HOME -->

            <a href="/connectzw/index.php">
                Home
            </a>


            <!-- BUSINESSES -->

            <a href="/connectzw/business/view_businesses.php">
                Businesses
            </a>


            <?php if (isset($_SESSION['user_id'])): ?>


                <!-- BUSINESS OWNER -->

                <?php if (
                    isset($_SESSION['role']) &&
                    $_SESSION['role'] === "business"
                ): ?>

                    <a href="/connectzw/business/business_dashboard.php">
                        🏪 My Business
                    </a>

                <?php endif; ?>


                <!-- CUSTOMER FAVORITES -->

                <?php if (
                    isset($_SESSION['role']) &&
                    $_SESSION['role'] === "customer"
                ): ?>

                    <a href="/connectzw/customer/my_favorites.php">
                        ❤️ Favorites
                    </a>

                    <a href="/connectzw/customer/my_reviews.php">
                        ⭐ My Reviews
                    </a>

                <?php endif; ?>


                <!-- ADMIN -->

                <?php if (
                    isset($_SESSION['role']) &&
                    $_SESSION['role'] === "admin"
                ): ?>

                    <a href="/connectzw/admin/dashboard.php">
                        ⚙ Admin
                    </a>

                <?php endif; ?>


                <!-- USER MENU -->

                <div class="user-menu">

                    <span>
                        👤
                        <?php
                        echo htmlspecialchars(
                            $_SESSION['name'] ?? 'User'
                        );
                        ?>
                    </span>


                    <a
                        href="/connectzw/logout.php"
                        class="logout-btn"
                    >
                        Logout
                    </a>

                </div>


            <?php else: ?>


                <!-- GUEST -->

                <a href="/connectzw/login.php">
                    Login
                </a>


                <a
                    href="/connectzw/register.php"
                    class="register-btn"
                >
                    Join ConnectZW
                </a>


            <?php endif; ?>


        </div>

    </div>

</nav>


<script>

function toggleMenu() {

    const menu = document.getElementById("navLinks");

    menu.classList.toggle("active");

}

</script>