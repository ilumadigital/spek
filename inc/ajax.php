<?php
/**
 * Theme AJAX handlers.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Read a scalar finder request value safely. */
function spek_finder_request_value(string $key): string
{
    if (!isset($_POST[$key]) || !is_scalar($_POST[$key])) {
        return '';
    }

    return trim(sanitize_text_field(wp_unslash((string) $_POST[$key])));
}

/** Normalize the frontend language carried by the finder AJAX request. */
function spek_finder_request_language(): string
{
    $lang = isset($_REQUEST['lang']) ? sanitize_key(wp_unslash((string) $_REQUEST['lang'])) : 'el';
    return in_array($lang, ['el', 'en'], true) ? $lang : 'el';
}

/** Collect all supported product-finder filters from the request. */
function spek_finder_request_filters(): array
{
    return [
        'product_category' => spek_finder_request_value('product_category'),
        'product_application' => spek_finder_request_value('product_application'),
        'product_material' => spek_finder_request_value('product_material'),
        'product_series' => spek_finder_request_value('product_series'),
        'product_installation_type' => spek_finder_request_value('product_installation_type'),
        'cover_dimensions_known' => spek_finder_request_value('cover_dimensions_known'),
        'cover_length' => spek_finder_request_value('cover_length'),
        'cover_width' => spek_finder_request_value('cover_width'),
    ];
}

/** Finder values backed by post meta are encoded so they cannot be confused with taxonomy slugs. */
function spek_finder_encode_meta_option(string $meta_key, string $value): string
{
    return 'meta:' . rawurlencode($meta_key) . ':' . rawurlencode($value);
}

/** Decode a finder meta-backed value. */
function spek_finder_decode_meta_option(string $value): array
{
    if (strpos($value, 'meta:') !== 0) {
        return [];
    }

    $parts = explode(':', $value, 3);
    if (count($parts) !== 3) {
        return [];
    }

    $meta_key = rawurldecode((string) $parts[1]);
    $meta_value = rawurldecode((string) $parts[2]);

    $allowed = [
        '_spek_product_application',
        'product_application_text',
        'product_material',
        '_spek_product_series',
    ];

    if (!in_array($meta_key, $allowed, true) || $meta_value === '') {
        return [];
    }

    return [
        'key' => $meta_key,
        'value' => $meta_value,
    ];
}

/** Normalize labels so taxonomy and meta fallbacks do not create duplicate UI choices. */
function spek_finder_normalize_option_label(string $value): string
{
    $value = trim(wp_strip_all_tags($value));
    $value = remove_accents($value);

    return function_exists('mb_strtolower')
        ? mb_strtolower($value, 'UTF-8')
        : strtolower($value);
}

/** Turn a decimal centimetre value into stable textual variants for legacy dimensions fields. */
function spek_finder_dimension_tokens(float $value): array
{
    $tokens = [];
    $one_decimal = number_format($value, 1, '.', '');
    $trimmed = rtrim(rtrim($one_decimal, '0'), '.');

    if ($trimmed !== '') {
        $tokens[] = $trimmed;
        if (strpos($trimmed, '.') !== false) {
            $tokens[] = str_replace('.', ',', $trimmed);
        }
    }

    // Some catalogues store the same dimension in millimetres instead of centimetres.
    $millimetres = (string) round($value * 10);
    if ($millimetres !== '') {
        $tokens[] = $millimetres;
    }

    return array_values(array_unique(array_filter($tokens)));
}

/**
 * Build a dimension clause that supports future structured fields and the current free-text dimensions field.
 * A small tolerance is allowed on dedicated numeric fields because seat covers are commonly specified approximately.
 */
function spek_finder_dimension_meta_clause(string $structured_key, float $value): array
{
    $tolerance = 1.5;
    $clause = [
        'relation' => 'OR',
        [
            'key' => $structured_key,
            'value' => [max(0, $value - $tolerance), $value + $tolerance],
            'type' => 'DECIMAL',
            'compare' => 'BETWEEN',
        ],
    ];

    foreach (spek_finder_dimension_tokens($value) as $token) {
        $clause[] = [
            'key' => 'product_dimensions',
            'value' => $token,
            'compare' => 'LIKE',
        ];
    }

    return $clause;
}

/** Build a language-aware query clause for a meta-backed finder option. */
function spek_finder_meta_option_query_clause(string $meta_key, string $value, string $lang): array
{
    $clause = [
        'relation' => 'OR',
        [
            'key' => $meta_key,
            'value' => $value,
            'compare' => '=',
        ],
    ];

    if ($lang === 'en' && function_exists('spek_i18n_post_meta_key')) {
        $translated_key = spek_i18n_post_meta_key($meta_key);
        if ($translated_key !== $meta_key) {
            $clause[] = [
                'key' => $translated_key,
                'value' => $value,
                'compare' => '=',
            ];
        }
    }

    return $clause;
}

