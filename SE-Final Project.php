<!DOCTYPE html>
<html>

<body>
<?php
// 1. Start the session to track cart items
session_start();

// 2. Define the product catalog (Mock Database)
$products = [
    1 => ['name' => 'Rutgers Scarlet Hoodie', 'price' => 49.99, 'available' => true],
    2 => ['name' => 'Retro Leather Basketball', 'price' => 29.95, 'available' => true],
    3 => ['name' => 'Wireless Noise-Canceling Headphones', 'price' => 149.99, 'available' => false],
    4 => ['name' => 'Aluminum Water Bottle (32oz)', 'price' => 18.50, 'available' => true],
];

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = []; // Array layout: [product_id => quantity]
}

// 3. Handle Actions (Add, Clear, Checkout)
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Action: Add to Cart
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $productId = (int)$_POST['product_id'];
        
        if (isset($products[$productId]) && $products[$productId]['available']) {
            if (isset($_SESSION['cart'][$productId])) {
                $_SESSION['cart'][$productId]++;
            } else {
                $_SESSION['cart'][$productId] = 1;
            }
            $message = "Added '" . $products[$productId]['name'] . "' to your cart.";
        } else {
            $message = "Error: Item is unavailable.";
        }
    }

    // Action: Clear Cart
    if (isset($_POST['action']) && $_POST['action'] === 'clear') {
        $_SESSION['cart'] = [];
        $message = "Your cart has been emptied.";
    }

    // Action: Checkout & Send Email
    if (isset($_POST['action']) && $_POST['action'] === 'checkout') {
        if (!empty($_SESSION['cart'])) {
            
            // Build the email body details dynamically
            $emailTo = "your-email@example.com"; // <-- REPLACE WITH YOUR EMAIL
            $subject = "New Order Confirmation - Web Store";
            
            $emailBody = "You received a new order!\n\n";
            $emailBody .= "Order Details:\n";
            $emailBody .= "-------------------------------------------\n";
            
            $grandTotal = 0;
            foreach ($_SESSION['cart'] as $id => $quantity) {
                $itemTotal = $products[$id]['price'] * $quantity;
                $grandTotal += $itemTotal;
                $emailBody .= sprintf(
                    "- %s x%d: $%s\n", 
                    $products[$id]['name'], 
                    $quantity, 
                    number_format($itemTotal, 2)
                );
            }
            
            $emailBody .= "-------------------------------------------\n";
            $emailBody .= "Grand Total: $" . number_format($grandTotal, 2) . "\n\n";
            $emailBody .= "Sent automatically by your PHP Shopping Cart application.";
            
            $headers = "From: webmaster@example.com\r\n" .
                       "Reply-To: webmaster@example.com\r\n" .
                       "X-Mailer: PHP/" . phpversion();

            // Send the email
            if (mail($emailTo, $subject, $emailBody, $headers)) {
                $message = "Thank you! Your order has been placed and a confirmation email was sent.";
                $_SESSION['cart'] = []; // Clear cart on success
            } else {
                $message = "Order processed, but the email server failed to send the confirmation notification.";
            }
        } else {
            $message = "Your cart is empty. Cannot checkout.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simple PHP Shopping Cart</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background-color: #f9f9f9; }
        .container { display: flex; gap: 40px; }
        .panel { background: white; padding: 20px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .products-list { flex: 2; }
        .cart-summary { flex: 1; min-width: 300px; }
        .product-item { border-bottom: 1px solid #eee; padding: 15px 0; display: flex; justify-content: space-between; align-items: center; }
        .product-item:last-child { border-bottom: none; }
        .btn { padding: 6px 12px; cursor: pointer; border-radius: 4px; border: none; font-weight: bold; }
        .btn-add { background-color: #28a745; color: white; }
        .btn-add:disabled { background-color: #ccc; cursor: not-allowed; }
        .btn-checkout { background-color: #007bff; color: white; width: 100%; padding: 10px; margin-top: 10px; }
        .btn-clear { background-color: #dc3545; color: white; font-size: 12px; }
        .alert { background-color: #d1ecf1; color: #0c5460; padding: 12px; border-radius: 4px; margin-bottom: 20px; }
        .text-muted { color: #888; font-style: italic; }
        .cart-total { font-size: 18px; font-weight: bold; border-top: 2px solid #333; padding-top: 10px; margin-top: 15px; }
    </style>
</head>
<body>

    <h1>Simple Web Store</h1>

    <?php if (!empty($message)): ?>
        <div class="alert"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="container">
        <div class="panel products-list">
            <h2>Product Catalog</h2>
            <?php foreach ($products as $id => $details): ?>
                <div class="product-item">
                    <div>
                        <strong><?php echo htmlspecialchars($details['name']); ?></strong><br>
                        <span>$<?php echo number_format($details['price'], 2); ?></span>
                        <?php if (!$details['available']): ?>
                            <span class="text-muted">(Out of Stock)</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <form method="POST" action="">
                            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" class="btn btn-add" <?php echo !$details['available'] ? 'disabled' : ''; ?>>
                                Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="panel cart-summary">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>Your Cart</h2>
                <?php if (!empty($_SESSION['cart'])): ?>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-clear">Clear All</button>
                    </form>
                <?php endif; ?>
            </div>

            <?php if (empty($_SESSION['cart'])): ?>
                <p class="text-muted">Your cart is empty.</p>
            <?php else: ?>
                <?php $total = 0; ?>
                <ul>
                    <?php foreach ($_SESSION['cart'] as $id => $quantity): ?>
                        <?php 
                        $itemCost = $products[$id]['price'] * $quantity; 
                        $total += $itemCost;
                        ?>
                        <li>
                            <?php echo htmlspecialchars($products[$id]['name']); ?> 
                            <strong>x<?php echo $quantity; ?></strong> 
                            ($<?php echo number_format($itemCost, 2); ?>)
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="cart-total">
                    Total: $<?php echo number_format($total, 2); ?>
                </div>

                <form method="POST" action="">
                    <input type="hidden" name="action" value="checkout">
                    <button type="submit" class="btn btn-checkout">Place Order & Email Receipt</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
