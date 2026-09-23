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
 * Mark the main Products query with the requested image status.
 *
 * We intentionally do not rely only on the _thumbnail_id meta value: a product
 * can still have stale thumbnail metadata pointing to a deleted attachment.
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

    if (in_array($status, ['missing', 'has_image'], true)) {
        $query->set('spek_image_status', $status);
    }
}
add_action('pre_get_posts', 'spek_product_admin_apply_image_filter');

/**
 * Filter against a real attachment, not just the presence of _thumbnail_id.
 *
 * "Χωρίς εικόνα" therefore also catches products whose old featured attachment
 * was deleted from the Media Library.
 */
function spek_product_admin_image_filter_where(string $where, WP_Query $query): string
{
    if (
        !is_admin()
        || !$query->is_main_query()
        || $query->get('post_type') !== 'spek_product'
    ) {
        return $where;
    }

    $status = (string) $query->get('spek_image_status');

    if (!in_array($status, ['missing', 'has_image'], true)) {
        return $where;
    }

    global $wpdb;

    $exists_sql = "
        SELECT 1
        FROM {$wpdb->postmeta} AS spek_thumb_meta
        INNER JOIN {$wpdb->posts} AS spek_thumb_attachment
            ON spek_thumb_attachment.ID = CAST(spek_thumb_meta.meta_value AS UNSIGNED)
            AND spek_thumb_attachment.post_type = 'attachment'
            AND spek_thumb_attachment.post_status = 'inherit'
        WHERE spek_thumb_meta.post_id = {$wpdb->posts}.ID
            AND spek_thumb_meta.meta_key = '_thumbnail_id'
            AND CAST(spek_thumb_meta.meta_value AS UNSIGNED) > 0
    ";

    if ($status === 'missing') {
        $where .= " AND NOT EXISTS ({$exists_sql})";
    } else {
        $where .= " AND EXISTS ({$exists_sql})";
    }

    return $where;
}
add_filter('posts_where', 'spek_product_admin_image_filter_where', 20, 2);
