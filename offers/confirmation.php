<?php
/**
 * Page: Offer Purchase Confirmation (Birthday Coverage, and future offers)
 * ?status=success → real Stripe payment completed
 * ?status=demo    → Stripe isn't configured yet; no real charge occurred
 */
require_once __DIR__ . '/../includes/booking-session.php';
$page_title = 'Booking Confirmed | Verve Creative Studio';
$noindex = true;
require __DIR__ . '/../includes/header.php';

$offer = offer_state();
$status = $_GET['status'] ?? 'demo';
$booking_ref = $offer['booking_ref'] ?? ('VC-BD-' . strtoupper(substr(session_id(), 0, 8)));
?>

<section style="padding:80px 0;">
  <div class="wrap" style="max-width:640px;">
    <?php if ($status === 'demo'): ?>
    <div class="wizard-pending-notice" style="margin-bottom:32px;">
      <p><strong>Demo mode:</strong> Stripe isn't connected yet, so no real payment was processed.</p>
    </div>
    <?php endif; ?>

    <p class="eyebrow">Booking Confirmed</p>
    <h1 style="font-size:36px;color:var(--cream);margin-bottom:16px;">Thank You<?php echo !empty($offer['customer_name']) ? ', ' . htmlspecialchars($offer['customer_name']) : ''; ?>!</h1>
    <p style="color:var(--muted);font-size:15px;margin-bottom:32px;">Your Birthday Coverage booking is confirmed. Let's make your celebration unforgettable!</p>

    <div class="wizard-summary-card" style="margin-bottom:28px;">
      <div class="wizard-summary-rows" style="border-top:none;padding-top:0;">
        <div><span>Booking Reference</span><span><?php echo htmlspecialchars($booking_ref); ?></span></div>
        <div><span>Package</span><span><?php echo htmlspecialchars($offer['tier_name'] ?? '-'); ?></span></div>
        <div><span>Amount Paid</span><span>&pound;<?php echo number_format($offer['amount'] ?? 0, 2); ?></span></div>
      </div>
    </div>

    <?php if ($status === 'success' && !empty($offer['customer_email'])): ?>
    <p style="color:var(--muted);font-size:13.5px;margin-bottom:28px;">A confirmation email has been sent to <strong><?php echo htmlspecialchars($offer['customer_email']); ?></strong>. We'll be in touch to arrange the details.</p>
    <?php endif; ?>

    <a href="<?php echo SITE_URL; ?>/" class="btn btn-solid">Return Home</a>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
