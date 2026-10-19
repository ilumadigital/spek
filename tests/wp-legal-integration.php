<?php
/**
 * Real WordPress database smoke tests. Executed by:
 * wp eval-file tests/wp-legal-integration.php
 * Run manually ONLY on a disposable WordPress database with outgoing mail intercepted.
 */
if (!defined('ABSPATH') || !function_exists('wp_set_current_user')) {
    exit(1);
}
function spek_wp_smoke_assert($ok, string $label): void {
    if (!$ok) {
        throw new RuntimeException('FAIL ' . $label);
    }
    echo 'PASS ' . $label . PHP_EOL;
}

wp_set_current_user(1); // Disposable CI admin, not a real account.
spek_wp_smoke_assert(current_user_can('manage_options'), 'WordPress admin context exists');

$existing_privacy = get_page_by_path('privacy-policy', OBJECT, 'page');
$existing_privacy_content = $existing_privacy ? $existing_privacy->post_content : null;
do_action('admin_init');
foreach (['privacy-policy', 'cookie-policy', 'terms-of-use', 'accessibility-statement'] as $slug) {
    $page = get_page_by_path($slug, OBJECT, 'page');
    spek_wp_smoke_assert($page instanceof WP_Post, $slug . ' exists');
    spek_wp_smoke_assert($page->post_status === 'draft', $slug . ' is unpublished');
    if ($slug === 'privacy-policy' && $existing_privacy) {
        spek_wp_smoke_assert($page->post_content === $existing_privacy_content, 'Existing WordPress privacy draft was not overwritten silently');
        spek_wp_smoke_assert(spek_legal_published_page_url($slug) === '', 'Unapproved existing privacy draft is not advertised');
        continue;
    }
    spek_wp_smoke_assert(get_post_meta($page->ID, '_wp_page_template', true) === 'templates/template-legal.php', $slug . ' uses corporate template');
    spek_wp_smoke_assert((string) get_post_meta($page->ID, '_spek_content_en', true) !== '', $slug . ' has English legal draft');
    spek_wp_smoke_assert((string) get_post_meta($page->ID, '_spek_legal_approved', true) !== '1', $slug . ' is not falsely approved');
    spek_wp_smoke_assert(spek_legal_published_page_url($slug) === '', $slug . ' is absent from public legal navigation');
}
do_action('admin_init');
$pages = get_posts(['post_type' => 'page', 'post_status' => 'draft', 'posts_per_page' => -1, 'suppress_filters' => true]);
$slugs = array_map(static fn($p) => $p->post_name, $pages);
foreach (['privacy-policy', 'cookie-policy', 'terms-of-use', 'accessibility-statement'] as $slug) {
    spek_wp_smoke_assert(count(array_filter($slugs, static fn($s) => $s === $slug)) === 1, $slug . ' creation is idempotent');
}

add_filter('pre_wp_mail', static function ($short_circuit) { return true; });
$ok = spek_newsletter_register_interest('smoke-user@example.test', 'Smoke', 'Test', 'el');
spek_wp_smoke_assert($ok, 'WordPress stored pending subscriber, email transport intercepted');
$subscribers = get_posts([
    'post_type' => 'spek_newsletter',
    'post_status' => 'publish',
    'posts_per_page' => 2,
    'meta_key' => '_spek_newsletter_email',
    'meta_value' => 'smoke-user@example.test',
    'suppress_filters' => true,
]);
spek_wp_smoke_assert(count($subscribers) === 1, 'One real WordPress newsletter record stored');
$id = $subscribers[0]->ID;
spek_wp_smoke_assert(get_post_meta($id, '_spek_newsletter_status', true) === 'pending', 'Newsletter confirmation required');
spek_wp_smoke_assert(get_post_meta($id, '_spek_newsletter_opted_at_utc', true) !== '', 'Consent timestamp stored');
spek_wp_smoke_assert(get_post_meta($id, '_spek_newsletter_consent_sha256', true) !== '', 'Consent notice hash stored');
spek_wp_smoke_assert((string) get_post_meta($id, '_spek_contact_message', true) === '', 'Newsletter record has no enquiry message');
spek_wp_smoke_assert(!spek_retention_cleanup_enabled(), 'Destructive retention cleanup disabled without explicit configuration');

echo 'All disposable WordPress integration smoke tests passed.' . PHP_EOL;
