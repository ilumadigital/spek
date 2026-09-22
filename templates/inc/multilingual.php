<?php
/**
 * SPEK custom i18n layer.
 *
 * One canonical WordPress record is kept for each page/product/term. English
 * values live in post/term/menu meta and theme strings are managed from the
 * WordPress admin. The public English version is exposed under /en/.
 *
 * @package SpekTheme
 */
if (!defined('ABSPATH')) { exit; }

function spek_multilingual_post_types(): array {
    return ['page', 'post', 'spek_product', 'spek_catalogue', 'spek_partner', 'spek_career'];
}
function spek_multilingual_taxonomies(): array {
    return ['product_category', 'product_application', 'product_material', 'product_series', 'partner_region', 'partner_type'];
}

/** Detect language from the public URL or an explicit frontend AJAX request. */
function spek_current_language(): string {
    if (wp_doing_ajax()) {
        $requested = isset($_REQUEST['lang']) ? sanitize_key(wp_unslash((string) $_REQUEST['lang'])) : '';
        return $requested === 'en' ? 'en' : 'el';
    }

    if (is_admin()) { return 'el'; }

    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
    $path = (string) wp_parse_url($uri, PHP_URL_PATH);
    $home_path = (string) wp_parse_url(home_url('/'), PHP_URL_PATH);
    $base = '/' . trim($home_path, '/');
    if ($base === '/') { $base = ''; }
    if ($base !== '' && strpos($path, $base) === 0) { $path = substr($path, strlen($base)); }
    $path = '/' . ltrim($path, '/');

    return preg_match('#^/en(?:/|$)#i', $path) ? 'en' : 'el';
}
function spek_language_for_locale(string $prefix): string {
    return stripos($prefix, 'en') === 0 ? 'en' : 'el';
}
function spek_language_args(array $args = []): array {
    unset($args['lang']);
    $args['suppress_filters'] = false;
    return $args;
}
function spek_is_english(): bool { return spek_current_language() === 'en'; }

/** Convert an internal URL to its Greek/English public equivalent. */
function spek_i18n_url(string $url, string $lang = ''): string {
    $lang = $lang ?: spek_current_language();
    $home = trailingslashit(home_url('/'));
    if (strpos($url, $home) !== 0) { return $url; }

    $relative = substr($url, strlen($home));
    $relative = preg_replace('#^en(?:/|$)#i', '', (string) $relative);
    $relative = ltrim((string) $relative, '/');

    foreach (['wp-admin/', 'wp-login.php', 'wp-json/', 'wp-content/', 'wp-includes/'] as $skip) {
        if (strpos($relative, $skip) === 0) { return $url; }
    }

    return $lang === 'en' ? $home . 'en/' . $relative : $home . $relative;
}
function spek_home_url(string $lang = ''): string {
    return spek_i18n_url(home_url('/'), $lang ?: spek_current_language());
}
function spek_page_url(string $path): string {
    static $cache = [];
    $path = trim($path, '/');
    $key = spek_current_language() . ':' . $path;
    if (isset($cache[$key])) { return $cache[$key]; }
    if ($path === '') { return $cache[$key] = spek_home_url(); }
    if ($path === 'products') {
        $url = get_post_type_archive_link('spek_product') ?: home_url('/products/');
        return $cache[$key] = spek_i18n_url((string) $url);
    }
    $page = get_page_by_path($path, OBJECT, 'page');
    $url = ($page && get_post_status($page) === 'publish') ? get_permalink($page) : home_url('/' . $path . '/');
    return $cache[$key] = spek_i18n_url((string) $url);
}
function spek_term_url(string $slug, string $taxonomy = 'product_category'): string {
    $term = get_term_by('slug', $slug, $taxonomy);
    if (!$term || is_wp_error($term)) { return spek_home_url(); }
    $url = get_term_link($term);
    return !is_wp_error($url) ? spek_i18n_url((string) $url) : spek_home_url();
}
function spek_menu_location(string $base): string {
    $base = sanitize_key($base);
    return ($base && has_nav_menu($base)) ? $base : '';
}
function spek_allow_hardcoded_menu_fallback(): bool { return true; }

