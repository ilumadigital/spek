<?php
/**
 * Product card template.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$product_id = get_the_ID();
$product_code = spek_get_product_field('product_code', $product_id);
$product_code = !spek_product_value_is_empty($product_code)
    ? spek_product_value_to_text($product_code)
    : '';
$product_code_label = function_exists('spek_is_english') && spek_is_english()
    ? 'Product code:'
    : 'Κωδ. Προϊόντος:';

$product_subtitle = get_post_meta($product_id, '_spek_product_subtitle', true) ?: spek_get_product_field('product_short_description', $product_id);
$product_application = get_post_meta($product_id, '_spek_product_application', true);
$product_series = get_post_meta($product_id, '_spek_product_series', true);
$show_finder_dimensions = !empty($GLOBALS['spek_finder_show_dimensions']);
$product_dimensions = ($show_finder_dimensions && function_exists('spek_finder_product_dimensions_label'))
    ? spek_finder_product_dimensions_label($product_id)
    : '';
?>

<article class="spek-product-card">
    <a class="spek-product-card__image" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <?php
            $product_image_id = get_post_thumbnail_id($product_id);
            $product_image_url = wp_get_attachment_image_url($product_image_id, 'full');
            $product_image_alt = get_post_meta($product_image_id, '_wp_attachment_image_alt', true);
            $product_image_alt = $product_image_alt ?: get_the_title($product_id);
            ?>
            <img
                src="<?php echo esc_url($product_image_url); ?>"
                alt="<?php echo esc_attr($product_image_alt); ?>"
                loading="lazy"
                decoding="async"
            >
        <?php else : ?>
            <div class="spek-product-card__placeholder" aria-hidden="true">
                <img
                    class="spek-product-card__placeholder-logo"
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                    alt=""
                    loading="lazy"
                    decoding="async"
                >
            </div>
        <?php endif; ?>
    </a>

    <div class="spek-product-card__content">
        <?php if ($product_series) : ?>
            <div class="spek-product-card__eyebrow">
                <?php echo esc_html($product_series); ?>
            </div>
        <?php else : ?>
            <div class="spek-product-card__eyebrow">
                SPEK
            </div>
        <?php endif; ?>

        <h2 class="spek-product-card__title">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </h2>

        <?php if ($product_subtitle) : ?>
            <p class="spek-product-card__text">
                <?php echo esc_html($product_subtitle); ?>
            </p>
        <?php elseif (has_excerpt()) : ?>
            <p class="spek-product-card__text">
                <?php echo esc_html(get_the_excerpt()); ?>
            </p>
        <?php endif; ?>

        <?php if ($product_application) : ?>
            <div class="spek-product-card__meta">
                <?php echo esc_html($product_application); ?>
            </div>
        <?php endif; ?>

        <?php if ($product_dimensions !== '') : ?>
            <div class="spek-product-card__finder-dimensions">
                <span><?php esc_html_e('Διαστάσεις', 'spek-theme'); ?></span>
                <strong><?php echo esc_html($product_dimensions); ?></strong>
            </div>
        <?php endif; ?>

        <div class="spek-product-card__footer">
            <a class="spek-product-card__button" href="<?php the_permalink(); ?>">
                <?php esc_html_e('Προβολή προϊόντος', 'spek-theme'); ?>
            </a>
            <?php if ($product_code !== '') : ?>
                <a class="spek-product-card__code-link" href="<?php echo esc_url(get_permalink($product_id)); ?>">
                    <span><?php echo esc_html($product_code_label); ?></span>
                    <strong><?php echo esc_html($product_code); ?></strong>
                </a>
            <?php endif; ?>
        </div>
    </div>
</article>