<?php
/**
 * Verve Creative Studio — Admin authentication
 */
require_once __DIR__ . '/db.php';

function admin_start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

admin_start_session();

function admin_csrf_token(): string {
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf_token'];
}

function admin_verify_csrf(?string $token): bool {
    return is_string($token)
        && isset($_SESSION['admin_csrf_token'])
        && hash_equals($_SESSION['admin_csrf_token'], $token);
}

function admin_is_logged_in(): bool {
    return isset($_SESSION['admin_user']['id']);
}

function admin_current_user(): ?array {
    return admin_is_logged_in() ? $_SESSION['admin_user'] : null;
}

function admin_login_attempt(PDO $pdo, string $email, string $password): bool {
    $stmt = $pdo->prepare(
        "SELECT id, name, email, password_hash, role
         FROM admin_users
         WHERE email = :email AND status = 'active'
         LIMIT 1"
    );

    $stmt->execute(['email' => trim(strtolower($email))]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return false;
    }

    session_regenerate_id(true);

    $_SESSION['admin_user'] = [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'role' => $user['role']
    ];

    unset($_SESSION['admin_csrf_token']);

    $update = $pdo->prepare(
        'UPDATE admin_users SET last_login_at = NOW() WHERE id = :id'
    );
    $update->execute(['id' => $user['id']]);

    return true;
}

function admin_require_login(): void {
    if (!admin_is_logged_in()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function admin_logout(): void {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Strict'
        ]);
    }

    session_destroy();
}
