<?php
/**
 * Header template.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main">
    <?php esc_html_e('Skip to content', 'spek-theme'); ?>
</a>

<div id="page" class="site">

    <?php get_template_part('template-parts/global/site-header'); ?>