<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . '/admin/');
    exit;
}

if (!admin_verify_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid security token.');
}

admin_logout();

header('Location: ' . SITE_URL . '/admin/login.php');
exit;
