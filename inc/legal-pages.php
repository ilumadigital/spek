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

        $el_path = SPEK_THEME_DIR . '/legal/drafts/' . $slug . '-el.html';
        $en_path = SPEK_THEME_DIR . '/legal/drafts/' . $slug . '-en.html';
        if (!is_readable($el_path) || !is_readable($en_path)) { continue; }

        $greek = file_get_contents($el_path);
        $english = file_get_contents($en_path);
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
    if (!$page || $page->post_status !== 'publish') { return ''; }
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
