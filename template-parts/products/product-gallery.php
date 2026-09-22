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
            <?php the_post_thumbnail('spek_product_large'); ?>
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