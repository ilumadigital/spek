<?php
/**
 * Product archive query handling.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Public product filter query-string names.
 *
 * Do not use the taxonomy names themselves as GET parameters. WordPress treats
 * registered taxonomy query vars (e.g. product_application) as native taxonomy
 * requests before the archive query is built. Greek slugs are percent-encoded,
 * so a normal browser form submission encodes the percent signs again (% -> %25)
 * and WordPress can incorrectly resolve the request as a non-existent taxonomy
 * archive, resulting in a 404.
 */
function spek_product_filter_params(): array
{
    return [
        'product_category' => 'filter_category',
        'product_application' => 'filter_application',
        'product_material' => 'filter_material',
        'product_series' => 'filter_series',
    ];
}

/**
 * Resolve one product filter to a validated term ID.
 *
 * New filter URLs use numeric term IDs. Legacy taxonomy-name parameters are
 * still accepted so old bookmarked/filter URLs do not break.
 */
function spek_product_filter_term_id(string $taxonomy): int
{
    $params = spek_product_filter_params();
    if (!isset($params[$taxonomy]) || !taxonomy_exists($taxonomy)) {
        return 0;
    }

    $param = $params[$taxonomy];
    if (isset($_GET[$param]) && $_GET[$param] !== '') {
        $term_id = absint(wp_unslash($_GET[$param]));
        if ($term_id > 0) {
            $term = get_term($term_id, $taxonomy);
            if ($term instanceof WP_Term && !is_wp_error($term)) {
                return $term_id;
            }
        }
    }

    // Backwards compatibility for older URLs such as ?product_application=...
    if (!isset($_GET[$taxonomy]) || $_GET[$taxonomy] === '') {
        return 0;
    }

    $legacy = sanitize_text_field(wp_unslash((string) $_GET[$taxonomy]));
    if ($legacy === '') {
        return 0;
    }

    if (ctype_digit($legacy)) {
        $term = get_term((int) $legacy, $taxonomy);
        return ($term instanceof WP_Term && !is_wp_error($term)) ? (int) $term->term_id : 0;
    }

    // WordPress can store non-Latin slugs as percent-encoded strings. Try the
    // literal value first, then a decoded/name-normalised representation.
    $candidates = array_values(array_unique(array_filter([
        $legacy,
        rawurldecode($legacy),
        sanitize_title(rawurldecode($legacy)),
    ])));

    foreach ($candidates as $candidate) {
        $term = get_term_by('slug', $candidate, $taxonomy);
        if ($term instanceof WP_Term) {
            return (int) $term->term_id;
        }
    }

    $decoded = rawurldecode($legacy);
    $term = get_term_by('name', $decoded, $taxonomy);
    return $term instanceof WP_Term ? (int) $term->term_id : 0;
}


/**
 * Homepage category groups.
 *
 * The homepage intentionally presents a curated eight-card structure that can
 * combine several native taxonomy terms under one public group. "Διάφορα"
 * automatically includes every product category that is not one of the seven
 * primary groups.
 */
function spek_home_category_group_definitions(): array
{
    return [
        'mixanismoi' => [
            'label' => __('Μηχανισμοί', 'spek-theme'),
            'slugs' => ['mixanismoi', 'mechanisms'],
            'names' => ['Μηχανισμοί', 'Mechanisms'],
        ],
        'floter' => [
            'label' => __('Φλοτέρ', 'spek-theme'),
            'slugs' => ['floter', 'float-valves', 'floater'],
            'names' => ['Φλοτέρ', 'Float valves'],
        ],
        'sifonia' => [
            'label' => __('Σιφόνια', 'spek-theme'),
            'slugs' => ['sifonia', 'siphons', 'traps'],
            'names' => ['Σιφόνια', 'Siphons', 'Traps'],
        ],
        'kalymmata-lekanis' => [
            'label' => __('Καλύμματα λεκάνης', 'spek-theme'),
            'slugs' => ['kalymmata-lekanis', 'kalimmata-lekanis', 'kalymmata', 'kalimmata', 'toilet-seats'],
            'names' => ['Καλύμματα λεκάνης', 'Καλύμματα Λεκάνης', 'Καπάκια λεκάνης', 'Καπάκια Λεκάνης', 'Toilet seats'],
        ],
        'kazanakia' => [
            'label' => __('Καζανάκια', 'spek-theme'),
            'slugs' => ['kazanakia', 'cisterns'],
            'names' => ['Καζανάκια', 'Cisterns'],
        ],
        'banio' => [
            'label' => __('Μπάνιο', 'spek-theme'),
            'slugs' => ['banio', 'mpanio', 'bathroom'],
            'names' => ['Μπάνιο', 'Bathroom'],
        ],
        'lastixa' => [
            'label' => __('Λάστιχα', 'spek-theme'),
            'slugs' => ['lastixa', 'rubber-parts', 'seals'],
            'names' => ['Λάστιχα', 'Rubber parts', 'Seals'],
        ],
        'diafora' => [
            'label' => __('Διάφορα', 'spek-theme'),
            'slugs' => [],
            'names' => [],
        ],
    ];
}

