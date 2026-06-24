<?php
session_start();

if (empty($_SESSION['last_order'])) {
    header('Location: index.php');
    exit;
}

$order = $_SESSION['last_order'];
unset($_SESSION['last_order']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Confirmed – Simple Web Store</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f4f4f4; color: #333; }

        header { background: #cc0033; color: white; padding: 16px 30px; }
        header h1 { font-size: 22px; }

        .page-wrap { max-width: 620px; margin: 40px auto; padding: 0 20px; }
        .panel { background: white; border-radius: 8px; padding: 32px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); text-align: center; }

        .check-icon { font-size: 56px; margin-bottom: 14px; }
        .panel h2 { font-size: 24px; margin-bottom: 8px; color: #28a745; }
        .panel .sub { color: #666; font-size: 15px; margin-bottom: 24px; }

        .order-details { text-align: left; border-top: 1px solid #eee; padding-top: 20px; }
        .order-details h3 { font-size: 15px; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #888; }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .detail-row span:first-child { color: #555; }
        .total-row { display: flex; justify-content: space-between; font-size: 17px; font-weight: bold; margin-top: 12px; padding-top: 10px; border-top: 2px solid #333; }

        .info-box { background: #f8f9fa; border-radius: 6px; padding: 14px 16px; margin-top: 20px; font-size: 13px; color: #555; text-align: left; line-height: 1.6; }

        .btn-continue {
            display: inline-block; margin-top: 24px;
            background: #cc0033; color: white; border: none;
            padding: 12px 28px; border-radius: 6px; font-size: 15px;
            font-weight: bold; cursor: pointer; text-decoration: none;
        }
        .btn-continue:hover { background: #a30028; }
    </style>
</head>
<body>

<header>
    <h1>🛒 Simple Web Store</h1>
</header>

<div class="page-wrap">
    <div class="panel">
        <div class="check-icon">✅</div>
        <h2>Order Confirmed!</h2>
        <p class="sub">
            Thanks, <?= htmlspecialchars($order['name']) ?>!
            A confirmation was sent to <strong><?= htmlspecialchars($order['email']) ?></strong>.
        </p>

        <div class="order-details">
            <h3>What you ordered</h3>
            <?php foreach ($order['items'] as $item): ?>
                <div class="detail-row">
                    <span><?= htmlspecialchars($item['name']) ?> × <?= $item['qty'] ?></span>
                    <span>$<?= number_format($item['subtotal'], 2) ?></span>
                </div>
            <?php endforeach; ?>
            <div class="total-row">
                <span>Total Charged</span>
                <span>$<?= number_format($order['total'], 2) ?></span>
            </div>
        </div>

        <div class="info-box">
            💳 &nbsp;Card ending in <strong><?= htmlspecialchars($order['last4']) ?></strong> was charged.<br>
            📦 &nbsp;Your order will be processed and shipped within 2–5 business days.
        </div>

        <a href="index.php" class="btn-continue">← Continue Shopping</a>
    </div>
</div>

</body>
</html>
