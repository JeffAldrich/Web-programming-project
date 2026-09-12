<?php

session_start();

require_once "../php/db.php";

 $is_logged_in = isset($_SESSION["user_id"]);

 $product_id = isset($_GET["id"]) ? (int) $_GET["id"] : 1;

if ($product_id <= 0) {
    $product_id = 1;
}


/* =========================
   GET PRODUCT
========================= */

 $stmt = $conn->prepare("
    SELECT id, name, price, image, description
    FROM products
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to load product.");
}

 $stmt->bind_param("i", $product_id);
 $stmt->execute();

 $product = $stmt->get_result()->fetch_assoc();
 $stmt->close();

if (!$product) {
    die("Product not found.");
}


/* =========================
   GET PRODUCT VARIANTS
========================= */

 $stmt = $conn->prepare("
    SELECT id, size, color, stock
    FROM product_variants
    WHERE product_id = ?
    ORDER BY id ASC
");

if (!$stmt) {
    die("Unable to load product variants.");
}

 $stmt->bind_param("i", $product_id);
 $stmt->execute();

 $variants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
 $stmt->close();


 $variant_data = [];

foreach ($variants as $variant) {

    $variant_data[] = [
        "id" => (int) $variant["id"],
        "size" => $variant["size"],
        "color" => $variant["color"],
        "stock" => (int) $variant["stock"]
    ];
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- JAC PRODUCT PAGE v4 -->

    <title>JAC - <?= htmlspecialchars($product["name"]) ?></title>

    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="product.css">

</head>

<body>


<!-- =========================
     HEADER
========================= -->

<header>

    <img src="../Images/Final LOGO.svg" alt="JAC Logo" class="logo">

    <nav>

        <a href="../index.php#home">HOME</a>
        <a href="../index.php#about">ABOUT US</a>
        <a href="../index.php#collections">COLLECTION</a>
        <a href="../store.php">STORE</a>
        <a href="../index.php#contact">CONTACT</a>

    </nav>


    <div class="account-menu">

        <button type="button" class="account-button">👤</button>

        <div class="account-dropdown">

            <?php if ($is_logged_in): ?>
                <a href="../user%20details/my%20account.php">MY ACCOUNT</a>
                <a href="../user%20details/my%20order.php">MY ORDERS</a>
                <a href="../user%20details/my%20cart.php">MY CART</a>
                <a href="../user%20details/wishlist.php">WISHLIST</a>
                <div class="account-divider"></div>
                <a href="../logout.php" class="logout">LOG OUT</a>
            <?php else: ?>
                <a href="../login.php">LOGIN</a>
                <a href="../register.php">REGISTER</a>
            <?php endif; ?>

        </div>

    </div>

</header>


<!-- =========================
     PRODUCT PAGE
========================= -->

<section class="product-page">

    <div class="product-container">


        <!-- PRODUCT IMAGE -->

        <div class="product-image">

            <?php

            $image = trim($product["image"]);

            if (stripos($image, "images/") === 0) {
                $image_path = "../" . $image;
            } else {
                $image_path = "../Images/" . $image;
            }

            ?>

            <img
                src="<?= htmlspecialchars($image_path) ?>"
                alt="<?= htmlspecialchars($product["name"]) ?>"
            >

        </div>


        <!-- PRODUCT DETAILS -->

        <div class="product-details">

            <h1><?= htmlspecialchars($product["name"]) ?></h1>

            <p class="product-price">₱<?= number_format($product["price"]) ?></p>

            <p class="product-description"><?= htmlspecialchars($product["description"]) ?></p>


            <!-- SIZE -->

            <h3>SIZE</h3>

            <div class="size-options">

                <button type="button" class="size-button" data-size="S">S</button>
                <button type="button" class="size-button" data-size="M">M</button>
                <button type="button" class="size-button" data-size="L">L</button>
                <button type="button" class="size-button" data-size="XL">XL</button>

            </div>


            <!-- COLOR -->

            <h3>COLOR</h3>

            <div class="color-options">

                <button type="button" class="color-button color-black" data-color="Black" title="Black"></button>
                <button type="button" class="color-button color-white" data-color="White" title="White"></button>
                <button type="button" class="color-button color-navy" data-color="Navy" title="Navy"></button>
                <button type="button" class="color-button color-gray" data-color="Gray" title="Gray"></button>

            </div>


            <!-- QUANTITY -->

            <h3>QUANTITY</h3>

            <input type="number" id="quantity" class="product-quantity" value="1" min="1">


            <!-- HIDDEN VARIANT -->

            <input type="hidden" id="variant_id" name="variant_id" value="">


            <!-- STOCK MESSAGE -->

            <p id="stockMessage" class="stock-message"></p>


            <!-- ACTIONS -->

            <div class="product-actions">

                <button type="button" class="add-to-cart" id="addToCartButton">
                    ADD TO CART
                </button>


                <form action="../php/add_to_wishlist.php" method="POST" class="wishlist-form">

                    <input type="hidden" name="product_id" value="<?= $product_id ?>">

                    <button type="submit" class="add-to-wishlist">♡</button>

                </form>

            </div>


            <!-- BACK -->

            <a href="../store.php" class="back-to-collections">BACK TO STORE</a>

        </div>

    </div>

</section>


<!-- =========================
     LOGIN REQUIRED POPUP
========================= -->

<div id="login-required-popup" class="login-required-popup">

    <div class="login-required-box">

        <button type="button" class="login-popup-close" aria-label="Close">×</button>

        <h2>LOGIN REQUIRED</h2>

        <p>Please log in to your JAC account before adding items to your cart.</p>

        <div class="login-popup-actions">

            <a href="../login.php" class="login-popup-login">LOGIN</a>

            <button type="button" class="login-popup-cancel">CANCEL</button>

        </div>

    </div>

</div>


<!-- =========================
     PRODUCT SCRIPT (self-contained)
========================= -->

<script>

    window.JAC_IS_LOGGED_IN = <?= $is_logged_in ? "true" : "false" ?>;
    window.JAC_PRODUCT_ID = <?= $product_id ?>;
    window.JAC_PRODUCT_VARIANTS = <?= json_encode($variant_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;


    /* =========================
       TOAST
    ========================= */

    function showToast(message, isSuccess) {

        var old = document.getElementById("jac-toast");
        if (old) old.remove();

        var toast = document.createElement("div");
        toast.id = "jac-toast";
        toast.textContent = (isSuccess ? "✔ " : "⚠ ") + message;

        toast.style.position = "fixed";
        toast.style.bottom = "25px";
        toast.style.right = "25px";
        toast.style.padding = "15px 25px";
        toast.style.background = isSuccess ? "black" : "#8a2222";
        toast.style.color = "white";
        toast.style.fontSize = "13px";
        toast.style.letterSpacing = "1px";
        toast.style.fontFamily = "inherit";
        toast.style.boxShadow = "0 5px 15px rgba(0,0,0,0.3)";
        toast.style.zIndex = "9999";
        toast.style.opacity = "0";
        toast.style.transition = "opacity 0.3s ease, transform 0.3s ease";
        toast.style.transform = "translateY(10px)";

        document.body.appendChild(toast);

        requestAnimationFrame(function () {
            toast.style.opacity = "1";
            toast.style.transform = "translateY(0)";
        });

        setTimeout(function () {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(10px)";
            setTimeout(function () { toast.remove(); }, 300);
        }, 2500);
    }


    /* =========================
       ELEMENTS
    ========================= */

    const productSizeButtons = document.querySelectorAll(".size-button");
    const productColorButtons = document.querySelectorAll(".color-button");
    const variantInput = document.getElementById("variant_id");
    const quantityInput = document.getElementById("quantity");
    const stockMessage = document.getElementById("stockMessage");
    const addToCartButton = document.getElementById("addToCartButton");

    let selectedSize = "";
    let selectedColor = "";


    /* =========================
       SIZE / COLOR SELECTION
    ========================= */

    productSizeButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            selectedSize = this.dataset.size;
            productSizeButtons.forEach(function (btn) { btn.classList.remove("selected"); });
            this.classList.add("selected");
            updateProductVariant();
        });
    });

    productColorButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            selectedColor = this.dataset.color;
            productColorButtons.forEach(function (btn) { btn.classList.remove("selected"); });
            this.classList.add("selected");
            updateProductVariant();
        });
    });


    /* =========================
       FIND VARIANT
    ========================= */

    function updateProductVariant() {

        variantInput.value = "";
        stockMessage.textContent = "";

        if (selectedSize === "" || selectedColor === "") {
            return;
        }

        const variant = window.JAC_PRODUCT_VARIANTS.find(function (item) {
            return item.size === selectedSize && item.color === selectedColor;
        });

        if (!variant) {
            stockMessage.textContent = "This combination is unavailable.";
            return;
        }

        variantInput.value = variant.id;

        if (variant.stock <= 0) {
            stockMessage.textContent = "OUT OF STOCK";
            return;
        }

        stockMessage.textContent = variant.stock + " available";
        quantityInput.max = variant.stock;
    }


    /* =========================
       QUANTITY LIMIT
    ========================= */

    quantityInput.addEventListener("change", function () {

        const variantId = parseInt(variantInput.value);
        const quantity = parseInt(quantityInput.value);

        const variant = window.JAC_PRODUCT_VARIANTS.find(function (item) {
            return item.id === variantId;
        });

        if (!variant) {
            quantityInput.value = 1;
            return;
        }

        if (quantity < 1) {
            quantityInput.value = 1;
        }

        if (quantity > variant.stock) {
            quantityInput.value = variant.stock;
        }
    });


    /* =========================
       ADD TO CART (AJAX + toast)
    ========================= */

    if (addToCartButton) {

        addToCartButton.addEventListener("click", function () {

            if (!window.JAC_IS_LOGGED_IN) {
                const popup = document.getElementById("login-required-popup");
                if (popup) {
                    popup.classList.add("active");
                    popup.classList.add("show");
                }
                return;
            }

            const variantId = parseInt(variantInput.value);

            if (!variantId) {
                showToast("Please select a size and color.", false);
                return;
            }

            const quantity = parseInt(quantityInput.value);

            if (!quantity || quantity < 1) {
                showToast("Please select a valid quantity.", false);
                return;
            }

            const payload = new FormData();
            payload.append("product_id", window.JAC_PRODUCT_ID);
            payload.append("variant_id", variantId);
            payload.append("quantity", quantity);

            addToCartButton.disabled = true;
            const originalText = addToCartButton.textContent;
            addToCartButton.textContent = "ADDING...";

            fetch("../php/add_to_cart.php", {
                method: "POST",
                body: payload
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (result) {

                if (result.redirect) {
                    window.location.href = result.redirect;
                    return;
                }

                showToast(result.message, result.success);

                if (result.success) {
                    if (typeof window.refreshAccountCounts === 'function') {
                        window.refreshAccountCounts();
                    }
                }
            })
            .catch(function () {
                showToast("Something went wrong. Please try again.", false);
            })
            .finally(function () {
                addToCartButton.disabled = false;
                addToCartButton.textContent = originalText;
            });
        });
    }


    /* =========================
       LOGIN POPUP CLOSE
    ========================= */

    function closeLoginPopup() {
        const popup = document.getElementById("login-required-popup");
        if (popup) {
            popup.classList.remove("active");
            popup.classList.remove("show");
        }
    }

    const loginPopupClose = document.querySelector(".login-popup-close");
    const loginPopupCancel = document.querySelector(".login-popup-cancel");
    const loginPopup = document.getElementById("login-required-popup");

    if (loginPopupClose) {
        loginPopupClose.addEventListener("click", closeLoginPopup);
    }

    if (loginPopupCancel) {
        loginPopupCancel.addEventListener("click", closeLoginPopup);
    }

    if (loginPopup) {
        loginPopup.addEventListener("click", function (event) {
            if (event.target === loginPopup) {
                closeLoginPopup();
            }
        });
    }

    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") {
            closeLoginPopup();
        }

    });

    document.addEventListener("submit", function (e) {

        var form = e.target;

        if (!form.action || form.action.indexOf("add_to_wishlist.php") === -1) {
            return;
        }

        e.preventDefault();

        if (!window.JAC_IS_LOGGED_IN) {
            const popup = document.getElementById("login-required-popup");
            if (popup) {
                popup.classList.add("active");
                popup.classList.add("show");
            }
            return;
        }

        var button = form.querySelector("button[type='submit']");
        if (button) button.disabled = true;

        var data = new FormData(form);
        data.append("ajax", "1");

        fetch(form.action, {
            method: "POST",
            body: data
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (result) {

            if (result.redirect) {
                window.location.href = result.redirect;
                return;
            }

            showToast(result.message, result.success);

            if (result.success && button) {
                button.textContent = "♥";
            }

            if (result.success) {
                if (typeof window.refreshAccountCounts === 'function') {
                    window.refreshAccountCounts();
                }
            }
        })
        .catch(function () {
            showToast("Something went wrong. Please try again.", false);
        })
        .finally(function () {
            if (button) button.disabled = false;
        });
    });

</script>


<script src="../script.js?v=3"></script>


</body>

</html>