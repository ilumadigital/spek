<?php
/**
 * Template Name: Company
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main" class="site-main page-company spek-company-page">
    <?php get_template_part('template-parts/company/company-hero'); ?>
    <?php get_template_part('template-parts/company/history'); ?>
    <?php get_template_part('template-parts/company/values'); ?>
    <div class="home-section-group home-section-group--two company-why-spek-group">
        <?php get_template_part('template-parts/home/why-spek'); ?>
    </div>
    <?php get_template_part('template-parts/company/certifications'); ?>
    <?php get_template_part('template-parts/company/exclusive-partnerships'); ?>
    <?php get_template_part('template-parts/company/global'); ?>
</main>

<?php
get_footer();
