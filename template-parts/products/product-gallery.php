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
?>

<div class="product-gallery">
    <div class="product-gallery__main">
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
            >
        <?php else : ?>
            <div class="product-gallery__placeholder">
                <span>SPEK</span>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($gallery) && is_array($gallery)) : ?>
        <div class="product-gallery__thumbs">
            <?php foreach ($gallery as $image) : ?>
                <?php
                $image_id = is_array($image) && isset($image['ID']) ? $image['ID'] : $image;
                ?>
                <div class="product-gallery__thumb">
                    <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>