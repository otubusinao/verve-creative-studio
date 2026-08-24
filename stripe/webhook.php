<?php
/**
 * Stripe webhook endpoint. Deliberately public (see .htaccess — this folder is NOT protected
 * by the includes/ deny-all rule) since Stripe's servers need to reach it directly.
 *
 * SECURITY: every request here MUST have its signature verified against STRIPE_WEBHOOK_SECRET
 * before anything in the payload is trusted — otherwise anyone could POST a fake
 * "payment succeeded" event and trigger false confirmation emails. Never skip this check.
 *
 * This is a separate server-to-server request from Stripe — it has NO access to the customer's
 * browser session, which is why process-payment.php attaches everything needed (name, email,
 * booking details) as Stripe Checkout Session metadata when the session is created.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/stripe-client.php';
require_once __DIR__ . '/../includes/mailer.php';

http_response_code(200); // acknowledge receipt by default; Stripe retries on non-2xx

if (!stripe_is_configured()) {
    exit; // nothing to verify against yet — SDK/keys not installed
}

require_once __DIR__ . '/../vendor/autoload.php';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, STRIPE_WEBHOOK_SECRET);
} catch (\Exception $e) {
    // Invalid payload or bad/missing signature — reject without processing anything further.
    error_log('Stripe webhook signature verification failed: ' . $e->getMessage());
    http_response_code(400);
    exit;
}

if ($event->type !== 'checkout.session.completed') {
    exit; // acknowledge and ignore any event type we don't act on
}

$session = $event->data->object;
$meta = $session->metadata ?? new stdClass();
$flow = $meta->flow ?? 'unknown';

$booking_ref = $meta->booking_ref ?? ('VC-' . strtoupper(substr($session->id, -8)));
$customer_name = $meta->customer_name ?? 'Customer';
$customer_email = $meta->customer_email ?? '';
$amount_paid = $meta->amount_paid ?? number_format(($session->amount_total ?? 0) / 100, 2);

// --- Admin notification (always attempted, regardless of flow) ---
$admin_subject = 'New Payment Received — ' . $booking_ref;
$admin_body = email_template('New Payment Received', '
    <p><strong>Booking reference:</strong> ' . htmlspecialchars($booking_ref) . '</p>
    <p><strong>Flow:</strong> ' . htmlspecialchars($flow) . '</p>
    <p><strong>Customer:</strong> ' . htmlspecialchars($customer_name) . ' (' . htmlspecialchars($customer_email) . ')</p>
    <p><strong>Phone:</strong> ' . htmlspecialchars($meta->customer_phone ?? '-') . '</p>
    <p><strong>Coverage:</strong> ' . htmlspecialchars($meta->coverage_label ?? $meta->package_name ?? '-') . '</p>
    <p><strong>Date/Time:</strong> ' . htmlspecialchars(($meta->date ?? '-') . ' ' . ($meta->time ?? '')) . '</p>
    <p><strong>Amount Paid:</strong> &pound;' . htmlspecialchars($amount_paid) . '</p>
');
send_email(SMTP_USER, 'Verve Creative Studio', $admin_subject, $admin_body, '', $customer_email, $customer_name);

// --- Customer confirmation (only if we actually have their email) ---
if ($customer_email !== '' && filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
    $customer_subject = 'Booking Confirmed — ' . $booking_ref;
    $customer_body = email_template('Thank You, ' . $customer_name . '!', '
        <p>Your booking with Verve Creative Studio is confirmed. We can\'t wait to be part of your celebration.</p>
        <p><strong>Booking reference:</strong> ' . htmlspecialchars($booking_ref) . '</p>
        <p><strong>Coverage:</strong> ' . htmlspecialchars($meta->coverage_label ?? $meta->package_name ?? '-') . '</p>
        <p><strong>Date/Time:</strong> ' . htmlspecialchars(($meta->date ?? '-') . ' ' . ($meta->time ?? '')) . '</p>
        <p><strong>Amount Paid:</strong> &pound;' . htmlspecialchars($amount_paid) . '</p>
        <p>We\'ll be in touch with any further details ahead of your date.</p>
    ');
    send_email($customer_email, $customer_name, $customer_subject, $customer_body);
}
