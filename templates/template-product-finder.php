<?php
/**
 * Template Name: Product Finder
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main" class="site-main product-finder-page">

    <section class="page-hero">
        <div class="container">
            <span class="eyebrow"><?php esc_html_e('Product Finder', 'spek-theme'); ?></span>
            <h1><?php esc_html_e('Βρείτε το κατάλληλο προϊόν', 'spek-theme'); ?></h1>
            <p><?php esc_html_e('Απαντήστε σε λίγα βήματα και εντοπίστε το προϊόν SPEK που ταιριάζει στην εφαρμογή σας.', 'spek-theme'); ?></p>
        </div>
    </section>

    <section class="section finder-app" id="spek-product-finder">
        <div class="container">
            <?php get_template_part('template-parts/finder/finder-start'); ?>
            <?php get_template_part('template-parts/finder/finder-step-category'); ?>
            <?php get_template_part('template-parts/finder/finder-step-application'); ?>
            <?php get_template_part('template-parts/finder/finder-step-specs'); ?>
            <?php get_template_part('template-parts/finder/finder-results'); ?>
        </div>
    </section>

</main>

<?php
get_footer();