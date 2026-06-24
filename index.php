<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple PHP Shopping Cart</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; }

        header {
            background-color: #cc0033;
            color: white;
            padding: 16px 30px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        header h1 { font-size: 22px; }
        header span { font-size: 13px; opacity: 0.85; }

        .page-wrap { max-width: 1100px; margin: 30px auto; padding: 0 20px; display: flex; gap: 30px; align-items: flex-start; }

        /* --- Products --- */
        .products { flex: 2; }
        .products h2 { margin-bottom: 16px; font-size: 18px; }
        .product-card {
            background: white;
            border-radius: 8px;
            padding: 16px 20px;
            margin-bottom: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .product-name { font-weight: bold; font-size: 15px; }
        .product-price { color: #555; margin-top: 4px; }
        .badge-oos { background: #ffeeba; color: #856404; font-size: 11px; padding: 2px 8px; border-radius: 12px; margin-left: 8px; }
        .btn-add {
            background: #28a745; color: white; border: none;
            padding: 8px 18px; border-radius: 5px; cursor: pointer; font-weight: bold;
        }
        .btn-add:disabled { background: #ccc; cursor: not-allowed; }
        .btn-add:hover:not(:disabled) { background: #218838; }

        /* --- Cart --- */
        .cart { flex: 1; min-width: 280px; }
        .cart-panel {
            background: white; border-radius: 8px; padding: 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08); position: sticky; top: 20px;
        }
        .cart-panel h2 { font-size: 18px; margin-bottom: 14px; }
        .cart-empty { color: #888; font-style: italic; }
        .cart-item { display: flex; align-items: center; gap: 8px; padding: 10px 0; border-bottom: 1px solid #eee; }
        .cart-item-name { flex: 1; font-size: 14px; }
        .qty-controls { display: flex; align-items: center; gap: 4px; }
        .qty-btn {
            width: 26px; height: 26px; border: 1px solid #ccc; background: #f0f0f0;
            border-radius: 4px; cursor: pointer; font-size: 15px; line-height: 1;
        }
        .qty-btn:hover { background: #ddd; }
        .qty-display { width: 28px; text-align: center; font-weight: bold; font-size: 14px; }
        .item-subtotal { font-size: 13px; color: #555; min-width: 52px; text-align: right; }
        .btn-remove { background: none; border: none; color: #cc0033; cursor: pointer; font-size: 16px; padding: 0 4px; }
        .btn-remove:hover { color: #900; }

        .cart-total-row {
            display: flex; justify-content: space-between;
            font-size: 17px; font-weight: bold;
            margin-top: 14px; padding-top: 10px; border-top: 2px solid #333;
        }
        .btn-checkout {
            display: block; width: 100%; margin-top: 14px;
            background: #cc0033; color: white; border: none;
            padding: 12px; border-radius: 6px; font-size: 15px;
            font-weight: bold; cursor: pointer; text-align: center;
            text-decoration: none;
        }
        .btn-checkout:hover { background: #a30028; }
        .btn-clear {
            display: block; width: 100%; margin-top: 8px;
            background: none; color: #888; border: 1px solid #ccc;
            padding: 8px; border-radius: 6px; cursor: pointer; font-size: 13px;
        }
        .btn-clear:hover { background: #f8d7da; color: #721c24; border-color: #f5c6cb; }

        .alert {
            background: #d1ecf1; color: #0c5460; padding: 12px 16px;
            border-radius: 6px; margin-bottom: 20px; font-size: 14px;
        }
        .alert.error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<?php
session_start();

$products = [
    1 => ['name' => 'Rutgers Scarlet Hoodie',             'price' => 49.99,  'available' => true],
    2 => ['name' => 'Retro Leather Basketball',            'price' => 29.95,  'available' => true],
    3 => ['name' => 'Wireless Noise-Canceling Headphones', 'price' => 149.99, 'available' => false],
    4 => ['name' => 'Aluminum Water Bottle (32oz)',         'price' => 18.50,  'available' => true],
];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action']     ?? '';
    $productId = (int)($_POST['product_id'] ?? 0);

    if ($action === 'add') {
        if (isset($products[$productId]) && $products[$productId]['available']) {
            $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
            $message = "Added "" . $products[$productId]['name'] . "" to your cart.";
        } else {
            $message     = "Sorry, that item is unavailable.";
            $messageType = 'error';
        }
    }

    if ($action === 'increase' && isset($products[$productId])) {
        $_SESSION['cart'][$productId] = ($_SESSION['cart'][$productId] ?? 0) + 1;
    }

    if ($action === 'decrease' && isset($products[$productId])) {
        if (($_SESSION['cart'][$productId] ?? 0) > 1) {
            $_SESSION['cart'][$productId]--;
        } else {
            unset($_SESSION['cart'][$productId]);
            $message = "Item removed from cart.";
        }
    }

    if ($action === 'remove' && isset($products[$productId])) {
        unset($_SESSION['cart'][$productId]);
        $message = "Item removed from cart.";
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
        $message = "Your cart has been cleared.";
    }
}

// Calculate cart total
$cartTotal = 0;
foreach ($_SESSION['cart'] as $id => $qty) {
    if (isset($products[$id])) {
        $cartTotal += $products[$id]['price'] * $qty;
    }
}
$cartCount = array_sum($_SESSION['cart']);
?>

<header>
    <div>
        <h1>🛒 Simple Web Store</h1>
        <span>Software Engineering – Final Project</span>
    </div>
</header>

<div class="page-wrap">

    <div class="products">
        <h2>Product Catalog</h2>

        <?php if (!empty($message)): ?>
            <div class="alert <?= $messageType === 'error' ? 'error' : '' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php foreach ($products as $id => $item): ?>
            <div class="product-card">
                <div>
                    <div class="product-name">
                        <?= htmlspecialchars($item['name']) ?>
                        <?php if (!$item['available']): ?>
                            <span class="badge-oos">Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-price">$<?= number_format($item['price'], 2) ?></div>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= $id ?>">
                    <button type="submit" class="btn-add" <?= !$item['available'] ? 'disabled' : '' ?>>
                        Add to Cart
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Cart Panel -->
    <div class="cart">
        <div class="cart-panel">
            <h2>Your Cart <?php if ($cartCount > 0): ?><span style="font-size:13px;color:#888;font-weight:normal">(<?= $cartCount ?> item<?= $cartCount !== 1 ? 's' : '' ?>)</span><?php endif; ?></h2>

            <?php if (empty($_SESSION['cart'])): ?>
                <p class="cart-empty">Your cart is empty.</p>
            <?php else: ?>
                <?php foreach ($_SESSION['cart'] as $id => $qty):
                    if (!isset($products[$id])) continue;
                    $subtotal = $products[$id]['price'] * $qty;
                ?>
                <div class="cart-item">
                    <span class="cart-item-name"><?= htmlspecialchars($products[$id]['name']) ?></span>

                    <!-- Decrease -->
                    <form method="POST">
                        <input type="hidden" name="action" value="decrease">
                        <input type="hidden" name="product_id" value="<?= $id ?>">
                        <button class="qty-btn" type="submit">−</button>
                    </form>

                    <span class="qty-display"><?= $qty ?></span>

                    <!-- Increase -->
                    <form method="POST">
                        <input type="hidden" name="action" value="increase">
                        <input type="hidden" name="product_id" value="<?= $id ?>">
                        <button class="qty-btn" type="submit">+</button>
                    </form>

                    <span class="item-subtotal">$<?= number_format($subtotal, 2) ?></span>

                    <!-- Remove -->
                    <form method="POST">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="product_id" value="<?= $id ?>">
                        <button class="btn-remove" type="submit" title="Remove">✕</button>
                    </form>
                </div>
                <?php endforeach; ?>

                <div class="cart-total-row">
                    <span>Total</span>
                    <span>$<?= number_format($cartTotal, 2) ?></span>
                </div>

                <a href="checkout.php" class="btn-checkout">Proceed to Checkout →</a>

                <form method="POST">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit" class="btn-clear">Clear Cart</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

</div>
</body>
</html>
