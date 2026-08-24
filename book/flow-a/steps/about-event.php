<?php
/**
 * Page: Flow A Step 7 — About Your Event
 * Status: BUILT by Claude per client authorization. Fields per Section 8: "free text with
 * prompts (schedule, special requests, guest size, etc.)"
 */
$page_title = 'Wedding Film & Photography — About Your Event | Verve Creative Studio';
$noindex = true;
require_once __DIR__ . '/../../../includes/booking-session.php';
require __DIR__ . '/../../../includes/header.php';

$service_title = 'Wedding Film & Photography';
$service_tagline = 'Book Your Date, Capture Your Forever';
$service_intro = 'Tell us about your special day and let us create timeless memories you will cherish forever.';
$current_step = 7;
require __DIR__ . '/../../../includes/booking/flow-a-header.php';

$fa = flow_a_state();
?>

<div class="wrap wizard-body">
  <div class="wizard-main">
    <p class="wizard-step-eyebrow">Step 7 of 10</p>
    <h2>Tell Us About Your Event</h2>
    <p class="wizard-step-sub">A few details help us prepare the right team and equipment for your day.</p>

    <form method="post" action="<?php echo SITE_URL; ?>/book/flow-a/steps/save-about-event">
      <p class="eyebrow" style="margin-bottom:16px;">Your Contact Details</p>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" name="customer_name" class="form-input" required placeholder="Your name" value="<?php echo htmlspecialchars($fa['customer_name'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input type="email" name="customer_email" class="form-input" required placeholder="you@example.com" value="<?php echo htmlspecialchars($fa['customer_email'] ?? ''); ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="customer_phone" class="form-input" style="max-width:280px;" placeholder="Optional" value="<?php echo htmlspecialchars($fa['customer_phone'] ?? ''); ?>">
      </div>
      <p class="form-hint" style="margin-bottom:26px;">We'll send your booking confirmation and receipt to this email.</p>

      <div class="form-group">
        <label class="form-label">Estimated Guest Count</label>
        <input type="number" min="1" name="guest_count" class="form-input" style="max-width:200px;" placeholder="e.g. 120" value="<?php echo htmlspecialchars($fa['guest_count'] ?? ''); ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Event Schedule</label>
        <textarea name="event_schedule" class="form-textarea" placeholder="e.g. 10am bridal prep, 12pm ceremony, 1pm photos, 3pm reception..."><?php echo htmlspecialchars($fa['event_schedule'] ?? ''); ?></textarea>
        <p class="form-hint">Rough timings are fine — we'll confirm the details closer to your date.</p>
      </div>

      <div class="form-group">
        <label class="form-label">Special Requests</label>
        <textarea name="special_requests" class="form-textarea" placeholder="Anything else we should know — specific shots, family arrangements, cultural traditions, accessibility needs..."><?php echo htmlspecialchars($fa['special_requests'] ?? ''); ?></textarea>
      </div>

      <div class="wizard-nav-buttons">
        <a href="<?php echo SITE_URL; ?>/book/flow-a/steps/addons" class="btn btn-outline">&larr; Back</a>
        <button type="submit" class="btn btn-solid">Continue &rarr;</button>
      </div>
    </form>
  </div>

  <?php require __DIR__ . '/../../../includes/booking/flow-a-sidebar.php'; ?>
</div>

<?php require __DIR__ . '/../../../includes/booking/flow-a-footer-trust.php'; ?>
<?php require __DIR__ . '/../../../includes/footer.php'; ?>
