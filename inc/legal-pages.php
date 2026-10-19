<?php
/**
 * SPEK legal information pages.
 *
 * Deliberately creates REVIEW DRAFTS only. Publishing requires verification
 * of the legal entity, processors, retention rules and deployed cookies.
 */
if (!defined('ABSPATH')) { exit; }

function spek_legal_page_definitions(): array {
    return [
        'privacy-policy' => ['el' => 'Πολιτική Απορρήτου', 'en' => 'Privacy Policy', 'eyebrow' => 'PRIVACY & PERSONAL DATA'],
        'cookie-policy' => ['el' => 'Πολιτική Cookies', 'en' => 'Cookie Policy', 'eyebrow' => 'COOKIES & TECHNOLOGIES'],
        'terms-of-use' => ['el' => 'Όροι Χρήσης', 'en' => 'Terms of Use', 'eyebrow' => 'WEBSITE TERMS'],
        'accessibility-statement' => ['el' => 'Δήλωση Προσβασιμότητας', 'en' => 'Accessibility Statement', 'eyebrow' => 'ACCESS FOR EVERYONE'],
    ];
}

function spek_legal_create_review_drafts(): void {
    // No publishing, overwriting, recurring updates, or front-end writes.
    if (!is_admin() || !current_user_can('manage_options')) { return; }

    foreach (spek_legal_page_definitions() as $slug => $titles) {
        if (get_page_by_path($slug, OBJECT, 'page')) { continue; }

        $el_path = SPEK_THEME_DIR . '/legal/drafts/' . $slug . '-el.php';
        $en_path = SPEK_THEME_DIR . '/legal/drafts/' . $slug . '-en.php';
        if (!is_readable($el_path) || !is_readable($en_path)) { continue; }

        $greek = require $el_path;
        $english = require $en_path;
        if ($greek === false || $english === false) { continue; }

        wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'draft',
            'post_name' => $slug,
            'post_title' => $titles['el'],
            'post_content' => $greek,
            'meta_input' => [
                '_wp_page_template' => 'templates/template-legal.php',
                '_spek_title_en' => $titles['en'],
                '_spek_content_en' => $english,
                '_spek_legal_review_required' => '1',
            ],
        ], true);
    }
}
add_action('admin_init', 'spek_legal_create_review_drafts', 25);

function spek_legal_published_page_url(string $slug): string {
    if (!isset(spek_legal_page_definitions()[$slug])) { return ''; }
    $page = get_page_by_path($slug, OBJECT, 'page');
    // A published legacy policy is not automatically considered legally reviewed.
    if (!$page || $page->post_status !== 'publish'
        || get_post_meta($page->ID, '_spek_legal_approved', true) !== '1') {
        return '';
    }
    return function_exists('spek_i18n_url')
        ? spek_i18n_url(get_permalink($page))
        : get_permalink($page);
}

add_action('wp_enqueue_scripts', static function (): void {
    if (!is_page_template('templates/template-legal.php')) { return; }
    $css = SPEK_THEME_DIR . '/assets/css/legal-pages.css';
    wp_enqueue_style(
        'spek-legal-pages',
        SPEK_THEME_URI . '/assets/css/legal-pages.css',
        ['spek-main'],
        file_exists($css) ? (string) filemtime($css) : SPEK_THEME_VERSION
    );
}, 30);


/**
 * Explicit editor sign-off in the page editor.
 * Require all visible placeholder warnings to be removed from both languages.
 */
add_action('add_meta_boxes_page', static function (WP_Post $post): void {
    if (!isset(spek_legal_page_definitions()[$post->post_name])) {
        return;
    }
    add_meta_box(
        'spek-legal-approval',
        __('Έλεγχος νομικής σελίδας', 'spek-theme'),
        static function (WP_Post $post): void {
            if (!current_user_can('manage_options')) {
                return;
            }
            wp_nonce_field('spek_legal_approval_' . $post->ID, 'spek_legal_approval_nonce');
            $approved = get_post_meta($post->ID, '_spek_legal_approved', true) === '1';
            echo '<p>' . esc_html__('Μόνο μετά από τελική νομική και τεχνική έγκριση και των δύο γλωσσών.', 'spek-theme') . '</p>';
            echo '<label><input type="checkbox" name="spek_legal_approval" value="1" ' . checked($approved, true, false) . '> '
                . esc_html__('Έγινε ο έλεγχος και εγκρίνεται η δημοσίευση', 'spek-theme') . '</label>';
        },
        'page',
        'side',
        'high'
    );
});

add_action('save_post_page', static function (int $post_id, WP_Post $post): void {
    if (!isset(spek_legal_page_definitions()[$post->post_name])
        || !current_user_can('manage_options')
        || wp_is_post_revision($post_id)) {
        return;
    }
    $nonce = isset($_POST['spek_legal_approval_nonce'])
        ? sanitize_text_field(wp_unslash((string) $_POST['spek_legal_approval_nonce']))
        : '';
    if (!$nonce || !wp_verify_nonce($nonce, 'spek_legal_approval_' . $post_id)) {
        return;
    }
    $approved = !empty($_POST['spek_legal_approval']);
    $english = (string) get_post_meta($post_id, '_spek_content_en', true);
    if ($approved && strpos($post->post_content, 'spek-pending') === false
        && $english !== '' && strpos($english, 'spek-pending') === false) {
        update_post_meta($post_id, '_spek_legal_approved', '1');
        update_post_meta($post_id, '_spek_legal_review_required', '0');
    } else {
        delete_post_meta($post_id, '_spek_legal_approved');
        update_post_meta($post_id, '_spek_legal_review_required', '1');
    }
}, 20, 2);
