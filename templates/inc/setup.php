<?php
/**
 * Theme setup.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

function spek_theme_setup(): void
{
    load_theme_textdomain('spek-theme', SPEK_THEME_DIR . '/languages');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', [
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ]);

    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor.css');

    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');

    // One canonical menu per location. SPEK i18n stores the English label on
    // each menu item and prefixes internal frontend URLs with /en/.
    register_nav_menus([
        'primary' => __('Primary Menu', 'spek-theme'),
        'footer_company' => __('Footer Company Menu', 'spek-theme'),
        'footer_products' => __('Footer Products Menu', 'spek-theme'),
        'footer_support' => __('Footer Support Menu', 'spek-theme'),
    ]);

    add_image_size('spek_product_card', 520, 520, true);
    add_image_size('spek_product_large', 900, 900, false);
    add_image_size('spek_hero', 1920, 980, true);
    add_image_size('spek_card_wide', 760, 480, true);
}

add_action('after_setup_theme', 'spek_theme_setup');

function spek_content_width(): void
{
    $GLOBALS['content_width'] = apply_filters('spek_content_width', 1280);
}

add_action('after_setup_theme', 'spek_content_width', 0);