function spek_home_category_group_term_ids(string $group): array
{
    $definitions = spek_home_category_group_definitions();
    if (!isset($definitions[$group])) {
        return [];
    }

    $ids = [];
    foreach ($definitions[$group]['slugs'] as $slug) {
        $term = get_term_by('slug', $slug, 'product_category');
        if ($term instanceof WP_Term) {
            $ids[] = (int) $term->term_id;
        }
    }

    foreach ($definitions[$group]['names'] as $name) {
        $term = get_term_by('name', $name, 'product_category');
        if ($term instanceof WP_Term) {
            $ids[] = (int) $term->term_id;
        }
    }

    return array_values(array_unique(array_filter($ids)));
}

function spek_home_category_group_label(string $group): string
{
    $definitions = spek_home_category_group_definitions();
    return isset($definitions[$group]) ? (string) $definitions[$group]['label'] : '';
}

/**
 * Prevent legacy taxonomy filter GET parameters from hijacking /products/ as a
 * native taxonomy request. The actual filtering is applied later by
 * pre_get_posts using validated term IDs.
 */
function spek_normalize_legacy_product_filter_request(array $query_vars): array
{
    if (is_admin()) {
        return $query_vars;
    }

    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
    $path = (string) wp_parse_url($uri, PHP_URL_PATH);
    $path = '/' . ltrim($path, '/');

    // Support both /products/ and /en/products/ (plus installations in a subdir).
    if (!preg_match('#/(?:en/)?products(?:/page/\d+)?/?$#i', $path)) {
        return $query_vars;
    }

    foreach (array_keys(spek_product_filter_params()) as $taxonomy) {
        if (isset($_GET[$taxonomy])) {
            unset($query_vars[$taxonomy]);
        }
    }

    return $query_vars;
}
add_filter('request', 'spek_normalize_legacy_product_filter_request', 5);

/**
 * Apply product archive filters from safe custom URL query vars.
 */
function spek_filter_product_archive_query($query): void
{
    if (is_admin() || !$query->is_main_query()) {
        return;
    }

    if (!$query->is_post_type_archive('spek_product') && !$query->is_tax([
        'product_category',
        'product_application',
        'product_material',
        'product_series',
    ])) {
        return;
    }

    $tax_query = (array) $query->get('tax_query');

    $home_group = isset($_GET['home_category_group'])
        ? sanitize_key(wp_unslash((string) $_GET['home_category_group']))
        : '';

    if ($home_group !== '' && function_exists('spek_home_category_group_definitions')) {
        $definitions = spek_home_category_group_definitions();

        if (isset($definitions[$home_group])) {
            if ($home_group === 'diafora') {
                $primary_groups = ['mixanismoi', 'floter', 'sifonia', 'kalymmata-lekanis', 'kazanakia', 'banio', 'lastixa'];
                $primary_term_ids = [];

                foreach ($primary_groups as $primary_group) {
                    $primary_term_ids = array_merge(
                        $primary_term_ids,
                        spek_home_category_group_term_ids($primary_group)
                    );
                }

                $primary_term_ids = array_values(array_unique(array_filter(array_map('intval', $primary_term_ids))));
                if ($primary_term_ids) {
                    $tax_query[] = [
                        'taxonomy' => 'product_category',
                        'field' => 'term_id',
                        'terms' => $primary_term_ids,
                        'operator' => 'NOT IN',
                    ];
                }
            } else {
                $group_term_ids = spek_home_category_group_term_ids($home_group);
                $tax_query[] = [
                    'taxonomy' => 'product_category',
                    'field' => 'term_id',
                    'terms' => $group_term_ids ?: [0],
                    'operator' => 'IN',
                ];
            }
        }
    }

    foreach (array_keys(spek_product_filter_params()) as $taxonomy) {
        $term_id = spek_product_filter_term_id($taxonomy);
        if ($term_id > 0) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => [$term_id],
            ];
        }
    }

    if (!empty($tax_query)) {
        $tax_query['relation'] = 'AND';
        $query->set('tax_query', $tax_query);
    }

    if (!empty($_GET['product_search'])) {
        $product_search = sanitize_text_field(wp_unslash($_GET['product_search']));
        $query->set('spek_product_search', $product_search);
        // Do not use WordPress' default `s` search here because it cannot search the SKU meta field.
        $query->set('s', '');
    }

    // English archives expose only products that have an explicit English title.
    if (function_exists('spek_is_english') && spek_is_english()) {
        $meta_query = (array) $query->get('meta_query');
        $meta_query[] = ['key' => '_spek_title_en', 'value' => '', 'compare' => '!='];
        $query->set('meta_query', $meta_query);
    }

    $query->set('posts_per_page', 12);
}

add_action('pre_get_posts', 'spek_filter_product_archive_query');

/**
 * Create default product terms on theme activation.
 */
