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
    <?php get_template_part('template-parts/home/why-spek'); ?>
    <?php get_template_part('template-parts/company/values'); ?>
    <?php get_template_part('template-parts/company/certifications'); ?>
</main>

<?php
get_footer();
