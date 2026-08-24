<?php
/**
 * Page: Birthday Coverage Checkout
 * Status: BUILT by Claude per client instruction ("connect the birthday offer to payment").
 * Deliberately a simple single-page checkout rather than routing through the 10-step
 * Wedding/Photography wizard — most of that wizard's fields (venue setting, wedding-specific
 * add-ons) don't apply to a flat-price birthday package. Flagged as Claude's own call.
 */
require_once __DIR__ . '/../includes/booking-session.php';
$page_title = 'Book Birthday Coverage | Verve Creative Studio';
$noindex = true;
require __DIR__ . '/../includes/header.php';

$offer = offer_state();
$tiers = birthday_coverage_tiers();
$requested_tier = $_GET['tier'] ?? null;
$selected = ($requested_tier && isset($tiers[$requested_tier])) ? $requested_tier : ($offer['tier_key'] ?? 'signature');
?>

<div class="wizard-breadcrumb">
  <div class="wrap"><a href="<?php echo SITE_URL; ?>/">Home</a> / <a href="<?php echo SITE_URL; ?>/offers/">Offers</a> / <span>Birthday Coverage</span></div>
</div>

<section style="padding:60px 0;">
  <div class="wrap" style="max-width:800px;">
    <p class="eyebrow">Special Birthday Coverage</p>
    <h1 style="font-size:34px;color:var(--cream);margin-bottom:8px;">Book Your Package</h1>
    <p style="color:var(--muted);font-size:15px;margin-bottom:34px;">Choose your tier and enter your details to secure your date.</p>

    <form method="post" action="<?php echo SITE_URL; ?>/offers/process-birthday-payment">
      <div class="pricing-tiers" style="margin-bottom:34px;">
        <?php foreach ($tiers as $key => $tier): $checked = $selected === $key; ?>
        <label class="pricing-tier pricing-tier-select <?php echo $checked ? 'selected' : ''; ?> <?php echo $key === 'signature' ? 'featured' : ''; ?>">
          <?php if ($key === 'signature'): ?><span class="pricing-tier-badge">Most Popular</span><?php endif; ?>
          <input type="radio" name="tier" value="<?php echo $key; ?>" <?php echo $checked ? 'checked' : ''; ?>>
          <span class="select-check">&check;</span>
          <h4><?php echo htmlspecialchars($tier['name']); ?></h4>
          <div class="price-row" style="border-bottom:none;">
            <span class="price-now">&pound;<?php echo number_format($tier['price']); ?></span>
          </div>
        </label>
        <?php endforeach; ?>
      </div>

      <p class="eyebrow" style="margin-bottom:16px;">Your Contact Details</p>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" name="customer_name" class="form-input" required value="<?php echo htmlspecialchars($offer['customer_name'] ?? ''); ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input type="email" name="customer_email" class="form-input" required value="<?php echo htmlspecialchars($offer['customer_email'] ?? ''); ?>">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input type="tel" name="customer_phone" class="form-input" style="max-width:280px;" placeholder="Optional" value="<?php echo htmlspecialchars($offer['customer_phone'] ?? ''); ?>">
      </div>

      <div class="wizard-nav-buttons" style="margin-top:30px;">
        <a href="<?php echo SITE_URL; ?>/offers/" class="btn btn-outline">&larr; Back</a>
        <button type="submit" class="btn btn-solid">Proceed to Payment &rarr;</button>
      </div>
    </form>
  </div>
</section>

<script>
  document.querySelectorAll('.pricing-tier-select').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.pricing-tier-select').forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');
      card.querySelector('input').checked = true;
    });
  });
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>
