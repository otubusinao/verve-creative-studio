<?php
/**
 * Stripe SDK wrapper.
 *
 * REQUIRES: `composer require stripe/stripe-php` run in the project root (SSH is confirmed
 * available on IONOS Web Hosting Plus — see Section 2A) and real keys filled in
 * includes/config.php (STRIPE_SECRET_KEY, STRIPE_PUBLISHABLE_KEY, STRIPE_WEBHOOK_SECRET).
 *
 * Until both of those are true, stripe_is_configured() returns false and the payment step
 * shows an honest "not yet connected" message instead of pretending to charge a card.
 */
require_once __DIR__ . '/config.php';

function stripe_is_configured(): bool {
    $vendor_autoload = __DIR__ . '/../vendor/autoload.php';
    return file_exists($vendor_autoload)
        && STRIPE_SECRET_KEY !== 'TODO'
        && STRIPE_SECRET_KEY !== '';
}

/**
 * Creates a Stripe Checkout Session for the given amount (in the smallest currency unit —
 * pence for GBP) and returns its redirect URL, or null if Stripe isn't configured yet.
 *
 * $metadata is attached to the Checkout Session and comes back on the webhook event — this is
 * how stripe/webhook.php (a separate server-to-server request with no access to the customer's
 * browser session) knows who the booking was for and what to put in the confirmation emails.
 */
function stripe_create_checkout_session(string $description, int $amount_pence, string $success_url, string $cancel_url, array $metadata = []): ?string {
    if (!stripe_is_configured()) {
        return null;
    }

    require_once __DIR__ . '/../vendor/autoload.php';
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'gbp',
                'product_data' => ['name' => $description],
                'unit_amount' => $amount_pence,
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',
        'success_url' => $success_url,
        'cancel_url' => $cancel_url,
        'metadata' => $metadata,
        'customer_email' => $metadata['customer_email'] ?? null,
    ]);

    return $session->url;
}