/** Build taxonomy/meta query parts for a finder filter set. */
function spek_finder_query_parts(array $filters, string $lang, string $exclude_taxonomy = '', string $exclude_meta = ''): array
{
    $tax_query = [];
    $meta_query = [];

    $taxonomy_filters = [
        'product_category',
        'product_application',
        'product_material',
        'product_series',
    ];

    foreach ($taxonomy_filters as $taxonomy) {
        if ($taxonomy === $exclude_taxonomy || empty($filters[$taxonomy])) {
            continue;
        }

        $selected = (string) $filters[$taxonomy];

        // Finder Step 1 uses the same curated public groups as the homepage/archive.
        // "diafora" in particular is a virtual group that spans all non-primary
        // product categories, so treating it as one taxonomy slug returns the wrong set.
        if (
            $taxonomy === 'product_category'
            && function_exists('spek_home_category_group_definitions')
            && function_exists('spek_product_category_group_term_ids')
        ) {
            $definitions = spek_home_category_group_definitions();

            if (isset($definitions[$selected])) {
                $group_term_ids = spek_product_category_group_term_ids([$selected]);

                $tax_query[] = [
                    'taxonomy' => 'product_category',
                    'field' => 'term_id',
                    'terms' => $group_term_ids ?: [0],
                    'operator' => 'IN',
                ];
                continue;
            }
        }

        $meta_option = spek_finder_decode_meta_option($selected);

        // The current database contains both taxonomy-backed and imported meta-backed
        // technical values. Meta-backed UI choices must filter against their real field.
        if (!empty($meta_option)) {
            if ($exclude_meta !== $meta_option['key']) {
                $meta_query[] = spek_finder_meta_option_query_clause(
                    (string) $meta_option['key'],
                    (string) $meta_option['value'],
                    $lang
                );
            }
            continue;
        }

        $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field' => 'slug',
            'terms' => sanitize_title($selected),
        ];
    }

    if (!empty($tax_query)) {
        $tax_query['relation'] = 'AND';
    }

    if ($exclude_meta !== 'product_installation_type' && !empty($filters['product_installation_type'])) {
        $meta_query[] = [
            'key' => 'product_installation_type',
            'value' => (string) $filters['product_installation_type'],
            'compare' => '=',
        ];
    }

    $seat_cover = !empty($filters['product_category']) && $filters['product_category'] === 'kalymmata-lekanis';
    $known_dimensions = $seat_cover && (($filters['cover_dimensions_known'] ?? '') === 'yes');

    if ($known_dimensions) {
        if ($exclude_meta !== 'cover_length' && is_numeric($filters['cover_length'] ?? '')) {
            $length = (float) $filters['cover_length'];
            if ($length > 0) {
                $meta_query[] = spek_finder_dimension_meta_clause('product_cover_length', $length);
            }
        }

        if ($exclude_meta !== 'cover_width' && is_numeric($filters['cover_width'] ?? '')) {
            $width = (float) $filters['cover_width'];
            if ($width > 0) {
                $meta_query[] = spek_finder_dimension_meta_clause('product_cover_width', $width);
            }
        }
    }

    if ($lang === 'en') {
        $meta_query[] = [
            'key' => '_spek_title_en',
            'value' => '',
            'compare' => '!=',
        ];
    }

    if (!empty($meta_query)) {
        $meta_query['relation'] = 'AND';
    }

    return [
        'tax_query' => $tax_query,
        'meta_query' => $meta_query,
    ];
}

/** Return IDs of products that match the supplied finder filters. */
function spek_finder_product_ids(array $filters, string $lang, string $exclude_taxonomy = '', string $exclude_meta = ''): array
{
    $parts = spek_finder_query_parts($filters, $lang, $exclude_taxonomy, $exclude_meta);
    $args = [
        'post_type' => 'spek_product',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'suppress_filters' => false,
    ];

    if (!empty($parts['tax_query'])) {
        $args['tax_query'] = $parts['tax_query'];
    }
    if (!empty($parts['meta_query'])) {
        $args['meta_query'] = $parts['meta_query'];
    }

    $query = new WP_Query($args);
    return array_values(array_map('intval', $query->posts));
}

/** Resolve a public taxonomy label for the language used by the current finder AJAX request. */
function spek_finder_term_label(WP_Term $term, string $lang): string
{
    if ($lang !== 'en') {
        return (string) $term->name;
    }

    $translated = get_term_meta($term->term_id, '_spek_name_en', true);
    if (is_string($translated) && trim($translated) !== '') {
        return trim($translated);
    }

    if (function_exists('spek_import_default_taxonomy_translation')) {
        $mapped = spek_import_default_taxonomy_translation((string) $term->name, (string) $term->taxonomy);
        if ($mapped !== '') {
            return $mapped;
        }
    }

    if (function_exists('spek_i18n_translate_string')) {
        return spek_i18n_translate_string((string) $term->name, (string) $term->name);
    }

    return (string) $term->name;
}

