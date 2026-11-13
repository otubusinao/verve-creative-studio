<?php
require_once __DIR__ . '/../includes/auth.php';

if (admin_is_logged_in()) {
    header('Location: ' . SITE_URL . '/admin/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_verify_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Your session has expired. Please refresh and try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        try {
            if (admin_login_attempt(get_db(), $email, $password)) {
                header('Location: ' . SITE_URL . '/admin/');
                exit;
            }

            $error = 'Invalid email or password.';
        } catch (Throwable $e) {
            error_log('Admin login error: ' . $e->getMessage());
            $error = 'Unable to sign in at this time. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | Verve Creative Studio</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;background:#0b0a09;color:#f2ede4;font-family:Jost,sans-serif;display:grid;place-items:center;padding:22px}
.login{width:100%;max-width:430px;background:#141210;border:1px solid #2a2620;padding:42px;border-radius:4px}
.brand{text-align:center;margin-bottom:34px}
.brand h1{font:600 38px 'Cormorant Garamond',serif;letter-spacing:4px;margin:0;color:#c9a24b}
.brand p{font-size:10px;letter-spacing:4px;color:#a8a29a;margin:2px 0}
h2{font:500 28px 'Cormorant Garamond',serif;text-align:center;margin:0 0 8px}
.intro{text-align:center;color:#a8a29a;font-size:13px;margin:0 0 28px}
label{display:block;font-size:12px;color:#e8c873;margin:18px 0 7px}
input{width:100%;padding:14px;background:#0b0a09;border:1px solid #393229;color:#f2ede4;font:14px Jost,sans-serif;border-radius:2px}
input:focus{outline:1px solid #c9a24b;border-color:#c9a24b}
button{width:100%;padding:14px;margin-top:25px;border:0;background:#c9a24b;color:#0b0a09;font:600 12px Jost,sans-serif;letter-spacing:2px;text-transform:uppercase;cursor:pointer}
button:hover{background:#e8c873}
.error{background:#351b19;border:1px solid #79423a;color:#ffd1c9;padding:12px;font-size:13px;margin-bottom:18px}
.back{text-align:center;margin-top:25px;font-size:12px}
a{color:#c9a24b;text-decoration:none}
</style>
</head>
<body>
<main class="login">
  <div class="brand"><h1>VERVE</h1><p>CREATIVE STUDIO</p></div>
  <h2>Admin Sign In</h2>
  <p class="intro">Sign in to manage your creative studio.</p>

  <?php if ($error): ?>
    <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <label for="email">Email address</label>
    <input id="email" type="email" name="email" required autocomplete="username">
    <label for="password">Password</label>
    <input id="password" type="password" name="password" required autocomplete="current-password">
    <button type="submit">Sign In</button>
  </form>
  <p class="back"><a href="<?= SITE_URL ?>/">← Return to website</a></p>
</main>
</body>
</html>
