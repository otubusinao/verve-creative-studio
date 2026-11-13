
<?php

require_once __DIR__ . '/db.php';

/**
 * Start a secure administrator session.
 */
function admin_start_session(): void
{
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
        'samesite' => 'Strict',
    ]);

    session_start();
}

admin_start_session();

/**
 * Generate a CSRF token.
 */
function admin_csrf_token(): string
{
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['admin_csrf_token'];
}

/**
 * Verify a submitted CSRF token.
 */
function admin_verify_csrf(?string $token): bool
{
    return is_string($token)
        && isset($_SESSION['admin_csrf_token'])
        && hash_equals($_SESSION['admin_csrf_token'], $token);
}

/**
 * Check whether an administrator is authenticated.
 */
function admin_is_logged_in(): bool
{
    return isset($_SESSION['admin_user']['id']);
}

/**
 * Return the current administrator.
 */
function admin_current_user(): ?array
{
    return admin_is_logged_in()
        ? $_SESSION['admin_user']
        : null;
}

/**
 * Authenticate an administrator using the existing database schema.
 */
function admin_login_attempt(
    PDO $pdo,
    string $email,
    string $password
): bool {

    $sql = "
        SELECT
            u.id,
            u.full_name,
            u.email,
            u.password_hash,
            u.role_id,
            u.status,
            r.role_key,
            r.role_name
        FROM admin_users u
        INNER JOIN admin_roles r ON r.id = u.role_id
        WHERE u.email = :email
        LIMIT 1
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'email' => trim(strtolower($email)),
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        !$user ||
        $user['status'] !== 'active' ||
        !password_verify($password, $user['password_hash'])
    ) {
        return false;
    }

    session_regenerate_id(true);

    $_SESSION['admin_user'] = [
        'id' => (int) $user['id'],
        'name' => $user['full_name'],
        'email' => $user['email'],
        'role_id' => (int) $user['role_id'],
        'role' => $user['role_key'],
        'role_name' => $user['role_name'],
    ];

    unset($_SESSION['admin_csrf_token']);

    $update = $pdo->prepare(
        'UPDATE admin_users SET last_login_at = NOW() WHERE id = :id'
    );

    $update->execute([
        'id' => $user['id'],
    ]);

    return true;
}

/**
 * Require administrator authentication.
 */
function admin_require_login(): void
{
    if (!admin_is_logged_in()) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Check administrator role.
 */
function admin_has_role(array $allowed_roles): bool
{
    $user = admin_current_user();

    return $user !== null
        && in_array($user['role'], $allowed_roles, true);
}

/**
 * Restrict access to authorised roles.
 */
function admin_require_role(array $allowed_roles): void
{
    admin_require_login();

    if (!admin_has_role($allowed_roles)) {
        http_response_code(403);
        exit('You do not have permission to access this section.');
    }
}

/**
 * Log out the administrator.
 */
function admin_logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();

        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => 'Strict',
        ]);
    }

    session_destroy();
}