/** Build counted taxonomy options from a candidate product set. */
function spek_finder_term_options(array $product_ids, string $taxonomy, string $lang): array
{
    if (empty($product_ids)) {
        return [];
    }

    $rows = wp_get_object_terms($product_ids, $taxonomy, ['fields' => 'all_with_object_id']);
    if (is_wp_error($rows) || empty($rows)) {
        return [];
    }

    $counts = [];
    foreach ($rows as $row) {
        if (!$row instanceof WP_Term) {
            continue;
        }
        $term_id = (int) $row->term_id;
        $counts[$term_id] = ($counts[$term_id] ?? 0) + 1;
    }

    $options = [];
    foreach ($counts as $term_id => $count) {
        $term = get_term($term_id, $taxonomy);
        if (!$term instanceof WP_Term || is_wp_error($term)) {
            continue;
        }

        $options[] = [
            'value' => (string) $term->slug,
            'label' => spek_finder_term_label($term, $lang),
            'count' => (int) $count,
        ];
    }

    usort($options, static function (array $a, array $b): int {
        return strnatcasecmp((string) $a['label'], (string) $b['label']);
    });

    return $options;
}

/**
 * Build options directly from the real product meta values.
 * This is required for the existing SPEK catalogue because many imported products
 * have application/material/series in post meta even when the matching taxonomy
 * was never assigned.
 */
function spek_finder_meta_options(array $product_ids, array $meta_keys, string $lang): array
{
    if (empty($product_ids) || empty($meta_keys)) {
        return [];
    }

    $values = [];

    foreach ($product_ids as $post_id) {
        foreach ($meta_keys as $meta_key) {
            $label = get_post_meta((int) $post_id, (string) $meta_key, true);
            $label = is_scalar($label) ? trim(wp_strip_all_tags((string) $label)) : '';

            if ($label === '') {
                continue;
            }

            $normalized = spek_finder_normalize_option_label($label);
            if ($normalized === '') {
                continue;
            }

            if (!isset($values[$normalized])) {
                $values[$normalized] = [
                    'value' => spek_finder_encode_meta_option((string) $meta_key, $label),
                    'label' => $label,
                    'count' => 0,
                ];
            }

            $values[$normalized]['count']++;

            // The first populated key is the preferred source for this product.
            // e.g. _spek_product_application before the longer application text.
            break;
        }
    }

    $options = array_values($values);
    usort($options, static function (array $a, array $b): int {
        return strnatcasecmp((string) $a['label'], (string) $b['label']);
    });

    return $options;
}

/** Merge taxonomy and meta fallbacks without showing the same human label twice. */
function spek_finder_merge_options(array $primary, array $fallback): array
{
    $merged = [];
    $seen = [];

    foreach (array_merge($primary, $fallback) as $option) {
        $label = isset($option['label']) ? (string) $option['label'] : '';
        $normalized = spek_finder_normalize_option_label($label);

        if ($normalized === '' || isset($seen[$normalized])) {
            continue;
        }

        $seen[$normalized] = true;
        $merged[] = $option;
    }

    return $merged;
}

/** Build counted installation-type options from the actual matching products. */
function spek_finder_installation_options(array $product_ids, string $lang): array
{
    if (empty($product_ids)) {
        return [];
    }

    $values = [];
    foreach ($product_ids as $post_id) {
        $raw = get_metadata_raw('post', (int) $post_id, 'product_installation_type', true);
        $raw = is_scalar($raw) ? trim((string) $raw) : '';
        if ($raw === '') {
            continue;
        }

        $label = $lang === 'en' ? trim((string) get_post_meta((int) $post_id, 'product_installation_type', true)) : $raw;
        if ($label === '') {
            continue;
        }

        if (!isset($values[$raw])) {
            $values[$raw] = [
                'value' => $raw,
                'label' => $label,
                'count' => 0,
            ];
        }
        $values[$raw]['count']++;
    }

    $options = array_values($values);
    usort($options, static function (array $a, array $b): int {
        return strnatcasecmp((string) $a['label'], (string) $b['label']);
    });

    return $options;
}

