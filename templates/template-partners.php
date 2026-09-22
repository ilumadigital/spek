<?php
/**
 * Template Name: Partners / Store Locator
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main partners-page">
    <?php
    while (have_posts()) :
        the_post();
        ?>

        <section class="page-hero page-hero--compact">
            <div class="container">
                <div class="page-hero__content">
                    <span class="eyebrow"><?php esc_html_e('Store Locator', 'spek-theme'); ?></span>
                    <h1><?php the_title(); ?></h1>
                    <?php if (has_excerpt()) : ?>
                        <p><?php echo esc_html(get_the_excerpt()); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="store-locator">
            <div class="container">
                
                <div class="store-locator__top">
                    <?php get_template_part('template-parts/partners/partner-filters'); ?>
                </div>

                <div class="store-locator-grid">
                    <div class="partners-list">
                        <?php get_template_part('template-parts/partners/partner-card'); ?>
                    </div>

                    <div class="partners-map">
                        <?php get_template_part('template-parts/partners/map'); ?>
                    </div>
                </div>
            </div>
        </section>

        <?php
    endwhile;
    ?>
</main>

<?php
get_footer();