<?php
/**
 * Standalone, WordPress-free smoke tests for the SPEK legal/newsletter branch.
 * Run: php tests/test-legal-newsletter.php
 */
declare(strict_types=1);
define('ABSPATH', __DIR__ . '/../');
define('DAY_IN_SECONDS', 86400);
define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', 3600);

$GLOBALS['spek_test_posts'] = [];
$GLOBALS['spek_test_meta'] = [];
$GLOBALS['spek_test_mail'] = [];
$GLOBALS['spek_test_transients'] = [];
$GLOBALS['spek_test_deleted'] = [];
$GLOBALS['spek_test_id'] = 100;
$GLOBALS['spek_test_mail_result'] = true;

function add_action(...$args): void {}
function add_filter(...$args): void {}
function sanitize_email($v): string { return filter_var(trim((string) $v), FILTER_SANITIZE_EMAIL); }
function sanitize_text_field($v): string { return trim(strip_tags((string) $v)); }
function is_email($v): bool { return filter_var($v, FILTER_VALIDATE_EMAIL) !== false; }
function is_wp_error($v): bool { return false; }
function get_posts(array $q): array {
    $matches = [];
    foreach ($GLOBALS['spek_test_posts'] as $id => $p) {
        if ($p['post_type'] !== ($q['post_type'] ?? '') || $p['post_status'] !== ($q['post_status'] ?? 'publish')) { continue; }
        if (isset($q['meta_key']) && (($GLOBALS['spek_test_meta'][$id][$q['meta_key']] ?? '') !== ($q['meta_value'] ?? ''))) { continue; }
        $matches[] = $id;
    }
    return array_slice($matches, 0, $q['posts_per_page'] ?? 10);
}
function get_post_meta(int $id, string $key, bool $single = false) {
    return $GLOBALS['spek_test_meta'][$id][$key] ?? '';
}
function update_post_meta(int $id, string $key, $value): void { $GLOBALS['spek_test_meta'][$id][$key] = $value; }
function delete_post_meta(int $id, string $key): void { unset($GLOBALS['spek_test_meta'][$id][$key]); }
function wp_insert_post(array $post, bool $error = false) {
    $id = ++$GLOBALS['spek_test_id'];
    $GLOBALS['spek_test_posts'][$id] = $post;
    return $id;
}
function wp_generate_password(int $length, bool $special = false, bool $extra = false): string {
    return substr(str_repeat('Ab19Cd42Ef53', 10) . substr(bin2hex(random_bytes(8)), 0, 16), 0, $length);
}
function current_time(string $type, bool $gmt = false): string { return '2026-10-19 11:24:00'; }
function get_transient(string $key) { return $GLOBALS['spek_test_transients'][$key] ?? false; }
function set_transient(string $key, $value, int $expiration): void { $GLOBALS['spek_test_transients'][$key] = $value; }
function home_url(string $path): string { return 'https://example.test' . $path; }
function add_query_arg(array $args, string $url): string { return $url . '?' . http_build_query($args); }
function wp_mail($to, $subject, $body, $headers = []): bool {
    $GLOBALS['spek_test_mail'][] = compact('to', 'subject', 'body');
    return $GLOBALS['spek_test_mail_result'];
}
function wp_next_scheduled(string $hook) { return false; }
function wp_schedule_event(...$args): bool { return true; }
function get_post_type(int $id): string { return $GLOBALS['spek_test_posts'][$id]['post_type'] ?? ''; }
function wp_delete_post(int $id, bool $force = false): void {
    $GLOBALS['spek_test_deleted'][] = $id;
    unset($GLOBALS['spek_test_posts'][$id], $GLOBALS['spek_test_meta'][$id]);
}

require __DIR__ . '/../inc/newsletter-interest.php';
require __DIR__ . '/../inc/data-retention.php';

function check($condition, string $message): void {
    if (!$condition) { throw new RuntimeException('FAIL ' . $message); }
    echo 'PASS ' . $message . PHP_EOL;
}

check(!spek_retention_cleanup_enabled(), 'Destructive cleanup disabled by default');
spek_retention_run();
check(!$GLOBALS['spek_test_deleted'], 'Disabled cleanup does not delete records');

check(strpos(spek_newsletter_consent_text('el'), 'Προαιρετικά') !== false, 'Greek consent wording clear');
check(strpos(spek_newsletter_consent_text('en'), 'Optional') !== false, 'English consent wording clear');