function spek_create_default_product_terms(): void
{
    $product_categories = [
        'Μηχανισμοί' => 'mixanismoi',
        'Φλοτέρ' => 'floter',
        'Σιφόνια' => 'sifonia',
        'Λάστιχα' => 'lastixa',
        'Καζανάκια' => 'kazanakia',
        'Nemo' => 'nemo',
        'Lamaplast' => 'lamaplast',
        'Διάφορα' => 'diafora',
    ];

    foreach ($product_categories as $name => $slug) {
        if (!term_exists($slug, 'product_category')) {
            wp_insert_term($name, 'product_category', [
                'slug' => $slug,
            ]);
        }
    }

    $applications = [
        'WC' => 'wc',
        'Νιπτήρας' => 'niptiras',
        'Μπάνιο' => 'banio',
        'Κουζίνα' => 'kouzina',
        'Επαγγελματικός χώρος' => 'epaggelmatikos-xoros',
        'Αντικατάσταση' => 'antikatastasi',
        'Νέα εγκατάσταση' => 'nea-egkatastasi',
    ];

    foreach ($applications as $name => $slug) {
        if (!term_exists($slug, 'product_application')) {
            wp_insert_term($name, 'product_application', [
                'slug' => $slug,
            ]);
        }
    }

    $materials = [
        'Πλαστικό' => 'plastiko',
        'Μεταλλικό' => 'metalliko',
        'Ελαστικό' => 'elastiko',
        'Μικτό' => 'mikto',
    ];

    foreach ($materials as $name => $slug) {
        if (!term_exists($slug, 'product_material')) {
            wp_insert_term($name, 'product_material', [
                'slug' => $slug,
            ]);
        }
    }

    $series = [
        'SPEK' => 'spek',
        'Nemo' => 'nemo',
        'Lamaplast' => 'lamaplast',
    ];

    foreach ($series as $name => $slug) {
        if (!term_exists($slug, 'product_series')) {
            wp_insert_term($name, 'product_series', [
                'slug' => $slug,
            ]);
        }
    }
}

add_action('after_switch_theme', 'spek_create_default_product_terms');

/**
 * Join the product code meta only when the custom product search is active.
 */
function spek_product_search_join(string $join, $query): string
{
    global $wpdb;

    if ((is_admin() && empty($GLOBALS['spek_frontend_ajax'])) || !$query->get('spek_product_search')) {
        return $join;
    }

    if (strpos($join, 'spek_product_code_meta') === false) {
        $join .= " LEFT JOIN {$wpdb->postmeta} AS spek_product_code_meta"
            . " ON ({$wpdb->posts}.ID = spek_product_code_meta.post_id"
            . " AND spek_product_code_meta.meta_key = 'product_code') ";
    }
    if (function_exists('spek_is_english') && spek_is_english()) {
        if (strpos($join, 'spek_product_title_en_meta') === false) {
            $join .= " LEFT JOIN {$wpdb->postmeta} AS spek_product_title_en_meta"
                . " ON ({$wpdb->posts}.ID = spek_product_title_en_meta.post_id"
                . " AND spek_product_title_en_meta.meta_key = '_spek_title_en') ";
        }
        if (strpos($join, 'spek_product_content_en_meta') === false) {
            $join .= " LEFT JOIN {$wpdb->postmeta} AS spek_product_content_en_meta"
                . " ON ({$wpdb->posts}.ID = spek_product_content_en_meta.post_id"
                . " AND spek_product_content_en_meta.meta_key = '_spek_content_en') ";
        }
    }

    return $join;
}
add_filter('posts_join', 'spek_product_search_join', 10, 2);

/**
 * Search product title/content/excerpt and exact or partial SKU from the same archive search box.
 */
function spek_product_search_where(string $search, $query): string
{
    global $wpdb;

    $term = (string) $query->get('spek_product_search');
    if ((is_admin() && empty($GLOBALS['spek_frontend_ajax'])) || $term === '') {
        return $search;
    }

    $like = '%' . $wpdb->esc_like($term) . '%';

    $password_sql = is_user_logged_in() ? '' : " AND {$wpdb->posts}.post_password = '' ";
    if (function_exists('spek_is_english') && spek_is_english()) {
        return $password_sql . $wpdb->prepare(
            " AND (spek_product_title_en_meta.meta_value LIKE %s OR spek_product_content_en_meta.meta_value LIKE %s OR spek_product_code_meta.meta_value LIKE %s) ",
            $like, $like, $like
        );
    }
    return $password_sql . $wpdb->prepare(
        " AND (\n"
        . "{$wpdb->posts}.post_title LIKE %s\n"
        . " OR {$wpdb->posts}.post_excerpt LIKE %s\n"
        . " OR {$wpdb->posts}.post_content LIKE %s\n"
        . " OR spek_product_code_meta.meta_value LIKE %s\n"
        . ") ",
        $like, $like, $like, $like
    );
}
add_filter('posts_search', 'spek_product_search_where', 10, 2);

/** Prevent duplicate products when the SKU meta join is active. */
function spek_product_search_distinct(string $distinct, $query): string
{
    if ((!is_admin() || !empty($GLOBALS['spek_frontend_ajax'])) && $query->get('spek_product_search')) {
        return 'DISTINCT';
    }

    return $distinct;
}
add_filter('posts_distinct', 'spek_product_search_distinct', 10, 2);
