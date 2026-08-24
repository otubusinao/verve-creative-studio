<?php
/**
 * Page: Offers Hub
 * Status: BUILT by Claude per client authorization ("design it yourself following our
 * established site style") — no design was supplied for this page.
 *
 * Birthday Coverage pricing confirmed from the client's flyer (clear copy, not the earlier
 * illegible screenshot) — Essential £168 (was £200), Signature £245 (was £350), Premium £336
 * (was £480), all 30% off, plus £70/hour for additional coverage.
 *
 * Structured as a genuine hub (an $offers array), not a single hardcoded page, so future
 * promotions can be added as additional entries without rebuilding the page.
 */
$page_title = 'Special Offers | Verve Creative Studio';
$meta_description = 'Limited-time offers on Verve Creative Studio\'s film, photography, and creative services.';
require __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/image-helpers.php';

$offers = [
    [
        'slug' => 'birthday-coverage',
        'badge' => '30% Off — Limited Time',
        'title' => 'Special Birthday Coverage',
        'desc' => 'Capture the moments, relive the memories — professional photography and videography for your celebration.',
        'image' => 'offers/birthday-coverage',
    ],
];

$birthday_tiers = [
    [
        'key' => 'essential', 'name' => 'Essential', 'featured' => false,
        'was' => 200, 'now' => 168,
        'features' => [
            'Up to 3 hours coverage',
            'Guest arrivals & key moments',
            'Up to 10-minute edited video',
            '1 customised song',
            '1 social media Reel',
        ],
    ],
    [
        'key' => 'signature', 'name' => 'Signature', 'featured' => true,
        'was' => 350, 'now' => 245,
        'features' => [
            'Up to 5 hours coverage',
            'Guest arrivals, programme highlights & candid moments',
            'Up to 20-minute edited video',
            '1 customised song',
            '2–3 social media Reels',
        ],
    ],
    [
        'key' => 'premium', 'name' => 'Premium', 'featured' => false,
        'was' => 480, 'now' => 336,
        'features' => [
            'Up to 8 hours coverage',
            'Full event coverage, guests, programme & behind-the-scenes',
            'Up to 30-minute edited video',
            '1 customised song',
            '3–5 social media Reels',
        ],
    ],
];
?>

<div class="wizard-breadcrumb">
  <div class="wrap"><a href="<?php echo SITE_URL; ?>/">Home</a> / <span>Offers</span></div>
</div>

<section class="hero">
  <div class="wrap hero-grid">
    <div>
      <p class="eyebrow" style="margin-bottom:22px;">Special Offers</p>
      <h1>Limited-Time Offers.<br><span class="gold-word">Timeless Results.</span></h1>
      <div class="gold-underline"></div>
      <p class="lead">From time to time, we open up special packages and seasonal pricing on select services. Explore what's currently available.</p>
    </div>
    <div class="hero-visual art-panel"><span>Offers &mdash; Placeholder</span></div>
  </div>
</section>

<section>
  <div class="wrap">
    <p class="eyebrow">Current Offers</p>
    <div class="offers-grid">
      <?php foreach ($offers as $o): ?>
      <div class="offer-card">
        <div class="art-panel">
          <span class="offer-card-badge"><?php echo htmlspecialchars($o['badge']); ?></span>
          <?php render_photo($o['image'], $o['title'], 'sm'); ?>
        </div>
        <div class="offer-card-body">
          <h3><?php echo htmlspecialchars($o['title']); ?></h3>
          <p><?php echo htmlspecialchars($o['desc']); ?></p>
          <a href="#<?php echo $o['slug']; ?>" class="explore-link">View Offer &rarr;</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section-alt" id="birthday-coverage">
  <div class="wrap">
    <p class="eyebrow">Special Birthday Coverage</p>
    <h2 style="font-size:32px;color:var(--cream);margin-bottom:20px;">Let's Make Your Celebration Unforgettable.</h2>

    <p style="color:var(--muted);font-size:15px;max-width:600px;margin-bottom:8px;">We capture the moments. You relive the memories. Professional videography and content creation that tells the story of your special day with style and emotion.</p>
    <p style="color:var(--gold);font-size:12px;letter-spacing:.08em;text-transform:uppercase;margin-bottom:30px;">30% Off Across All Packages — Limited Time Offer</p>

    <div class="pricing-tiers">
      <?php foreach ($birthday_tiers as $tier): ?>
      <div class="pricing-tier <?php echo $tier['featured'] ? 'featured' : ''; ?>">
        <?php if ($tier['featured']): ?><span class="pricing-tier-badge">Most Popular</span><?php endif; ?>
        <h4><?php echo htmlspecialchars($tier['name']); ?></h4>
        <div class="price-row">
          <span class="price-was">&pound;<?php echo number_format($tier['was']); ?></span>
          <span class="price-now">&pound;<?php echo number_format($tier['now']); ?></span>
          <span class="price-save">Save <?php echo round((1 - $tier['now'] / $tier['was']) * 100); ?>%</span>
        </div>
        <ul>
          <?php foreach ($tier['features'] as $f): ?>
          <li><span class="check">&check;</span><span><?php echo htmlspecialchars($f); ?></span></li>
          <?php endforeach; ?>
        </ul>
        <a href="<?php echo SITE_URL; ?>/offers/birthday-checkout?tier=<?php echo urlencode($tier['key']); ?>" class="btn <?php echo $tier['featured'] ? 'btn-solid' : 'btn-outline'; ?>" style="width:100%;justify-content:center;">Book This Package</a>
      </div>
      <?php endforeach; ?>
    </div>

    <p class="offer-addon-note">Additional coverage available at &pound;70/hour.</p>

    <div style="text-align:center;margin-top:36px;">
      <p style="font-family:'Cormorant Garamond',serif;font-style:italic;color:var(--gold-light);font-size:19px;margin-bottom:20px;">Let's make your celebration unforgettable!</p>
      <div class="cta-actions" style="justify-content:center;">
        <a href="<?php echo SITE_URL; ?>/offers/birthday-checkout" class="btn btn-solid">Book Your Package &rarr;</a>
        <a href="https://wa.me/447466483138" target="_blank" class="btn btn-outline">Questions? WhatsApp Us</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../includes/footer.php'; ?>