/** Duplicate every public rewrite rule under /en/. */
add_filter('query_vars', static function (array $vars): array {
    $vars[] = 'spek_lang';
    return array_values(array_unique($vars));
});
add_filter('rewrite_rules_array', static function (array $rules): array {
    $english = ['^en/?$' => 'index.php?spek_lang=en'];
    foreach ($rules as $regex => $query) {
        if (strpos($regex, '^en/') === 0) { continue; }
        $body = ltrim($regex, '^/');
        $en_regex = '^en/' . $body;
        $separator = strpos($query, '?') === false ? '?' : '&';
        $english[$en_regex] = $query . $separator . 'spek_lang=en';
    }
    return $english + $rules;
});
add_action('admin_init', static function (): void {
    $version = 'spek-i18n-20260918-1';
    if (get_option('_spek_i18n_rewrite_version') !== $version) {
        flush_rewrite_rules(false);
        update_option('_spek_i18n_rewrite_version', $version, false);
    }
});

/** Public links keep one canonical object but receive the current language prefix. */
function spek_i18n_filter_permalink(string $url): string {
    if (is_admin() && !wp_doing_ajax()) { return $url; }
    return spek_i18n_url($url);
}
add_filter('post_link', 'spek_i18n_filter_permalink', 20);
add_filter('post_type_link', 'spek_i18n_filter_permalink', 20);
add_filter('page_link', 'spek_i18n_filter_permalink', 20);
add_filter('post_type_archive_link', 'spek_i18n_filter_permalink', 20);
add_filter('get_pagenum_link', 'spek_i18n_filter_permalink', 20);
add_filter('term_link', static function ($url) {
    return is_wp_error($url) ? $url : spek_i18n_filter_permalink((string) $url);
}, 20);
add_filter('nav_menu_link_attributes', static function (array $atts): array {
    if (!empty($atts['href'])) { $atts['href'] = spek_i18n_url((string) $atts['href']); }
    return $atts;
}, 20);

/** Correct document language attributes without changing the WordPress admin locale. */
add_filter('language_attributes', static function (string $output): string {
    if (!spek_is_english()) { return $output; }
    if (preg_match('/lang=("|\')[^"\']+("|\')/i', $output)) {
        $output = preg_replace('/lang=("|\')[^"\']+("|\')/i', 'lang="en-US"', $output, 1);
    } else {
        $output = trim($output . ' lang="en-US"');
    }
    return $output;
});

/** English post fields live on the same canonical record. */
function spek_i18n_post_meta_key(string $field): string {
    if ($field === 'title') { return '_spek_title_en'; }
    if ($field === 'content') { return '_spek_content_en'; }
    if ($field === 'excerpt') { return '_spek_excerpt_en'; }
    $clean = ltrim($field, '_');
    if (strpos($clean, 'spek_') === 0) { $clean = substr($clean, 5); }
    return '_spek_' . $clean . '_en';
}
function spek_i18n_raw_post_translation(int $post_id, string $field): string {
    $key = spek_i18n_post_meta_key($field);
    $value = get_metadata_raw('post', $post_id, $key, true);
    if (($value === '' || $value === null) && strpos($field, '_spek_') === 0) {
        // Read the temporary double-prefixed key produced by an earlier importer build.
        $legacy = get_metadata_raw('post', $post_id, '_spek_' . $field . '_en', true);
        if ($legacy !== '' && $legacy !== null) { $value = $legacy; }
    }
    return is_scalar($value) ? (string) $value : '';
}
function spek_i18n_translatable_post_meta_keys(): array {
    return array_values(array_unique(array_merge(
        function_exists('spek_product_translated_meta_keys') ? spek_product_translated_meta_keys() : [],
        ['product_short_description', 'product_dimensions', 'product_weight', 'product_capacity', 'product_material', 'product_color', 'product_application_text', 'product_compatibility', 'product_installation_type', 'product_packaging', 'product_features', '_spek_product_subtitle', '_spek_product_application', '_spek_product_series']
    )));
}
add_filter('the_title', static function (string $title, int $post_id = 0): string {
    if (!spek_is_english() || is_admin() || !$post_id || !in_array(get_post_type($post_id), spek_multilingual_post_types(), true)) { return $title; }
    $translated = spek_i18n_raw_post_translation($post_id, 'title');
    return $translated !== '' ? $translated : $title;
}, 20, 2);
add_filter('the_content', static function (string $content): string {
    if (!spek_is_english() || is_admin()) { return $content; }
    $post_id = get_the_ID();
    if (!$post_id || !in_array(get_post_type($post_id), spek_multilingual_post_types(), true)) { return $content; }
    $translated = spek_i18n_raw_post_translation($post_id, 'content');
    return $translated !== '' ? wpautop($translated) : $content;
}, 20);
add_filter('get_the_excerpt', static function (string $excerpt, $post): string {
    if (!spek_is_english() || is_admin() || !$post instanceof WP_Post) { return $excerpt; }
    $translated = spek_i18n_raw_post_translation((int) $post->ID, 'excerpt');
    return $translated !== '' ? $translated : $excerpt;
}, 20, 2);
add_filter('get_post_metadata', static function ($value, $object_id, $meta_key, $single) {
    static $busy = false;
    if ($busy || !spek_is_english() || (is_admin() && !wp_doing_ajax()) || !$meta_key || !in_array($meta_key, spek_i18n_translatable_post_meta_keys(), true)) { return $value; }
    $busy = true;
    $translated = spek_i18n_raw_post_translation((int) $object_id, (string) $meta_key);
    $busy = false;
    if ($translated === '') { return $value; }
    return $single ? $translated : [$translated];
}, 20, 4);

