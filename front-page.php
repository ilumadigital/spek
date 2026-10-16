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

    <?php // A compact benefits strip fills the space below the hero video. ?>
    <?php get_template_part('template-parts/home/practical-benefits', null, ['home_strip' => true]); ?>

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
