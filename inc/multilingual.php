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

/** Product fields that may carry a dedicated English value on the same product. */
function spek_product_translated_meta_keys(): array {
    return [
        'product_short_description',
        'product_dimensions',
        'product_material',
        'product_color',
        'product_application_text',
        'product_compatibility',
        'product_installation_type',
        'product_packaging',
        'product_weight',
        'product_capacity',
        'product_features',
        '_spek_product_subtitle',
        '_spek_product_application',
        '_spek_product_series',
    ];
}

/** True when a stored value contains Greek letters and must not leak into /en/. */
function spek_i18n_value_contains_greek(string $value): bool {
    return (bool) preg_match('/[Α-Ωα-ωΆΈΉΊΌΎΏάέήίόύώϊΐϋΰ]/u', $value);
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

/** True only for the public English homepage request (/en/). */
function spek_is_english_home_request(): bool {
    if (is_admin()) { return false; }

    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
    $path = (string) wp_parse_url($uri, PHP_URL_PATH);
    $home_path = (string) wp_parse_url(home_url('/'), PHP_URL_PATH);
    $base = '/' . trim($home_path, '/');
    if ($base === '/') { $base = ''; }
    if ($base !== '' && strpos($path, $base) === 0) { $path = substr($path, strlen($base)); }
    $path = '/' . trim((string) $path, '/');

    return $path === '/en';
}

/** Convert an internal URL to its Greek/English public equivalent. */
function spek_i18n_url(string $url, string $lang = ''): string {
    $url = trim($url);
    if ($url === '') { return $url; }

    // Never rewrite anchors, browser pseudo URLs, contact links or data URLs.
    if (preg_match('#^(?:\#|mailto:|tel:|javascript:|data:)#i', $url)) { return $url; }

    $lang = $lang === 'en' ? 'en' : ($lang === 'el' ? 'el' : spek_current_language());
    $home = trailingslashit(home_url('/'));
    $home_parts = wp_parse_url($home);
    $parts = wp_parse_url($url);

    if ($parts === false || !is_array($home_parts)) { return $url; }

    // External hosts stay untouched. The protocol is deliberately ignored so
    // http -> https canonicalisation does not accidentally drop the language.
    $home_host = isset($home_parts['host']) ? strtolower((string) $home_parts['host']) : '';
    $url_host = isset($parts['host']) ? strtolower((string) $parts['host']) : '';
    if ($url_host !== '' && $home_host !== '' && $url_host !== $home_host) { return $url; }

    $home_path = '/' . trim((string) ($home_parts['path'] ?? '/'), '/');
    if ($home_path === '/') { $home_path = ''; }

    $path = isset($parts['path']) ? (string) $parts['path'] : '';
    $is_absolute_path = strpos($url, '/') === 0;
    $has_scheme_or_host = !empty($parts['scheme']) || $url_host !== '';

    if (!$has_scheme_or_host && !$is_absolute_path) {
        // Plain relative URLs are treated as site-relative links.
        $path = ($home_path !== '' ? $home_path : '') . '/' . ltrim($path, '/');
    }

    $path = '/' . ltrim($path, '/');

    // Only URLs inside this WordPress installation are rewritten.
    if ($home_path !== '') {
        if ($path !== $home_path && strpos($path, trailingslashit($home_path)) !== 0) { return $url; }
        $relative = ltrim(substr($path, strlen($home_path)), '/');
    } else {
        $relative = ltrim($path, '/');
    }

    // Idempotent: remove an existing language prefix before applying target.
    $relative = preg_replace('#^en(?:/|$)#i', '', (string) $relative);
    $relative = ltrim((string) $relative, '/');

    foreach (['wp-admin/', 'wp-login.php', 'wp-json/', 'wp-content/', 'wp-includes/'] as $skip) {
        if (strpos($relative, $skip) === 0) { return $url; }
    }

    $target_relative = ($lang === 'en' ? 'en/' : '') . $relative;
    $target = $home . $target_relative;

    if (isset($parts['query']) && $parts['query'] !== '') { $target .= '?' . $parts['query']; }
    if (isset($parts['fragment']) && $parts['fragment'] !== '') { $target .= '#' . $parts['fragment']; }

    return $target;
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

/** Resolve /en/ to the configured static front page before WP_Query is built. */
add_filter('request', static function (array $query_vars): array {
    if (!spek_is_english_home_request()) { return $query_vars; }

    $front_id = (get_option('show_on_front') === 'page') ? (int) get_option('page_on_front') : 0;
    $query_vars = ['spek_lang' => 'en'];
    if ($front_id > 0) { $query_vars['page_id'] = $front_id; }
    return $query_vars;
}, 1);

/** Resolve /en/ to the same static WordPress front page even before a rewrite flush. */
add_action('parse_request', static function ($wp): void {
    if (!spek_is_english_home_request()) { return; }

    $wp->query_vars['spek_lang'] = 'en';
    $front_id = (get_option('show_on_front') === 'page') ? (int) get_option('page_on_front') : 0;
    if ($front_id > 0) {
        $wp->query_vars['page_id'] = $front_id;
        unset($wp->query_vars['name'], $wp->query_vars['pagename'], $wp->query_vars['error']);
    }
}, 20);

add_filter('rewrite_rules_array', static function (array $rules): array {
    $front_id = (get_option('show_on_front') === 'page') ? (int) get_option('page_on_front') : 0;
    $english_home_query = $front_id > 0
        ? 'index.php?page_id=' . $front_id . '&spek_lang=en'
        : 'index.php?spek_lang=en';
    $english = ['^en/?$' => $english_home_query];
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
    $version = 'spek-i18n-20260921-1';
    if (get_option('_spek_i18n_rewrite_version') !== $version) {
        flush_rewrite_rules(false);
        update_option('_spek_i18n_rewrite_version', $version, false);
    }
});

/** Keep WordPress canonical/frontend redirects inside the currently selected language. */
add_filter('redirect_canonical', static function ($redirect_url, $requested_url) {
    if (!spek_is_english() || !$redirect_url) { return $redirect_url; }
    return spek_i18n_url((string) $redirect_url, 'en');
}, 20, 2);
add_filter('wp_redirect', static function (string $location, int $status): string {
    if (is_admin() || wp_doing_ajax() || !spek_is_english()) { return $location; }
    return spek_i18n_url($location, 'en');
}, 20, 2);

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

/** Always expose Home as the first item of the primary navigation without changing menu data. */
add_filter('wp_nav_menu_items', static function (string $items, $args): string {
    $location = '';
    if (is_object($args) && isset($args->theme_location)) { $location = (string) $args->theme_location; }
    elseif (is_array($args) && isset($args['theme_location'])) { $location = (string) $args['theme_location']; }
    if ($location !== 'primary') { return $items; }

    // WordPress adds menu-item-home for a real Home item. Avoid duplicates.
    if (strpos($items, 'menu-item-home') !== false || strpos($items, 'spek-menu-home') !== false) { return $items; }

    $classes = ['menu-item', 'menu-item-home', 'spek-menu-home'];
    if (is_front_page() || spek_is_english_home_request()) { $classes[] = 'current-menu-item'; }
    $label = function_exists('spek_i18n_translate_string')
        ? spek_i18n_translate_string('Αρχική', 'Αρχική')
        : 'Αρχική';

    $home_item = '<li class="' . esc_attr(implode(' ', $classes)) . '"><a href="' . esc_url(spek_home_url()) . '">' . esc_html($label) . '</a></li>';
    return $home_item . $items;
}, 10, 2);

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
    if ($translated !== '') { return $translated; }
    // Known page/menu titles can use the bundled i18n dictionary until a
    // specific backend override is entered. Product titles still require an
    // explicit _spek_title_en value before they are exposed in English.
    return function_exists('spek_i18n_translate_string') ? spek_i18n_translate_string($title, $title) : $title;
}, 20, 2);
add_filter('the_content', static function (string $content): string {
    if (!spek_is_english() || is_admin()) { return $content; }
    $post_id = get_the_ID();
    if (!$post_id || !in_array(get_post_type($post_id), spek_multilingual_post_types(), true)) { return $content; }
    $translated = spek_i18n_raw_post_translation($post_id, 'content');
    return $translated !== '' ? wpautop($translated) : $content;
}, 20);
add_filter('the_content', static function (string $content): string {
    if (!spek_is_english() || is_admin() || $content === '') { return $content; }

    if (class_exists('WP_HTML_Tag_Processor')) {
        $processor = new WP_HTML_Tag_Processor($content);
        while ($processor->next_tag('A')) {
            $href = $processor->get_attribute('href');
            if (is_string($href) && $href !== '') { $processor->set_attribute('href', spek_i18n_url($href, 'en')); }
        }
        return $processor->get_updated_html();
    }

    return preg_replace_callback("#href=([\"'])(.*?)\\1#i", static function (array $match): string {
        return 'href=' . $match[1] . esc_url(spek_i18n_url(html_entity_decode($match[2], ENT_QUOTES), 'en')) . $match[3];
    }, $content) ?: $content;
}, 30);
add_filter('get_the_excerpt', static function (string $excerpt, $post): string {
    if (!spek_is_english() || is_admin() || !$post instanceof WP_Post) { return $excerpt; }
    $translated = spek_i18n_raw_post_translation((int) $post->ID, 'excerpt');
    if ($translated !== '') { return $translated; }
    return function_exists('spek_i18n_translate_string') ? spek_i18n_translate_string($excerpt, $excerpt) : $excerpt;
}, 20, 2);
add_filter('get_post_metadata', static function ($value, $object_id, $meta_key, $single) {
    static $busy = false;
    if ($busy || !spek_is_english() || (is_admin() && !wp_doing_ajax()) || !$meta_key || !in_array($meta_key, spek_i18n_translatable_post_meta_keys(), true)) { return $value; }
    $busy = true;
    $translated = spek_i18n_raw_post_translation((int) $object_id, (string) $meta_key);
    $post_type = get_post_type((int) $object_id);
    $busy = false;
    if ($translated !== '') { return $single ? $translated : [$translated]; }

    // Never leak Greek product copy into /en/. A missing translation may reuse
    // the canonical value only when that value contains no Greek characters
    // (e.g. 1/2", 9 L, ABS, Soft close). Otherwise the field stays empty.
    if ($post_type === 'spek_product') {
        $busy = true;
        $original = get_metadata_raw('post', (int) $object_id, (string) $meta_key, true);
        $busy = false;
        $original = is_scalar($original) ? trim((string) $original) : '';
        if ($original === '' || spek_i18n_value_contains_greek($original)) {
            return $single ? '' : [];
        }
    }

    return $value;
}, 20, 4);