/** English taxonomy labels/descriptions are stored as term meta. */
function spek_i18n_translate_term_object($term) {
    if (!spek_is_english() || is_admin() || !$term instanceof WP_Term || !in_array((string) $term->taxonomy, spek_multilingual_taxonomies(), true)) { return $term; }
    $copy = clone $term;
    $name = get_term_meta($term->term_id, '_spek_name_en', true);
    $description = get_term_meta($term->term_id, '_spek_description_en', true);
    if (is_string($name) && $name !== '') { $copy->name = $name; }
    if (is_string($description) && $description !== '') { $copy->description = $description; }
    return $copy;
}
add_filter('get_term', static function ($term, $taxonomy) {
    return spek_i18n_translate_term_object($term);
}, 20, 2);
add_filter('get_terms', static function ($terms) {
    if (!spek_is_english() || is_admin() || !is_array($terms)) { return $terms; }
    return array_map('spek_i18n_translate_term_object', $terms);
}, 20, 1);

/** English menu labels are stored on the same menu item. */
add_filter('nav_menu_item_title', static function (string $title, $item): string {
    if (!spek_is_english() || !$item instanceof WP_Post) { return $title; }
    $translated = get_post_meta($item->ID, '_spek_label_en', true);
    if (is_string($translated) && $translated !== '') { return $translated; }
    return function_exists('spek_i18n_translate_string') ? spek_i18n_translate_string($title, $title) : $title;
}, 20, 2);

/** Theme string translations: admin override -> bundled defaults -> source. */
function spek_registered_strings(): array {
    static $strings;
    if ($strings === null) { $strings = require SPEK_THEME_DIR . '/inc/translation-strings.php'; }
    return is_array($strings) ? $strings : [];
}
function spek_i18n_default_strings_en(): array {
    static $strings;
    if ($strings === null) {
        $file = SPEK_THEME_DIR . '/inc/i18n-defaults-en.php';
        $strings = file_exists($file) ? require $file : [];
    }
    return is_array($strings) ? $strings : [];
}
function spek_i18n_translate_string(string $source, string $fallback = ''): string {
    if (!spek_is_english()) { return $fallback !== '' ? $fallback : $source; }
    $custom = get_option('spek_i18n_strings_en', []);
    if (is_array($custom) && isset($custom[$source]) && trim((string) $custom[$source]) !== '') { return (string) $custom[$source]; }
    $defaults = spek_i18n_default_strings_en();
    if (isset($defaults[$source]) && trim((string) $defaults[$source]) !== '') { return (string) $defaults[$source]; }
    return $fallback !== '' ? $fallback : $source;
}
add_filter('gettext_spek-theme', static function (string $translation, string $text): string {
    return spek_i18n_translate_string($text, $translation);
}, 20, 2);
add_filter('ngettext_spek-theme', static function (string $translation, string $single, string $plural, int $number): string {
    return spek_i18n_translate_string($number === 1 ? $single : $plural, $translation);
}, 20, 4);

/** On English product pages, never expose a product without an English title. */
add_action('template_redirect', static function (): void {
    if (!spek_is_english() || !is_singular('spek_product')) { return; }
    $id = get_queried_object_id();
    if ($id && spek_i18n_raw_post_translation((int) $id, 'title') === '') {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }
}, 1);

/** Product taxonomy archives use the product archive layout. */
add_filter('template_include', static function ($template) {
    return is_tax(['product_category', 'product_application', 'product_material', 'product_series'])
        ? SPEK_THEME_DIR . '/archive-spek_product.php' : $template;
});
