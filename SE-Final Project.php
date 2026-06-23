<!DOCTYPE html>
<html>
<h1> Software Engineering-Final Project</h1>

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

</body>
</html>