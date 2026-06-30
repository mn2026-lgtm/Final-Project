<?php
session_start();

// Hardcoded user accounts (simple array - no database)
$users = [
    'student'  => 'password123',
    'admin'    => 'admin123',
    'testuser' => 'test123',
];

// If already logged in, go straight to store
if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (isset($users[$username]) && $users[$username] === $password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username']  = $username;
        header('Location: index.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login – Simple Web Store</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            background: white;
            border-radius: 8px;
            padding: 36px;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }

        .login-card h1 {
            font-size: 22px;
            text-align: center;
            margin-bottom: 4px;
        }
        .login-card .sub {
            text-align: center;
            color: #888;
            font-size: 13px;
            margin-bottom: 24px;
        }

        .field { margin-bottom: 16px; }
        .field label { display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; }
        .field input {
            width: 100%; padding: 10px 12px; border: 1px solid #ccc;
            border-radius: 5px; font-size: 14px;
        }
        .field input:focus {
            outline: none; border-color: #cc0033; box-shadow: 0 0 0 2px rgba(204,0,51,0.15);
        }

        .btn-login {
            display: block; width: 100%; margin-top: 8px;
            background: #cc0033; color: white; border: none;
            padding: 12px; border-radius: 6px; font-size: 15px;
            font-weight: bold; cursor: pointer;
        }
        .btn-login:hover { background: #a30028; }

        .alert {
            background: #f8d7da; color: #721c24; padding: 10px 14px;
            border-radius: 6px; margin-bottom: 16px; font-size: 13px;
        }

        .test-creds {
            margin-top: 24px; padding-top: 16px; border-top: 1px solid #eee;
            font-size: 12px; color: #888; line-height: 1.6;
        }
        .test-creds strong { color: #555; }
    </style>
</head>
<body>

    <div class="login-card">
        <h1>🛒 Simple Web Store</h1>
        <p class="sub">Sign in to start shopping</p>

        <?php if (!empty($error)): ?>
            <div class="alert"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="field">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" autofocus required>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Log In</button>
        </form>

        <div class="test-creds">
            <strong>Test credentials:</strong><br>
            student / password123<br>
            admin / admin123<br>
            testuser / test123
        </div>
    </div>

</body>
</html>
