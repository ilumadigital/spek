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
 * Apply product archive filters from URL query vars.
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

    $allowed_tax_filters = [
        'product_category',
        'product_application',
        'product_material',
        'product_series',
    ];

    foreach ($allowed_tax_filters as $taxonomy) {
        if (!empty($_GET[$taxonomy])) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => sanitize_text_field(wp_unslash($_GET[$taxonomy])),
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
