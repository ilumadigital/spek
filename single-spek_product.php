<?php
/**
 * Single product template.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$product_id = get_the_ID();
$product_code = function_exists('spek_get_product_field')
    ? spek_get_product_field('product_code', $product_id)
    : get_post_meta($product_id, 'product_code', true);
$product_categories = get_the_terms($product_id, 'product_category');
$products_url = get_post_type_archive_link('spek_product') ?: spek_page_url('products/');
$short_description = function_exists('spek_get_product_field')
    ? spek_get_product_field('product_short_description', $product_id)
    : get_post_meta($product_id, 'product_short_description', true);
?>

<main id="main" class="site-main single-product-page">

    <?php while (have_posts()) : the_post(); ?>

        <section class="single-product-hero">
            <div class="container single-product-hero__grid">

                <?php get_template_part('template-parts/products/product-gallery'); ?>

                <div class="single-product-summary">
                    <?php if (!empty($product_categories) && !is_wp_error($product_categories)) : ?>
                        <div class="product-taxonomy-links product-taxonomy-links--hero" aria-label="<?php esc_attr_e('Κατηγορίες προϊόντος', 'spek-theme'); ?>">
                            <?php foreach ($product_categories as $category) : ?>
                                <a class="product-taxonomy-link" href="<?php echo esc_url(get_term_link($category)); ?>">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h1><?php the_title(); ?></h1>

                    <?php if (!spek_product_value_is_empty($product_code)) : ?>
                        <p class="product-code">
                            <span><?php esc_html_e('SKU:', 'spek-theme'); ?></span>
                            <a class="product-code__link" href="<?php echo esc_url(add_query_arg('product_search', (string) $product_code, $products_url)); ?>">
                                <?php echo esc_html((string) $product_code); ?>
                            </a>
                        </p>
                    <?php endif; ?>

                    <?php if (!spek_product_value_is_empty($short_description)) : ?>
                        <p class="single-product-summary__excerpt">
                            <?php echo esc_html(spek_product_value_to_text($short_description)); ?>
                        </p>
                    <?php endif; ?>

                    <div class="single-product-actions">
                        <a href="#technical-specs" class="button button-primary">
                            <?php esc_html_e('Τεχνικά χαρακτηριστικά', 'spek-theme'); ?>
                        </a>

                        <a href="<?php echo esc_url(spek_page_url('partners/')); ?>" class="button button-secondary">
                            <?php esc_html_e('Πού θα το βρείτε', 'spek-theme'); ?>
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <section class="section product-main-content">
            <div class="container product-main-content__grid">

                <article class="product-description">
                    <span class="eyebrow"><?php esc_html_e('Description', 'spek-theme'); ?></span>
                    <h2><?php esc_html_e('Περιγραφή προϊόντος', 'spek-theme'); ?></h2>

                    <div class="content-area">
                        <?php if (trim((string) get_the_content()) !== '') : ?>
                            <?php the_content(); ?>
                        <?php else : ?>
                            <p><?php esc_html_e('Δεν έχει καταχωρηθεί αναλυτική περιγραφή για αυτό το προϊόν.', 'spek-theme'); ?></p>
                        <?php endif; ?>
                    </div>
                </article>

                <?php get_template_part('template-parts/products/product-specs'); ?>

            </div>
        </section>

        <?php get_template_part('template-parts/products/product-media'); ?>

        <?php get_template_part('template-parts/products/product-downloads'); ?>

        <?php get_template_part('template-parts/products/related-products'); ?>

    <?php endwhile; ?>

</main>

<?php
get_footer();
