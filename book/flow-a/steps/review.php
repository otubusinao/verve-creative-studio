<?php
/**
 * Page: Flow A Step 9 — Review Booking
 * Status: BUILT by Claude per client authorization. Deposit is set at 30% of the estimated
 * total as a reasonable placeholder — confirm the actual deposit policy and adjust the
 * DEPOSIT_PERCENTAGE constant below if different. Terms & Conditions page doesn't exist yet
 * (no content received for it) — the checkbox links to a not-yet-built /terms page; that's a
 * genuine content gap, not something to fabricate.
 */
$page_title = 'Wedding Film & Photography — Review Your Booking | Verve Creative Studio';
$noindex = true;
require_once __DIR__ . '/../../../includes/booking-session.php';
require __DIR__ . '/../../../includes/header.php';

$service_title = 'Wedding Film & Photography';
$service_tagline = 'Book Your Date, Capture Your Forever';
$service_intro = 'Tell us about your special day and let us create timeless memories you will cherish forever.';
$current_step = 9;
require __DIR__ . '/../../../includes/booking/flow-a-header.php';

$fa = flow_a_state();
const DEPOSIT_PERCENTAGE = 0.30;
$deposit = $fa['estimated_total'] * DEPOSIT_PERCENTAGE;
?>

<div class="wrap wizard-body">
  <div class="wizard-main">
    <p class="wizard-step-eyebrow">Step 9 of 10</p>
    <h2>Review Your Booking</h2>
    <p class="wizard-step-sub">Please check everything below before proceeding to payment.</p>

    <form method="post" action="<?php echo SITE_URL; ?>/book/flow-a/steps/save-review">
      <div class="review-section">
        <h4>Contact Details</h4>
        <div class="review-row"><span>Name</span><span><?php echo htmlspecialchars($fa['customer_name'] ?? 'Not provided'); ?></span></div>
        <div class="review-row"><span>Email</span><span><?php echo htmlspecialchars($fa['customer_email'] ?? 'Not provided'); ?></span></div>
        <div class="review-row"><span>Phone</span><span><?php echo htmlspecialchars($fa['customer_phone'] ?: '-'); ?></span></div>
      </div>

      <div class="review-section">
        <h4>Coverage</h4>
        <div class="review-row"><span>Type</span><span><?php echo htmlspecialchars($fa['coverage_label'] ?? 'Not selected'); ?></span></div>
        <div class="review-row"><span>Duration</span><span><?php echo htmlspecialchars($fa['duration_label'] ?? 'Not selected'); ?></span></div>
      </div>

      <div class="review-section">
        <h4>Date &amp; Time</h4>
        <div class="review-row"><span>Date</span><span><?php echo htmlspecialchars($fa['date'] ?? 'Not selected'); ?></span></div>
        <div class="review-row"><span>Time</span><span><?php echo htmlspecialchars($fa['time'] ?? 'Not selected'); ?></span></div>
      </div>

      <div class="review-section">
        <h4>Venue</h4>
        <div class="review-row"><span>Name</span><span><?php echo htmlspecialchars($fa['venue_name'] ?: '-'); ?></span></div>
        <div class="review-row"><span>Address</span><span><?php echo htmlspecialchars(trim(($fa['venue_address'] ?? '') . ', ' . ($fa['venue_city'] ?? ''), ', ') ?: '-'); ?></span></div>
        <div class="review-row"><span>Setting</span><span><?php echo htmlspecialchars(ucfirst($fa['venue_setting'] ?? '-')); ?></span></div>
      </div>

      <div class="review-section">
        <h4>Add-ons</h4>
        <?php if (empty($fa['addons'])): ?>
        <div class="review-row"><span>None selected</span><span></span></div>
        <?php else: foreach ($fa['addons'] as $a): ?>
        <div class="review-row"><span><?php echo htmlspecialchars($a['label']); ?></span><span>&pound;<?php echo number_format($a['price']); ?></span></div>
        <?php endforeach; endif; ?>
      </div>

      <div class="review-section">
        <h4>Your Event</h4>
        <div class="review-row"><span>Guest Count</span><span><?php echo htmlspecialchars($fa['guest_count'] ?: '-'); ?></span></div>
        <div class="review-row"><span>Files Uploaded</span><span><?php echo count($fa['uploads']); ?></span></div>
      </div>

      <div class="review-section" style="border-bottom:none;">
        <h4>Estimated Cost</h4>
        <div class="review-row"><span>Total</span><span>&pound;<?php echo number_format($fa['estimated_total'], 2); ?></span></div>
        <div class="review-row"><span>Deposit Due Today (30%)</span><span>&pound;<?php echo number_format($deposit, 2); ?></span></div>
      </div>

      <label class="terms-check">
        <input type="checkbox" name="terms_accepted" required>
        <span>I agree to the <a href="<?php echo SITE_URL; ?>/terms" target="_blank">Terms &amp; Conditions</a> (page pending — content not yet received from client).</span>
      </label>

      <div class="wizard-nav-buttons">
        <a href="<?php echo SITE_URL; ?>/book/flow-a/steps/upload" class="btn btn-outline">&larr; Back</a>
        <button type="submit" class="btn btn-solid">Continue to Payment &rarr;</button>
      </div>
    </form>
  </div>

  <?php require __DIR__ . '/../../../includes/booking/flow-a-sidebar.php'; ?>
</div>

<?php require __DIR__ . '/../../../includes/booking/flow-a-footer-trust.php'; ?>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
