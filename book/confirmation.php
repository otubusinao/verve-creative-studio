<?php
/**
 * Page: Booking Confirmation
 * Status: BUILT out of necessity (process-payment.php redirects here) — not explicitly part of
 * the "Steps 2-10" authorization, built following the same established pattern so the flow
 * doesn't dead-end after payment. Please review like the other unrequested steps.
 *
 * ?status=success  → real Stripe payment completed (once Stripe is actually configured)
 * ?status=demo     → Stripe isn't configured yet; this is a demo confirmation, no real charge occurred
 */
$page_title = 'Booking Confirmed | Verve Creative Studio';
$noindex = true;
require_once __DIR__ . '/../includes/booking-session.php';
require __DIR__ . '/../includes/header.php';

$fa = flow_a_state();
$status = $_GET['status'] ?? 'demo';
$booking_ref = $fa['booking_ref'] ?? ('VC-' . strtoupper(substr(session_id(), 0, 8)));
?>

<section style="padding:80px 0;">
  <div class="wrap" style="max-width:640px;">
    <?php if ($status === 'demo'): ?>
    <div class="wizard-pending-notice" style="margin-bottom:32px;">
      <p><strong>Demo mode:</strong> Stripe isn't connected yet, so no real payment was processed. This page shows what a couple would see after a successful payment, once Stripe is fully configured.</p>
    </div>
    <?php endif; ?>

    <p class="eyebrow">Booking Confirmed</p>
    <h1 style="font-size:36px;color:var(--cream);margin-bottom:16px;">Thank You<?php echo !empty($fa['customer_name']) ? ', ' . htmlspecialchars($fa['customer_name']) : ''; ?>!</h1>
    <p style="color:var(--muted);font-size:15px;margin-bottom:32px;">Your wedding film &amp; photography booking is confirmed. We can't wait to be part of your day.</p>

    <div class="wizard-summary-card" style="margin-bottom:28px;">
      <div class="wizard-summary-rows" style="border-top:none;padding-top:0;">
        <div><span>Booking Reference</span><span><?php echo htmlspecialchars($booking_ref); ?></span></div>
        <div><span>Coverage</span><span><?php echo htmlspecialchars($fa['coverage_label'] ?? '-'); ?></span></div>
        <div><span>Date</span><span><?php echo htmlspecialchars($fa['date'] ?? '-'); ?></span></div>
        <div><span>Time</span><span><?php echo htmlspecialchars($fa['time'] ?? '-'); ?></span></div>
        <div><span>Amount Paid</span><span>&pound;<?php echo number_format($fa['payment_amount'] ?? 0, 2); ?></span></div>
      </div>
    </div>

    <?php if ($status === 'success' && !empty($fa['customer_email'])): ?>
    <p style="color:var(--muted);font-size:13.5px;margin-bottom:28px;">A confirmation email and receipt have been sent to <strong><?php echo htmlspecialchars($fa['customer_email']); ?></strong>.</p>
    <?php else: ?>
    <p style="color:var(--muted);font-size:13.5px;margin-bottom:28px;">In demo mode, no confirmation email is sent — this only happens once Stripe processes a real payment.</p>
    <?php endif; ?>

    <a href="<?php echo SITE_URL; ?>/" class="btn btn-solid">Return Home</a>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
