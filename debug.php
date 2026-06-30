<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// Bump a counter each time this page loads, to prove session persistence
$_SESSION['debug_counter'] = ($_SESSION['debug_counter'] ?? 0) + 1;
?>
<!DOCTYPE html>
<html>
<head><title>Session Debug</title>
<style>body{font-family:monospace; padding:30px; line-height:1.8;}</style>
</head>
<body>
<h2>Session Debug Info</h2>

<p><strong>Session ID:</strong> <?= htmlspecialchars(session_id()) ?></p>
<p><strong>Counter (reload this page — should go up each time):</strong> <?= $_SESSION['debug_counter'] ?></p>
<p><strong>logged_in in session:</strong> <?= isset($_SESSION['logged_in']) ? var_export($_SESSION['logged_in'], true) : 'NOT SET' ?></p>
<p><strong>username in session:</strong> <?= htmlspecialchars($_SESSION['username'] ?? 'NOT SET') ?></p>
<p><strong>HTTPS:</strong> <?= isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : 'not set' ?></p>
<p><strong>session.save_path:</strong> <?= htmlspecialchars(session_save_path() ?: '(default)') ?></p>
<p><strong>save_path writable:</strong> <?= is_writable(session_save_path() ?: sys_get_temp_dir()) ? 'YES' : 'NO \u26a0\ufe0f THIS IS LIKELY YOUR PROBLEM' ?></p>
<p><strong>Cookies received by PHP:</strong></p>
<pre><?php print_r($_COOKIE); ?></pre>

<hr>
<p><a href="login.php">Go to login</a> then come back to this page to see if logged_in persists.</p>
</body>
</html>
