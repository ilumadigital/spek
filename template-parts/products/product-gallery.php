<?php
/**
 * Product gallery.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$product_id = get_the_ID();
$gallery = spek_get_product_field('product_gallery', $product_id);
$gallery_items = [];

if (!empty($gallery) && is_array($gallery)) {
    foreach ($gallery as $image) {
        $image_id = is_array($image) && isset($image['ID']) ? (int) $image['ID'] : (int) $image;

        if (!$image_id) {
            continue;
        }

        $full_url = wp_get_attachment_image_url($image_id, 'full');

        if (!$full_url) {
            continue;
        }

        $alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        $alt = $alt ?: get_the_title($product_id);

        $gallery_items[] = [
            'id'       => $image_id,
            'full_url' => $full_url,
            'alt'      => $alt,
        ];
    }
}

$has_gallery_items = !empty($gallery_items);
?>

<div class="product-gallery<?php echo $has_gallery_items ? ' product-gallery--has-thumbs' : ''; ?>">
    <?php if ($has_gallery_items) : ?>
        <div class="product-gallery__thumbs" aria-label="<?php esc_attr_e('Επιπλέον εικόνες προϊόντος', 'spek-theme'); ?>">
            <?php foreach ($gallery_items as $gallery_item) : ?>
                <button
                    type="button"
                    class="product-gallery__thumb"
                    data-product-gallery-thumb
                    data-product-gallery-src="<?php echo esc_url($gallery_item['full_url']); ?>"
                    data-product-gallery-alt="<?php echo esc_attr($gallery_item['alt']); ?>"
                    aria-label="<?php esc_attr_e('Προβολή εικόνας προϊόντος', 'spek-theme'); ?>"
                >
                    <?php echo wp_get_attachment_image($gallery_item['id'], 'thumbnail'); ?>
                </button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="product-gallery__main"<?php echo has_post_thumbnail() ? ' data-product-zoom' : ''; ?>>
        <?php if (has_post_thumbnail()) : ?>
            <?php
            $main_image_id = get_post_thumbnail_id($product_id);
            $main_image_url = wp_get_attachment_image_url($main_image_id, 'full');
            $main_image_alt = get_post_meta($main_image_id, '_wp_attachment_image_alt', true);
            $main_image_alt = $main_image_alt ?: get_the_title($product_id);
            ?>
            <img
                src="<?php echo esc_url($main_image_url); ?>"
                alt="<?php echo esc_attr($main_image_alt); ?>"
                loading="eager"
                decoding="async"
                fetchpriority="high"
                data-product-zoom-image
                data-product-gallery-main
            >
            <span class="product-gallery__lens" data-product-zoom-lens aria-hidden="true"></span>
        <?php else : ?>
            <div class="product-gallery__placeholder">
                <span>SPEK</span>
            </div>
        <?php endif; ?>
    </div>
</div>
