<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/booking-session.php';

$name = trim($_POST['customer_name'] ?? '');
$email = trim($_POST['customer_email'] ?? '');

// Name + valid email are required — without them we can't send a confirmation/receipt at all.
if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . SITE_URL . '/book/flow-a/steps/about-event');
    exit;
}

flow_a_set('customer_name', $name);
flow_a_set('customer_email', $email);
flow_a_set('customer_phone', trim($_POST['customer_phone'] ?? ''));
flow_a_set('guest_count', trim($_POST['guest_count'] ?? ''));
flow_a_set('event_schedule', trim($_POST['event_schedule'] ?? ''));
flow_a_set('special_requests', trim($_POST['special_requests'] ?? ''));

header('Location: ' . SITE_URL . '/book/flow-a/steps/upload');
exit;
