<?php
/**
 * Product admin list enhancements.
 *
 * Adds an image-status filter to Products → All Products so administrators can
 * quickly find products that still need a featured image.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Render the product image-status dropdown in wp-admin.
 */
function spek_product_admin_image_filter(string $post_type): void
{
    if ($post_type !== 'spek_product') {
        return;
    }

    $selected = isset($_GET['spek_image_status'])
        ? sanitize_key(wp_unslash((string) $_GET['spek_image_status']))
        : '';

    ?>
    <label class="screen-reader-text" for="spek_image_status">
        <?php esc_html_e('Φίλτρο κατάστασης εικόνας προϊόντος', 'spek-theme'); ?>
    </label>

    <select name="spek_image_status" id="spek_image_status">
        <option value="">
            <?php esc_html_e('Όλες οι εικόνες', 'spek-theme'); ?>
        </option>
        <option value="missing" <?php selected($selected, 'missing'); ?>>
            <?php esc_html_e('Χωρίς εικόνα', 'spek-theme'); ?>
        </option>
        <option value="has_image" <?php selected($selected, 'has_image'); ?>>
            <?php esc_html_e('Με εικόνα', 'spek-theme'); ?>
        </option>
    </select>
    <?php
}
add_action('restrict_manage_posts', 'spek_product_admin_image_filter', 20, 1);

/**
 * Apply the selected image-status filter to the main Products admin query.
 */
function spek_product_admin_apply_image_filter(WP_Query $query): void
{
    if (
        !is_admin()
        || !$query->is_main_query()
        || $query->get('post_type') !== 'spek_product'
    ) {
        return;
    }

    $status = isset($_GET['spek_image_status'])
        ? sanitize_key(wp_unslash((string) $_GET['spek_image_status']))
        : '';

    if (!in_array($status, ['missing', 'has_image'], true)) {
        return;
    }

    $existing_meta_query = $query->get('meta_query');
    $existing_meta_query = is_array($existing_meta_query)
        ? $existing_meta_query
        : [];

    if ($status === 'missing') {
        $image_meta_query = [
            'relation' => 'OR',
            [
                'key' => '_thumbnail_id',
                'compare' => 'NOT EXISTS',
            ],
            [
                'key' => '_thumbnail_id',
                'value' => '',
                'compare' => '=',
            ],
            [
                'key' => '_thumbnail_id',
                'value' => '0',
                'compare' => '=',
            ],
        ];
    } else {
        $image_meta_query = [
            'relation' => 'AND',
            [
                'key' => '_thumbnail_id',
                'compare' => 'EXISTS',
            ],
            [
                'key' => '_thumbnail_id',
                'value' => '',
                'compare' => '!=',
            ],
            [
                'key' => '_thumbnail_id',
                'value' => '0',
                'compare' => '!=',
            ],
        ];
    }

    if ($existing_meta_query) {
        $query->set('meta_query', [
            'relation' => 'AND',
            $existing_meta_query,
            $image_meta_query,
        ]);
        return;
    }

    $query->set('meta_query', $image_meta_query);
}
add_action('pre_get_posts', 'spek_product_admin_apply_image_filter');

/**
 * Keep the custom image filter when changing pagination/order/search URLs.
 */
function spek_product_admin_preserve_image_filter(array $query_args): array
{
    if (
        !is_admin()
        || !isset($_GET['post_type'])
        || sanitize_key(wp_unslash((string) $_GET['post_type'])) !== 'spek_product'
        || empty($_GET['spek_image_status'])
    ) {
        return $query_args;
    }

    $status = sanitize_key(wp_unslash((string) $_GET['spek_image_status']));

    if (in_array($status, ['missing', 'has_image'], true)) {
        $query_args['spek_image_status'] = $status;
    }

    return $query_args;
}
add_filter('removable_query_args', static function (array $args): array {
    return array_values(array_diff($args, ['spek_image_status']));
});
