<?php
/**
 * Lightweight session-based state for the Flow A (Online Payment) booking wizard.
 * Placeholder until real DB-backed bookings exist (see database/schema.sql) — good enough to
 * carry selections between steps while the wizard is being built one confirmed step at a time.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['flow_a'])) {
    $_SESSION['flow_a'] = [
        'coverage'          => null,
        'coverage_label'    => null,
        'coverage_price'    => 0,
        'duration'          => null,
        'duration_label'    => null,
        'duration_hours'    => null,
        'date'              => null,
        'time'              => null,
        'venue_name'        => null,
        'venue_address'     => null,
        'venue_city'        => null,
        'venue_county'      => null,
        'venue_country'     => null,
        'venue_postcode'    => null,
        'venue_setting'     => null, // 'indoor' | 'outdoor'
        'location'          => null, // short display string for the sidebar, derived from the above
        'addons'            => [],   // list of ['label' => ..., 'price' => ...]
        'customer_name'     => null,
        'customer_email'    => null,
        'customer_phone'    => null,
        'guest_count'       => null,
        'event_schedule'    => null,
        'special_requests'  => null,
        'uploads'           => [],   // list of ['category' => ..., 'filename' => ...]
        'pinterest_url'     => null,
        'terms_accepted'    => false,
        'estimated_total'   => 0,
    ];
}

function flow_a_state(): array {
    return $_SESSION['flow_a'];
}

function flow_a_set(string $key, $value): void {
    $_SESSION['flow_a'][$key] = $value;
}

/**
 * Recomputes the running total from coverage price + all selected add-on prices.
 * Call this after any change to coverage or addons.
 */
function flow_a_recalculate_total(): void {
    $total = (float) ($_SESSION['flow_a']['coverage_price'] ?? 0);
    foreach ($_SESSION['flow_a']['addons'] as $addon) {
        $total += (float) ($addon['price'] ?? 0);
    }
    $_SESSION['flow_a']['estimated_total'] = $total;
}

/* ============================================================
   Flow B — Creative Project Brief (Film Production, Music Video
   Production, Corporate Content, Artwork Commission, Creative
   Direction, Brand Content Creation). Custom-quote, no payment.
   ============================================================ */
if (!isset($_SESSION['flow_b'])) {
    $_SESSION['flow_b'] = [
        'service_slug'    => null,
        'project_title'   => null,
        'production_type' => null,
        'summary'         => null,
        'goal'            => null,
        'style_tone'      => null,
        'reference'       => null,
        'referral_source' => null,
        'project_types'   => [],
        'scope'           => [],
        'needed_by'       => null,
        'urgency'         => null,
        'budget_range'    => null,
        'location_text'   => null,
        'setting'         => null,
        'uploads'         => [],
        'terms_accepted'  => false,
    ];
}

function flow_b_state(): array {
    return $_SESSION['flow_b'];
}

function flow_b_set(string $key, $value): void {
    $_SESSION['flow_b'][$key] = $value;
}

/**
 * Service catalog for Flow B — one entry per non-payment service. Taglines/intros reused
 * verbatim from each service's own detail page for consistency (see services/*.php).
 */
/* ============================================================
   Offer checkouts (e.g. Birthday Coverage) — simple, single-page,
   flat-price purchases, distinct from the multi-step Flow A/B wizards.
   ============================================================ */
if (!isset($_SESSION['offer'])) {
    $_SESSION['offer'] = [
        'slug' => null,
        'tier_key' => null,
        'tier_name' => null,
        'amount' => null,
        'customer_name' => null,
        'customer_email' => null,
        'customer_phone' => null,
        'booking_ref' => null,
    ];
}

function offer_state(): array {
    return $_SESSION['offer'];
}

function offer_set(string $key, $value): void {
    $_SESSION['offer'][$key] = $value;
}

/**
 * Birthday Coverage tier catalog — same figures as offers/index.php's display copy.
 * Kept here (not just inline on the page) so the checkout/payment/webhook can all reference
 * the same source of truth for pricing instead of trusting a client-submitted amount.
 */
function birthday_coverage_tiers(): array {
    return [
        'essential' => ['name' => 'Essential', 'price' => 168],
        'signature' => ['name' => 'Signature', 'price' => 245],
        'premium'   => ['name' => 'Premium',   'price' => 336],
    ];
}

function flow_b_service_catalog(): array {
    return [
        'film-production' => [
            'title' => 'Film Production', 'tagline' => 'Bring Your Story to Life',
            'intro' => 'From concept to final cut, we create powerful films that inspire, engage and leave a lasting impression.',
            'types' => ['Commercial Films', 'Brand Films', 'Corporate Films', 'Promotional Videos', 'Short Films', 'Feature Film Production', 'Documentary Films', 'Music Video Production', 'Livestream Production'],
            'image' => 'services/film-production',
        ],
        'music-video-production' => [
            'title' => 'Music Video Production', 'tagline' => 'Visuals That Hit Different',
            'intro' => 'We work closely with artists to translate sound into striking, original imagery.',
            'types' => ['Performance Videos', 'Narrative Music Videos', 'Lyric Videos', 'Album Artwork & Visualisers', 'Live Performance Filming'],
            'image' => 'services/music-video',
        ],
        'corporate-content' => [
            'title' => 'Corporate Content', 'tagline' => 'Professional Content That Builds Trust',
            'intro' => 'We produce corporate video and photography that communicates who you are and what you stand for.',
            'types' => ['Corporate Video Production', 'Company Profile Videos', 'Training Videos', 'Executive Interviews', 'Testimonial Videos'],
            'image' => 'services/corporate-content',
        ],
        'artwork-commission' => [
            'title' => 'Artwork Commission', 'tagline' => 'Original Art, Made Personal',
            'intro' => 'We work with clients to create original paintings and digital artworks that reflect their story.',
            'types' => ['Custom Portrait Commissions', 'Original Paintings', 'Digital Artwork Commissions', 'Corporate & Office Art', 'Abstract & Conceptual Art'],
            'image' => 'services/artwork-commission',
        ],
        'creative-direction' => [
            'title' => 'Creative Direction', 'tagline' => 'The Vision Behind the Work',
            'intro' => 'We provide the concept development, art direction, and creative oversight your project needs.',
            'types' => ['Concept Development', 'Art Direction', 'Campaign Ideation', 'Creative Supervision On Set', 'Brand Storytelling Strategy'],
            'image' => 'services/creative-direction',
        ],
        'brand-content-creation' => [
            'title' => 'Brand Content Creation', 'tagline' => 'Content That Connects',
            'intro' => 'We create photo and video content built specifically for how your brand shows up.',
            'types' => ['Social Media Content', 'Brand Photography', 'Product Content Creation', 'Advertising Creative Assets', 'Ongoing Content Retainers'],
            'image' => 'services/brand-content',
        ],
    ];
}
