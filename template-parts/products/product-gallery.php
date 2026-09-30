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

$main_image_id = has_post_thumbnail($product_id) ? (int) get_post_thumbnail_id($product_id) : 0;
$main_image_url = $main_image_id ? wp_get_attachment_image_url($main_image_id, 'full') : '';
$main_image_alt = $main_image_id ? get_post_meta($main_image_id, '_wp_attachment_image_alt', true) : '';
$main_image_alt = $main_image_alt ?: get_the_title($product_id);

$gallery_items = [];
$seen_ids = [];

if ($main_image_id && $main_image_url) {
    $gallery_items[] = [
        'id'       => $main_image_id,
        'full_url' => $main_image_url,
        'alt'      => $main_image_alt,
        'featured' => true,
    ];
    $seen_ids[$main_image_id] = true;
}

if (!empty($gallery) && is_array($gallery)) {
    foreach ($gallery as $image) {
        $image_id = is_array($image) && isset($image['ID']) ? (int) $image['ID'] : (int) $image;

        if (!$image_id || isset($seen_ids[$image_id])) {
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
            'featured' => false,
        ];

        $seen_ids[$image_id] = true;
    }
}

$has_multiple_images = count($gallery_items) > 1;
?>

<div class="product-gallery<?php echo $has_multiple_images ? ' product-gallery--has-thumbs' : ''; ?>">
    <div class="product-gallery__main"<?php echo $main_image_url ? ' data-product-zoom' : ''; ?>>
        <?php if ($has_multiple_images) : ?>
            <div class="product-gallery__thumbs" aria-label="<?php esc_attr_e('Εικόνες προϊόντος', 'spek-theme'); ?>">
                <?php foreach ($gallery_items as $index => $gallery_item) : ?>
                    <button
                        type="button"
                        class="product-gallery__thumb<?php echo $index === 0 ? ' is-active' : ''; ?>"
                        data-product-gallery-thumb
                        data-product-gallery-src="<?php echo esc_url($gallery_item['full_url']); ?>"
                        data-product-gallery-alt="<?php echo esc_attr($gallery_item['alt']); ?>"
                        aria-label="<?php echo esc_attr($index === 0 ? __('Βασική εικόνα προϊόντος', 'spek-theme') : __('Προβολή εικόνας προϊόντος', 'spek-theme')); ?>"
                        <?php echo $index === 0 ? 'aria-current="true"' : ''; ?>
                    >
                        <?php echo wp_get_attachment_image($gallery_item['id'], 'thumbnail'); ?>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($main_image_url) : ?>
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
            <div class="product-gallery__placeholder" aria-hidden="true">
                <img
                    class="product-gallery__placeholder-logo"
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                    alt=""
                    loading="eager"
                    decoding="async"
                >
            </div>
        <?php endif; ?>
    </div>
</div>
