<?php
/**
 * Front page template.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main" class="site-main front-page">

    <?php get_template_part('template-parts/home/hero'); ?>

    <div class="home-section-group home-section-group--one">
        <?php get_template_part('template-parts/home/product-categories'); ?>
        <?php get_template_part('template-parts/home/product-finder-preview'); ?>
    </div>

    <div class="home-section-group home-section-group--two">
        <?php get_template_part('template-parts/home/why-spek'); ?>
        <?php get_template_part('template-parts/home/catalogue-preview'); ?>
    </div>

    <div class="home-section-group home-section-group--three">
        <?php get_template_part('template-parts/home/store-locator-preview'); ?>
        <?php get_template_part('template-parts/home/b2b-cta'); ?>
    </div>

</main>

<?php
get_footer();
