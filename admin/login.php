
<?php
require_once __DIR__ . '/../includes/auth.php';

if (admin_is_logged_in()) {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!admin_verify_csrf($csrfToken)) {
        $error = 'Your session has expired. Please refresh and try again.';
    } elseif ($email === '' || $password === '') {
        $error = 'Please enter your email address and password.';
    } else {
        try {
            if (admin_login_attempt(get_db(), $email, $password)) {
                header('Location: ' . SITE_URL . '/admin/index.php');
                exit;
            }

            $error = 'The email address or password is incorrect.';
        } catch (Throwable $e) {
            error_log('Admin login error: ' . $e->getMessage());
            $error = 'Unable to sign in at the moment. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Sign In | Verve Creative Studio</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600&family=Jost:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0B0A09;
            --panel: #141210;
            --line: #2A2620;
            --gold: #C9A24B;
            --gold-light: #E8C873;
            --cream: #F2EDE4;
            --muted: #A8A29A;
            --danger: #E58A82;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 18px;
            background:
                radial-gradient(ellipse at 50% 0%, rgba(201,162,75,.09), transparent 48%),
                var(--bg);
            color: var(--cream);
            font-family: 'Jost', sans-serif;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
        }

        .brand {
            text-align: center;
            margin-bottom: 36px;
        }

        .brand-name {
            margin: 0;
            color: var(--gold-light);
            font-family: 'Cormorant Garamond', serif;
            font-size: 36px;
            font-weight: 500;
            letter-spacing: 5px;
        }

        .brand-subtitle {
            margin-top: 5px;
            color: var(--muted);
            font-size: 10px;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .login-card {
            padding: 38px;
            background: rgba(20,18,16,.96);
            border: 1px solid var(--line);
            border-radius: 5px;
            box-shadow: 0 24px 70px rgba(0,0,0,.35);
        }

        .eyebrow {
            margin: 0 0 9px;
            color: var(--gold);
            font-size: 10px;
            font-weight: 500;
            letter-spacing: 2.5px;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-family: 'Cormorant Garamond', serif;
            font-size: 34px;
            font-weight: 500;
        }

        .intro {
            margin: 8px 0 28px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.7;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 9px;
            color: var(--cream);
            font-size: 12px;
            letter-spacing: .4px;
        }

        input {
            width: 100%;
            height: 49px;
            padding: 0 14px;
            border: 1px solid var(--line);
            border-radius: 3px;
            outline: none;
            background: #0D0C0B;
            color: var(--cream);
            font-family: inherit;
            font-size: 14px;
            transition: border-color .2s ease;
        }

        input:focus {
            border-color: var(--gold);
        }

        input::placeholder {
            color: #6F6A62;
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap input {
            padding-right: 75px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 13px;
            transform: translateY(-50%);
            padding: 5px;
            border: 0;
            background: transparent;
            color: var(--gold);
            cursor: pointer;
            font-family: inherit;
            font-size: 11px;
        }

        .error-message {
            margin-bottom: 20px;
            padding: 12px 14px;
            border: 1px solid rgba(229,138,130,.3);
            border-radius: 3px;
            background: rgba(229,138,130,.08);
            color: var(--danger);
            font-size: 12px;
            line-height: 1.6;
        }

        .submit-button {
            width: 100%;
            min-height: 49px;
            margin-top: 5px;
            border: 1px solid var(--gold);
            border-radius: 3px;
            background: var(--gold);
            color: #17130B;
            cursor: pointer;
            font-family: inherit;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: background .2s ease, border-color .2s ease;
        }

        .submit-button:hover {
            border-color: var(--gold-light);
            background: var(--gold-light);
        }

        .footer {
            margin-top: 26px;
            color: #6F6A62;
            font-size: 11px;
            text-align: center;
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 30px 23px;
            }

            .brand-name {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>

    <main class="login-wrapper">
        <header class="brand">
            <p class="brand-name">VERVE</p>
            <div class="brand-subtitle">Creative Studio</div>
        </header>

        <section class="login-card">
            <p class="eyebrow">Administration</p>
            <h1>Welcome back</h1>
            <p class="intro">
                Sign in to access your studio management dashboard.
            </p>

            <?php if ($error !== ''): ?>
                <div class="error-message" role="alert">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars(admin_csrf_token(), ENT_QUOTES, 'UTF-8') ?>"
                >

                <div class="form-group">
                    <label for="email">Email address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email address"
                        value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        autocomplete="username"
                        required
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                        <button
                            type="button"
                            class="toggle-password"
                            id="togglePassword"
                            aria-label="Show password"
                        >Show</button>
                    </div>
                </div>

                <button type="submit" class="submit-button">
                    Sign In
                </button>
            </form>
        </section>

        <footer class="footer">
            &copy; <?= date('Y') ?> Verve Creative Studio. All rights reserved.
        </footer>
    </main>

    <script>
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');

        toggleButton.addEventListener('click', function () {
            const showing = passwordInput.type === 'text';

            passwordInput.type = showing ? 'password' : 'text';
            toggleButton.textContent = showing ? 'Show' : 'Hide';
            toggleButton.setAttribute(
                'aria-label',
                showing ? 'Show password' : 'Hide password'
            );
        });
    </script>

</body>
</html>