/** AJAX: return only facet values that exist on products matching the current path. */
function spek_ajax_product_finder_facets(): void
{
    check_ajax_referer('spek_finder_nonce', 'nonce');

    $GLOBALS['spek_frontend_ajax'] = true;
    $lang = spek_finder_request_language();
    $filters = spek_finder_request_filters();

    if (empty($filters['product_category'])) {
        wp_send_json_success([
            'applications' => [],
            'materials' => [],
            'series' => [],
            'installationTypes' => [],
            'count' => 0,
        ]);
    }

    $application_filters = $filters;
    $application_filters['product_application'] = '';
    $application_filters['product_material'] = '';
    $application_filters['product_series'] = '';
    $application_filters['product_installation_type'] = '';
    $application_filters['cover_dimensions_known'] = '';
    $application_filters['cover_length'] = '';
    $application_filters['cover_width'] = '';

    $is_seat_cover = $filters['product_category'] === 'kalymmata-lekanis';
    $applications = [];

    if (!$is_seat_cover) {
        $application_ids = spek_finder_product_ids($application_filters, $lang, 'product_application');

        // Application choices must come exclusively from the canonical
        // product_application taxonomy shown in wp-admin. Do not synthesize
        // extra choices from free-text product meta such as
        // "Μπαλκόνι / ταράτσα".
        $applications = spek_finder_term_options(
            $application_ids,
            'product_application',
            $lang
        );
    }

    // Faceted options: each facet is calculated with the other selected facets applied,
    // so every option shown can still lead to at least one real product.
    $material_ids = spek_finder_product_ids($filters, $lang, 'product_material', 'product_material');
    $series_ids = spek_finder_product_ids($filters, $lang, 'product_series', '_spek_product_series');
    $installation_ids = spek_finder_product_ids($filters, $lang, '', 'product_installation_type');
    $matching_ids = spek_finder_product_ids($filters, $lang);

    $materials = spek_finder_merge_options(
        spek_finder_term_options($material_ids, 'product_material', $lang),
        spek_finder_meta_options($material_ids, ['product_material'], $lang)
    );

    $series = spek_finder_merge_options(
        spek_finder_term_options($series_ids, 'product_series', $lang),
        spek_finder_meta_options($series_ids, ['_spek_product_series'], $lang)
    );

    wp_send_json_success([
        'applications' => $applications,
        'materials' => $materials,
        'series' => $series,
        'installationTypes' => spek_finder_installation_options($installation_ids, $lang),
        'count' => count($matching_ids),
    ]);
}

add_action('wp_ajax_spek_product_finder_facets', 'spek_ajax_product_finder_facets');
add_action('wp_ajax_nopriv_spek_product_finder_facets', 'spek_ajax_product_finder_facets');

/**
 * AJAX product finder.
 */
function spek_ajax_product_finder(): void
{
    check_ajax_referer('spek_finder_nonce', 'nonce');

    $GLOBALS['spek_frontend_ajax'] = true;
    $lang = spek_finder_request_language();
    $filters = spek_finder_request_filters();
    $parts = spek_finder_query_parts($filters, $lang);

    $search = '';
    if (!empty($_POST['product_search'])) {
        $search = sanitize_text_field(wp_unslash($_POST['product_search']));
    }

    $args = [
        'post_type' => 'spek_product',
        'post_status' => 'publish',
        'posts_per_page' => 12,
        's' => '',
        'spek_product_search' => $search,
        'suppress_filters' => false,
    ];

    if (!empty($parts['tax_query'])) {
        $args['tax_query'] = $parts['tax_query'];
    }
    if (!empty($parts['meta_query'])) {
        $args['meta_query'] = $parts['meta_query'];
    }

    $products = new WP_Query($args);

    ob_start();

    if ($products->have_posts()) :
        while ($products->have_posts()) :
            $products->the_post();
            get_template_part('template-parts/products/product-card');
        endwhile;
    else :
        ?>
        <div class="empty-state spek-finder-no-results">
            <h2><?php esc_html_e('Δεν βρέθηκαν προϊόντα', 'spek-theme'); ?></h2>
            <p><?php esc_html_e('Δοκιμάστε λιγότερα φίλτρα ή διαφορετική λέξη-κλειδί.', 'spek-theme'); ?></p>
            <a class="button button-primary" href="<?php echo esc_url(get_post_type_archive_link('spek_product')); ?>">
                <?php esc_html_e('Δείτε όλα τα προϊόντα', 'spek-theme'); ?>
            </a>
        </div>
        <?php
    endif;

    wp_reset_postdata();

    $html = ob_get_clean();
    $count = (int) $products->found_posts;

    wp_send_json_success([
        'html' => $html,
        'count' => $count,
        'countText' => sprintf(_n('%d προϊόν ταιριάζει με τα κριτήρια.', '%d προϊόντα ταιριάζουν με τα κριτήρια.', $count, 'spek-theme'), $count),
    ]);
}

add_action('wp_ajax_spek_product_finder', 'spek_ajax_product_finder');
add_action('wp_ajax_nopriv_spek_product_finder', 'spek_ajax_product_finder');
