<?php
// ALL PHP logic must run before ANY HTML output
session_start();

// Redirect to store if cart is empty
if (empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit;
}

$products = [
    1 => ['name' => 'Rutgers Scarlet Hoodie',             'price' => 49.99,  'available' => true],
    2 => ['name' => 'Retro Leather Basketball',            'price' => 29.95,  'available' => true],
    3 => ['name' => 'Wireless Noise-Canceling Headphones', 'price' => 149.99, 'available' => false],
    4 => ['name' => 'Aluminum Water Bottle (32oz)',         'price' => 18.50,  'available' => true],
];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName  = trim($_POST['first_name']  ?? '');
    $lastName   = trim($_POST['last_name']   ?? '');
    $email      = trim($_POST['email']       ?? '');
    $address    = trim($_POST['address']     ?? '');
    $city       = trim($_POST['city']        ?? '');
    $state      = trim($_POST['state']       ?? '');
    $zip        = trim($_POST['zip']         ?? '');
    $cardName   = trim($_POST['card_name']   ?? '');
    $cardNumber = preg_replace('/\s+/', '', $_POST['card_number'] ?? '');
    $expMonth   = trim($_POST['exp_month']   ?? '');
    $expYear    = trim($_POST['exp_year']    ?? '');
    $cvv        = trim($_POST['cvv']         ?? '');

    if (!$firstName) $errors[] = 'First name is required.';
    if (!$lastName)  $errors[] = 'Last name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
    if (!$address)   $errors[] = 'Street address is required.';
    if (!$city)      $errors[] = 'City is required.';
    if (!$state)     $errors[] = 'State is required.';
    if (!preg_match('/^\d{5}(-\d{4})?$/', $zip)) $errors[] = 'A valid ZIP code is required.';
    if (!$cardName)  $errors[] = 'Name on card is required.';
    if (!preg_match('/^\d{13,19}$/', $cardNumber)) $errors[] = 'A valid card number is required (13–19 digits).';
    if (!$expMonth || !$expYear) $errors[] = 'Expiration date is required.';
    if (!preg_match('/^\d{3,4}$/', $cvv)) $errors[] = 'CVV must be 3 or 4 digits.';

    if (empty($errors)) {
        $orderItems = [];
        $grandTotal = 0;
        foreach ($_SESSION['cart'] as $id => $qty) {
            if (!isset($products[$id])) continue;
            $subtotal = $products[$id]['price'] * $qty;
            $grandTotal += $subtotal;
            $orderItems[] = [
                'name'     => $products[$id]['name'],
                'qty'      => $qty,
                'subtotal' => $subtotal,
            ];
        }
        $_SESSION['last_order'] = [
            'items' => $orderItems,
            'total' => $grandTotal,
            'name'  => "$firstName $lastName",
            'email' => $email,
            'last4' => substr($cardNumber, -4),
        ];
        $_SESSION['cart'] = [];

        $emailBody = "Hi $firstName,\n\nThank you for your order!\n\nOrder Summary:\n";
        foreach ($orderItems as $item) {
            $emailBody .= sprintf("  - %s x%d: $%s\n", $item['name'], $item['qty'], number_format($item['subtotal'], 2));
        }
        $emailBody .= "\nTotal: $" . number_format($grandTotal, 2) . "\n\nShipping to:\n$address, $city, $state $zip\n";
        $headers = "From: webmaster@example.com\r\nReply-To: webmaster@example.com\r\nX-Mailer: PHP/" . phpversion();
        @mail($email, "Order Confirmation – Rutgers-Newark Store", $emailBody, $headers);

        header('Location: confirmation.php');
        exit;
    }
}