$ok = spek_newsletter_register_interest('TEST@example.test', 'Γιάννης', 'Δοκιμή', 'el');
check($ok, 'Voluntary newsletter interest can be registered');
check(count($GLOBALS['spek_test_posts']) === 1, 'One subscriber stored');
$id = array_key_first($GLOBALS['spek_test_posts']);
check(get_post_meta($id, '_spek_newsletter_status', true) === 'pending', 'New subscription unconfirmed');
check(get_post_meta($id, '_spek_newsletter_email', true) === 'test@example.test', 'Email normalised');
check(get_post_meta($id, '_spek_newsletter_first_name', true) === 'Γιάννης', 'Given name stored');
check(get_post_meta($id, '_spek_newsletter_source', true) === 'contact_form', 'Consent source tracked');
check(get_post_meta($id, '_spek_newsletter_consent_sha256', true) === hash('sha256', spek_newsletter_consent_text('el')), 'Consent text provenance tracked');
check(count($GLOBALS['spek_test_mail']) === 1, 'One functional verification email sent');

$body = $GLOBALS['spek_test_mail'][0]['body'];
check(str_contains($body, 'spek_newsletter_action=confirm'), 'Email contains confirmation link');
check(str_contains($body, 'spek_newsletter_action=withdraw'), 'Email contains cancellation link');
check(!str_contains($body, 'newsletter campaign'), 'No mailing campaign is dispatched');

preg_match('/spek_newsletter_action=confirm&[^\n]*?spek_newsletter_token=([A-Za-z0-9]+)/', $body, $match);
check(isset($match[1]), 'Confirmation token can be found in verification email');
check(hash('sha256', $match[1]) === get_post_meta($id, '_spek_newsletter_confirm_token', true), 'Only confirmation token hash stored');
check((int) get_post_meta($id, '_spek_newsletter_confirm_expires', true) > time(), 'Confirmation link expires in the future');

$again = spek_newsletter_register_interest('test@example.test', 'Γιάννης', 'Δοκιμή', 'el');
check($again && count($GLOBALS['spek_test_posts']) === 1 && count($GLOBALS['spek_test_mail']) === 1, 'Repeat opt-in throttled and deduplicated');

check(!spek_newsletter_register_interest('not-email', 'A', 'B', 'en'), 'Bad emails rejected');
check(count($GLOBALS['spek_test_posts']) === 1, 'Invalid entry not saved');

$contactForm = file_get_contents(__DIR__ . '/../templates/template-contact.php');
check(str_contains($contactForm, 'name="newsletter_opt_in"'), 'Optional newsletter checkbox wired into contact form');
check(!preg_match('/name="newsletter_opt_in"[^>]*checked/i', $contactForm), 'Newsletter consent is not preselected');
$contactHandler = file_get_contents(__DIR__ . '/../inc/contact-submissions.php');
check(str_contains($contactHandler, 'if ($newsletter_opt_in'), 'Separate opt-in guarded by user choice');
check(str_contains($contactHandler, 'spek_newsletter_register_interest('), 'Contact handler calls separate newsletter registrar');

$privacyGr = require __DIR__ . '/../legal/drafts/privacy-policy-el.php';
$privacyEn = require __DIR__ . '/../legal/drafts/privacy-policy-en.php';
check(str_contains($privacyGr, '12 μήνες') && str_contains($privacyGr, '9 μήνες'), 'Greek policy reflects approved 12/9 months');
check(str_contains($privacyEn, '12 months') && str_contains($privacyEn, '9 months'), 'English policy reflects approved 12/9 months');
check(str_contains($privacyGr, 'WordPress'), 'Greek newsletter local-storage disclosure');
check(str_contains($privacyEn, 'WordPress'), 'English newsletter local-storage disclosure');

$careers = file_get_contents(__DIR__ . '/../template-parts/careers/application-form.php');
check(str_contains($careers, 'name="scientific_interest"'), 'Career interest field exists');
check(str_contains($careers, 'name="future_roles_opt_in"'), 'Separate future-role consent exists');
check(!preg_match('/name="future_roles_opt_in"[^>]*checked/i', $careers), 'Future-role consent not preselected');

echo 'All targeted source and logic smoke tests passed.' . PHP_EOL;
