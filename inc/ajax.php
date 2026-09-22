<?php
/**
 * Theme AJAX handlers.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX product finder.
 */
function spek_ajax_product_finder(): void
{
    check_ajax_referer('spek_finder_nonce', 'nonce');

    $GLOBALS['spek_frontend_ajax'] = true;
    $lang = isset($_REQUEST['lang']) ? sanitize_key(wp_unslash((string) $_REQUEST['lang'])) : 'el';
    if (!in_array($lang, ['el', 'en'], true)) { $lang = 'el'; }
    $tax_query = [];

    $tax_filters = [
        'product_category',
        'product_application',
        'product_material',
        'product_series',
    ];

    foreach ($tax_filters as $taxonomy) {
        if (!empty($_POST[$taxonomy])) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => sanitize_text_field(wp_unslash($_POST[$taxonomy])),
            ];
        }
    }

    if (!empty($tax_query)) {
        $tax_query['relation'] = 'AND';
    }

    $meta_query = [];

    if (!empty($_POST['product_installation_type'])) {
        $meta_query[] = [
            'key' => 'product_installation_type',
            'value' => sanitize_text_field(wp_unslash($_POST['product_installation_type'])),
            'compare' => 'LIKE',
        ];
    }

    if ($lang === 'en') {
        $meta_query[] = ['key' => '_spek_title_en', 'value' => '', 'compare' => '!='];
    }

    if (!empty($meta_query)) {
        $meta_query['relation'] = 'AND';
    }

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

    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
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