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

    <?php get_template_part('template-parts/home/product-categories'); ?>

    <?php get_template_part('template-parts/home/product-finder-preview'); ?>

    <?php get_template_part('template-parts/home/why-spek'); ?>

    <?php get_template_part('template-parts/home/catalogue-preview'); ?>

    <?php get_template_part('template-parts/home/store-locator-preview'); ?>

    <?php get_template_part('template-parts/home/b2b-cta'); ?>

</main>

<?php
get_footer();
