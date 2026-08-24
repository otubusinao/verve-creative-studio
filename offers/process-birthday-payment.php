<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/booking-session.php';
require_once __DIR__ . '/../includes/stripe-client.php';

$tiers = birthday_coverage_tiers();
$tier_key = $_POST['tier'] ?? '';
$name = trim($_POST['customer_name'] ?? '');
$email = trim($_POST['customer_email'] ?? '');
$phone = trim($_POST['customer_phone'] ?? '');

// Validate everything server-side — never trust a submitted price, and never proceed to
// Stripe without a valid tier + real contact details.
if (!isset($tiers[$tier_key]) || $name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . SITE_URL . '/offers/birthday-checkout');
    exit;
}

$tier = $tiers[$tier_key];
$booking_ref = 'VC-BD-' . strtoupper(substr(session_id(), 0, 8));

offer_set('slug', 'birthday-coverage');
offer_set('tier_key', $tier_key);
offer_set('tier_name', $tier['name']);
offer_set('amount', $tier['price']);
offer_set('customer_name', $name);
offer_set('customer_email', $email);
offer_set('customer_phone', $phone);
offer_set('booking_ref', $booking_ref);

if (stripe_is_configured()) {
    $success_url = SITE_DOMAIN . SITE_URL . '/offers/confirmation?status=success';
    $cancel_url  = SITE_DOMAIN . SITE_URL . '/offers/birthday-checkout';
    $checkout_url = stripe_create_checkout_session(
        'Birthday Coverage — ' . $tier['name'] . ' Package',
        $tier['price'] * 100, // pence
        $success_url,
        $cancel_url,
        [
            'flow' => 'offer_birthday_coverage',
            'booking_ref' => $booking_ref,
            'customer_name' => $name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'package_name' => 'Birthday Coverage — ' . $tier['name'],
            'amount_paid' => number_format($tier['price'], 2),
        ]
    );
    if ($checkout_url) {
        header('Location: ' . $checkout_url);
        exit;
    }
}

header('Location: ' . SITE_URL . '/offers/confirmation?status=demo');
exit;