/** English taxonomy labels/descriptions are stored as term meta. */
function spek_i18n_translate_term_object($term) {
    if (!spek_is_english() || is_admin() || !$term instanceof WP_Term || !in_array((string) $term->taxonomy, spek_multilingual_taxonomies(), true)) { return $term; }
    $copy = clone $term;
    $name = get_term_meta($term->term_id, '_spek_name_en', true);
    $description = get_term_meta($term->term_id, '_spek_description_en', true);
    if (is_string($name) && $name !== '') {
        $copy->name = $name;
    } elseif (function_exists('spek_import_default_taxonomy_translation')) {
        $mapped = spek_import_default_taxonomy_translation((string) $term->name, (string) $term->taxonomy);
        $copy->name = $mapped !== '' ? $mapped : (function_exists('spek_i18n_translate_string') ? spek_i18n_translate_string((string) $term->name, (string) $term->name) : (string) $term->name);
    } elseif (function_exists('spek_i18n_translate_string')) {
        $copy->name = spek_i18n_translate_string((string) $term->name, (string) $term->name);
    }
    if (is_string($description) && $description !== '') {
        $copy->description = $description;
    } elseif ($term->description !== '' && function_exists('spek_i18n_translate_string')) {
        $copy->description = spek_i18n_translate_string((string) $term->description, (string) $term->description);
    }
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
function spek_i18n_normalize_source(string $value): string {
    $value = trim((string) preg_replace('/\s+/u', ' ', $value));
    if (function_exists('mb_strtolower')) { return mb_strtolower($value, 'UTF-8'); }

    // mbstring is not guaranteed on every host. Keep Greek menu/string lookup
    // case-insensitive with an explicit Unicode map, then lowercase ASCII too.
    $value = strtr($value, [
        'Α' => 'α', 'Β' => 'β', 'Γ' => 'γ', 'Δ' => 'δ', 'Ε' => 'ε', 'Ζ' => 'ζ',
        'Η' => 'η', 'Θ' => 'θ', 'Ι' => 'ι', 'Κ' => 'κ', 'Λ' => 'λ', 'Μ' => 'μ',
        'Ν' => 'ν', 'Ξ' => 'ξ', 'Ο' => 'ο', 'Π' => 'π', 'Ρ' => 'ρ', 'Σ' => 'σ',
        'Τ' => 'τ', 'Υ' => 'υ', 'Φ' => 'φ', 'Χ' => 'χ', 'Ψ' => 'ψ', 'Ω' => 'ω',
        'Ά' => 'ά', 'Έ' => 'έ', 'Ή' => 'ή', 'Ί' => 'ί', 'Ό' => 'ό', 'Ύ' => 'ύ',
        'Ώ' => 'ώ', 'Ϊ' => 'ϊ', 'Ϋ' => 'ϋ',
    ]);
    return strtolower($value);
}
function spek_i18n_translate_string(string $source, string $fallback = ''): string {
    if (!spek_is_english()) { return $fallback !== '' ? $fallback : $source; }

    $normalized_source = spek_i18n_normalize_source($source);
    $custom = get_option('spek_i18n_strings_en', []);
    if (is_array($custom)) {
        if (isset($custom[$source]) && trim((string) $custom[$source]) !== '') { return (string) $custom[$source]; }

        static $normalized_custom = null;
        if ($normalized_custom === null) {
            $normalized_custom = [];
            foreach ($custom as $custom_source => $custom_value) {
                if (trim((string) $custom_value) === '') { continue; }
                $normalized_custom[spek_i18n_normalize_source((string) $custom_source)] = (string) $custom_value;
            }
        }
        if (isset($normalized_custom[$normalized_source])) { return $normalized_custom[$normalized_source]; }
    }

    $defaults = spek_i18n_default_strings_en();
    if (isset($defaults[$source]) && trim((string) $defaults[$source]) !== '') { return (string) $defaults[$source]; }

    static $normalized_defaults = null;
    if ($normalized_defaults === null) {
        $normalized_defaults = [];
        foreach ($defaults as $default_source => $default_value) {
            if (trim((string) $default_value) === '') { continue; }
            $normalized_defaults[spek_i18n_normalize_source((string) $default_source)] = (string) $default_value;
        }
    }
    if (isset($normalized_defaults[$normalized_source])) { return $normalized_defaults[$normalized_source]; }

    return $fallback !== '' ? $fallback : $source;
}
add_filter('gettext_spek-theme', static function (string $translation, string $text): string {
    return spek_i18n_translate_string($text, $translation);
}, 20, 2);
add_filter('ngettext_spek-theme', static function (string $translation, string $single, string $plural, int $number): string {
    return spek_i18n_translate_string($number === 1 ? $single : $plural, $translation);
}, 20, 4);

/** Stable language hooks for CSS/JS without changing the WordPress locale. */
add_filter('body_class', static function (array $classes): array {
    $classes[] = spek_is_english() ? 'spek-lang-en' : 'spek-lang-el';
    return array_values(array_unique($classes));
});

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

/** Resolve the English root to the real front-page template and keep product taxonomy layout. */
add_filter('template_include', static function ($template) {
    if (spek_is_english_home_request()) {
        $front = SPEK_THEME_DIR . '/front-page.php';
        if (file_exists($front)) { return $front; }
    }

    return is_tax(['product_category', 'product_application', 'product_material', 'product_series'])
        ? SPEK_THEME_DIR . '/archive-spek_product.php' : $template;
}, 20);
