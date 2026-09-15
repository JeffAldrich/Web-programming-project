<?php

session_start();

/*
    WELCOME POPUP (NEW)

    grabs the flag set by login.php,
    then clears it so the popup
    only shows ONCE per login
*/

 $welcome_popup = null;

if (isset($_SESSION["welcome_popup"])) {

    $welcome_popup = $_SESSION["welcome_popup"];

    unset($_SESSION["welcome_popup"]);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JAC - Timeless & Limitless</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- HEADER -->
    <header>

        <a href="index.php">
            <img src="Images/Final LOGO.svg" alt="JAC Logo" class="logo">
        </a>

        <nav>
            <a href="#home">HOME</a>
            <a href="#about">ABOUT US</a>
            <a href="#collections">COLLECTION</a>
            <a href="store.php">STORE</a>
            <a href="#contact">CONTACT</a>
        </nav>

        <div class="account-menu">

            <button class="account-button">👤</button>

            <div class="account-dropdown">
                <?php if (isset($_SESSION["user_id"])): ?>
                    <a href="user%20details/my%20account.php">MY ACCOUNT</a>
                    <a href="user%20details/my%20order.php">MY ORDERS</a>
                    <a href="user%20details/my%20cart.php">MY CART</a>
                    <a href="user%20details/wishlist.php">WISHLIST</a>
                    <div class="account-divider"></div>
                    <a href="logout.php" class="logout">LOG OUT</a>
                <?php else: ?>
                    <a href="login.php">LOGIN</a>
                    <a href="register.php">REGISTER</a>
                <?php endif; ?>
            </div>

        </div>

    </header>


    <!-- HOME -->
    <section id="home">

        <div class="hero-image">
            <img src="images/four dudes.jpeg" alt="JAC Suit">
        </div>

        <div class="hero-text">

            <p class="hero-small">TIMELESS & LIMITLESS</p>

            <h2>
                Classic tailoring,<br>
                Modern confidence<br>
                Made to last.
            </h2>

            <p>
                Discover timeless suits crafted with minimalist design
                and exceptional quality.
            </p>

            <a href="#collections" class="hero-register-button">
                SHOP COLLECTION
            </a>

        </div>

    </section>


    <!-- ABOUT US -->
    <section id="about">

        <h2>ABOUT US</h2>

        <p>
            We are a contemporary fashion brand dedicated to creating
            timeless suits through minimalist design and exceptional
            craftsmanship. We believe that simplicity, quality, and
            elegance never go out of style, delivering apparel that
            inspires confidence for every occasion.
        </p>

        <div class="about-image">

            <img
                src="images/vecteezy_ai-generated-an-image-of-men-s-suits-in-a-boutique-fashion_37112305.jpeg"
                alt="About JAC"
            >

        </div>

        <div class="about-info">

            <p class="about-small">THE JAC PHILOSOPHY</p>

            <h3>TIMELESS STYLE. CONFIDENTLY YOURS.</h3>

            <p>
                JAC is built around the belief that true style does not
                need to follow every trend. We create refined pieces
                that balance classic tailoring with modern simplicity,
                allowing every individual to express confidence through
                what they wear.
            </p>

            <div class="about-values">

                <div>
                    <h4>TIMELESS DESIGN</h4>

                    <p>
                        Classic designs created to remain elegant beyond
                        changing trends.
                    </p>
                </div>

                <div>
                    <h4>CRAFTSMANSHIP</h4>

                    <p>
                        Attention to detail and quality are at the heart
                        of every JAC piece.
                    </p>
                </div>

                <div>
                    <h4>CONFIDENCE</h4>

                    <p>
                        Designed to help you look refined and feel
                        confident for every occasion.
                    </p>
                </div>

            </div>

        </div>

    </section>


    <!-- COLLECTIONS -->
    <section id="collections">

        <h2>COLLECTIONS</h2>

        <div class="collection-container">

            <article>
                <a href="product%20page/product.php?id=1" style="text-decoration: none; color: inherit; display: block;">
                    <img
                        src="images/single-breasted.jpg"
                        alt="Single Breasted Suit"
                    >
                    <h3>Single Breasted</h3>
                </a>
            </article>

            <article>
                <a href="product%20page/product.php?id=2" style="text-decoration: none; color: inherit; display: block;">
                    <img
                        src="images/double breasted.jpg"
                        alt="Double Breasted Suit"
                    >
                    <h3>Double Breasted</h3>
                </a>
            </article>

            <article>
                <a href="product%20page/product.php?id=3" style="text-decoration: none; color: inherit; display: block;">
                    <img
                        src="images/Three-Piece Suit.jpg"
                        alt="Three-Piece Suit"
                    >
                    <h3>Three-Piece</h3>
                </a>
            </article>

            <article>
                <a href="product%20page/product.php?id=4" style="text-decoration: none; color: inherit; display: block;">
                    <img
                        src="images/peak lapel suit.jpg"
                        alt="Peak Lapel Suit"
                    >
                    <h3>Peak Lapel</h3>
                </a>
            </article>

            <article>
                <a href="product%20page/product.php?id=5" style="text-decoration: none; color: inherit; display: block;">
                    <img
                        src="images/shawl lepel suit.jpg"
                        alt="Shawl Lapel Suit"
                    >
                    <h3>Shawl Lapel</h3>
                </a>
            </article>

        </div>

    </section>


    <!-- BRAND STATEMENT -->
    <section class="brand-statement">

        <h2>
            CRAFTED FOR THE MOMENT.<br>
            DESIGNED BEYOND IT.
        </h2>

        <p>
            Timeless pieces created with precision, confidence,
            and a vision that goes beyond the ordinary.
        </p>

    </section>


    <!-- PROMOTIONAL SECTION -->
    <section class="promo">

        <div class="promo-text">

            <h2>COLLECTION, ENDLESS POSSIBILITIES</h2>

            <p>
                Timeless suits and refined essentials,
                designed to complete every look.
            </p>

            <a href="store.php" class="promo-button">
                SHOP NOW
            </a>

        </div>

        <div class="promo-image">

            <img src="images/suit.png" alt="JAC Suit">

        </div>

    </section>


    <!-- OCCASION -->
    <section class="occasion">

        <div>

            <p>MINIMAL & TIMELESS</p>

            <h2>
                REFINED FOR<br>
                EVERY OCCASION
            </h2>

        </div>

        <div class="occasion-image">

            <img
                src="images/gettyimages-1293366109-612x612.jpg"
                alt="Suit for Every Occasion"
            >

        </div>

    </section>


    <!-- CONTACT -->
    <section id="contact">

        <div class="contact-image">

            <img
                src="images/man in car.webp"
                alt="JAC Suit"
            >

        </div>

        <div class="contact-content">

            <h2>CONTACT US</h2>

            <h3>Our Promise to You</h3>

            <ul>
                <li>Expert Craftsmanship</li>
                <li>Quality Materials</li>
                <li>Personalized Styling Advice</li>
                <li>Fast, Reliable Shipping</li>
                <li>Sustainability Commitment</li>
                <li>Satisfaction Guarantee</li>
            </ul>

            <div class="contact-details">

                <p>
                    E-mail:<br>
                    JACTimeless&Limitless@gmail.com
                </p>

                <p>
                    Phone:<br>
                    +63 912 345 6789
                </p>

            </div>

        </div>

    </section>


    <!-- NEWSLETTER -->
    <section class="newsletter-section">

        <div class="newsletter-left">

            <h2>NEWSLETTER</h2>

            <p>
                Stay updated with JAC and be the first to discover
                new collections, exclusive offers, and timeless
                style inspiration.
            </p>

            <form id="newsletter-form" onsubmit="return false;">

                <input
                    type="email"
                    id="newsletter-email"
                    name="email"
                    placeholder="Enter your e-mail"
                    required
                >

                <button type="submit">
                    SUBSCRIBE
                </button>

            </form>

        </div>

        <div class="newsletter-right">

            <div>

                <h3>CUSTOMER</h3>

                <a href="login.php">LOGIN</a>
                <a href="register.php">REGISTER</a>

            </div>

            <div>

                <h3>INFO</h3>

                <a href="#home">HOME</a>
                <a href="#about">ABOUT US</a>
                <a href="#collections">COLLECTIONS</a>
                <a href="#contact">CONTACT</a>

            </div>

        </div>

    </section>


    <!-- FOOTER -->
    <footer>

        <div class="footer-bottom">

            <p>© 2026 All Rights Reserved.</p>

            <div class="footer-right">

                <a href="legal/privacy.html">PRIVACY POLICY</a>
                <a href="legal/terms.html">TERMS OF USE</a>

                <div class="footer-social">

                    <a href="#">
                        <img src="images/instagram.png" alt="Instagram">
                    </a>

                    <a href="#">
                        <img src="images/twitter.png" alt="Twitter">
                    </a>

                    <a href="#">
                        <img src="images/facebook.png" alt="Facebook">
                    </a>

                </div>

            </div>

        </div>

    </footer>


        <!-- WELCOME POPUP -->
    <?php if ($welcome_popup !== null): ?>

        <div class="welcome-overlay" id="welcomeOverlay">

            <div class="welcome-popup">

                <button class="welcome-close" id="welcomeClose" aria-label="Close">
                    ×
                </button>

                <?php if ($welcome_popup === "back"): ?>

                    <p class="welcome-label">WELCOME BACK</p>

                    <h3>
                        <?= strtoupper(htmlspecialchars($_SESSION["first_name"] ?? "FRIEND")) ?>
                    </h3>

                    <hr class="welcome-divider">

                    <p class="welcome-text">
                        It's great to see you again.<br>
                        Your timeless collection awaits.
                    </p>

                <?php else: ?>

                    <p class="welcome-label">WELCOME TO JAC</p>

                    <h3>
                        <?= strtoupper(htmlspecialchars($_SESSION["first_name"] ?? "FRIEND")) ?>
                    </h3>

                    <hr class="welcome-divider">

                    <p class="welcome-text">
                        Thank you for joining us.<br>
                        Discover the collection made to last.
                    </p>

                <?php endif; ?>

                <p class="welcome-signature">— JAC —</p>

                <button class="welcome-continue" id="welcomeContinue">
                    CONTINUE SHOPPING
                </button>

            </div>

        </div>

        <script>
            (function () {

                var overlay = document.getElementById("welcomeOverlay");

                function closeWelcome() {
                    overlay.classList.add("is-closing");
                    setTimeout(function () {
                        overlay.remove();
                    }, 250);
                }

                document.getElementById("welcomeContinue")
                    .addEventListener("click", closeWelcome);

                document.getElementById("welcomeClose")
                    .addEventListener("click", closeWelcome);

                /* click on the dark backdrop closes it too */
                overlay.addEventListener("click", function (event) {
                    if (event.target === overlay) {
                        closeWelcome();
                    }
                });

                /* Escape key closes it */
                document.addEventListener("keydown", function (event) {
                    if (event.key === "Escape") {
                        closeWelcome();
                    }
                });

            })();
        </script>

    <?php endif; ?>




    <script src="script.js?v=1789447156"></script>


<script>
(function() {
    var form = document.getElementById("newsletter-form");
    if (!form) return;

    form.addEventListener("submit", function(e) {
        e.preventDefault();
        e.stopPropagation();

        var input = document.getElementById("newsletter-email") || form.querySelector("input[type='email']");
        var btn = form.querySelector("button");
        var email = input ? input.value.trim() : "";

        if (!email) {
            if (typeof showToast === "function") showToast("Please enter your e-mail.", false);
            return;
        }

        if (btn) btn.disabled = true;

        var data = new FormData();
        data.append("email", email);

        fetch("php/subscribe.php", {
            method: "POST",
            body: data
        })
        .then(function(res) {
            return res.json();
        })
        .then(function(res) {
            if (typeof showToast === "function") {
                showToast(res.message, res.already ? false : res.success);
            } else {
                alert(res.message);
            }
            if (res.success && !res.already && input) {
                input.value = "";
            }
        })
        .catch(function(err) {
            if (typeof showToast === "function") {
                showToast("Something went wrong. Please try again.", false);
            } else {
                alert("Something went wrong.");
            }
        })
        .finally(function() {
            if (btn) btn.disabled = false;
        });
    });
})();
</script>

</body>

</html>