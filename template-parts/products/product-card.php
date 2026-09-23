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

$product_subtitle = get_post_meta($product_id, '_spek_product_subtitle', true) ?: spek_get_product_field('product_short_description', $product_id);
$product_application = get_post_meta($product_id, '_spek_product_application', true);
$product_series = get_post_meta($product_id, '_spek_product_series', true);
?>

<article class="spek-product-card">
    <a class="spek-product-card__image" href="<?php the_permalink(); ?>" aria-label="<?php the_title_attribute(); ?>">
        <?php if (has_post_thumbnail()) : ?>
            <?php the_post_thumbnail('full', [
                'loading' => 'lazy',
                'decoding' => 'async',
            ]); ?>
        <?php else : ?>
            <div class="spek-product-card__placeholder">
                SPEK
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

        <a class="spek-product-card__button" href="<?php the_permalink(); ?>">
            <?php esc_html_e('Προβολή προϊόντος', 'spek-theme'); ?>
        </a>
    </div>
</article>