// Compute sidebar totals (only reached if GET or POST with errors)
$cartTotal = 0;
$cartItems = [];
foreach ($_SESSION['cart'] as $id => $qty) {
    if (!isset($products[$id])) continue;
    $subtotal = $products[$id]['price'] * $qty;
    $cartTotal += $subtotal;
    $cartItems[] = ['name' => $products[$id]['name'], 'qty' => $qty, 'subtotal' => $subtotal];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout – Rutgers-Newark Store</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }

        header {
            background: #cc0033; color: white;
            padding: 16px 30px; display: flex; align-items: center; gap: 14px;
        }
        header h1 { font-size: 22px; }
        header a { color: white; font-size: 13px; opacity: 0.9; text-decoration: none; margin-left: auto; }
        header a:hover { text-decoration: underline; }

        .page-wrap { max-width: 960px; margin: 30px auto; padding: 0 20px; display: flex; gap: 30px; align-items: flex-start; }
        .panel { background: white; border-radius: 8px; padding: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }

        .payment-form { flex: 3; }
        .payment-form h2 { font-size: 18px; margin-bottom: 20px; }

        .section-label {
            font-size: 11px; font-weight: bold; text-transform: uppercase;
            letter-spacing: 1px; color: #888; margin: 20px 0 10px;
        }
        .section-label:first-of-type { margin-top: 0; }

        .field-row { display: flex; gap: 14px; }
        .field-row .field { flex: 1; }
        .field { margin-bottom: 14px; }
        .field label { display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; }
        .field input, .field select {
            width: 100%; padding: 9px 12px; border: 1px solid #ccc;
            border-radius: 5px; font-size: 14px;
        }
        .field input:focus, .field select:focus {
            outline: none; border-color: #cc0033; box-shadow: 0 0 0 2px rgba(204,0,51,0.15);
        }

        .card-icons { display: flex; gap: 6px; margin-bottom: 10px; }
        .card-icon {
            padding: 3px 8px; border: 1px solid #ddd; border-radius: 4px;
            font-size: 12px; font-weight: bold; color: #555; background: #fafafa;
        }

        .btn-place-order {
            display: block; width: 100%; margin-top: 20px;
            background: #cc0033; color: white; border: none;
            padding: 13px; border-radius: 6px; font-size: 16px;
            font-weight: bold; cursor: pointer;
        }
        .btn-place-order:hover { background: #a30028; }
        .secure-note { text-align: center; color: #888; font-size: 12px; margin-top: 10px; }

        .order-summary { flex: 2; min-width: 240px; position: sticky; top: 20px; }
        .order-summary h2 { font-size: 18px; margin-bottom: 16px; }
        .summary-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 14px; }
        .summary-item span:first-child { color: #555; }
        .summary-total { display: flex; justify-content: space-between; font-size: 17px; font-weight: bold; margin-top: 12px; padding-top: 10px; border-top: 2px solid #333; }

        .alert { background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
    </style>
</head>
<body>

<header>
    <h1>🛒 Checkout</h1>
    <a href="index.php">← Back to Store</a>
</header>

<div class="page-wrap">

    <div class="panel payment-form">
        <h2>Payment Information</h2>

        <?php if (!empty($errors)): ?>
            <div class="alert">
                <?php foreach ($errors as $e): ?>
                    <div>• <?= htmlspecialchars($e) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="section-label">Contact</div>
            <div class="field-row">
                <div class="field">
                    <label>First Name</label>
                    <input type="text" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>Last Name</label>
                    <input type="text" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                </div>
            </div>
            <div class="field">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="section-label">Shipping Address</div>
            <div class="field">
                <label>Street Address</label>
                <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" required>
            </div>
            <div class="field-row">
                <div class="field" style="flex:2">
                    <label>City</label>
                    <input type="text" name="city" value="<?= htmlspecialchars($_POST['city'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>State</label>
                    <input type="text" name="state" maxlength="2" placeholder="NJ" value="<?= htmlspecialchars($_POST['state'] ?? '') ?>" required>
                </div>
                <div class="field">
                    <label>ZIP</label>
                    <input type="text" name="zip" maxlength="10" placeholder="07001" value="<?= htmlspecialchars($_POST['zip'] ?? '') ?>" required>
                </div>
            </div>

            <div class="section-label">Card Details</div>
            <div class="card-icons">
                <span class="card-icon">VISA</span>
                <span class="card-icon">MC</span>
                <span class="card-icon">AMEX</span>
                <span class="card-icon">DISC</span>
            </div>
            <div class="field">
                <label>Name on Card</label>
                <input type="text" name="card_name" value="<?= htmlspecialchars($_POST['card_name'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label>Card Number</label>
                <input type="text" name="card_number" maxlength="19" placeholder="1234 5678 9012 3456"
                       value="<?= htmlspecialchars($_POST['card_number'] ?? '') ?>" required
                       oninput="this.value=this.value.replace(/[^0-9 ]/g,'')">
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Expiry Month</label>
                    <select name="exp_month">
                        <option value="">MM</option>
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= (($_POST['exp_month'] ?? '') == $m) ? 'selected' : '' ?>>
                                <?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Expiry Year</label>
                    <select name="exp_year">
                        <option value="">YYYY</option>
                        <?php for ($y = date('Y'); $y <= date('Y') + 10; $y++): ?>
                            <option value="<?= $y ?>" <?= (($_POST['exp_year'] ?? '') == $y) ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="field">
                    <label>CVV</label>
                    <input type="text" name="cvv" maxlength="4" placeholder="123"
                           value="<?= htmlspecialchars($_POST['cvv'] ?? '') ?>"
                           oninput="this.value=this.value.replace(/\D/g,'')" required>
                </div>
            </div>

            <button type="submit" class="btn-place-order">🔒 Place Order</button>
            <p class="secure-note">🔐 Your information is encrypted and secure.</p>
        </form>
    </div>

    <div class="order-summary">
        <div class="panel">
            <h2>Order Summary</h2>
            <?php foreach ($cartItems as $item): ?>
                <div class="summary-item">
                    <span><?= htmlspecialchars($item['name']) ?> × <?= $item['qty'] ?></span>
                    <span>$<?= number_format($item['subtotal'], 2) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="summary-total">
                <span>Total</span>
                <span>$<?= number_format($cartTotal, 2) ?></span>
            </div>
        </div>
    </div>

</div>
</body>
</html>
