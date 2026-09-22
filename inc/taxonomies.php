<?php
/**
 * Custom Taxonomies.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

function spek_register_taxonomies(): void
{
    register_taxonomy('product_category', ['spek_product'], [
        'labels' => [
            'name' => __('Κατηγορίες προϊόντων', 'spek-theme'),
            'singular_name' => __('Κατηγορία προϊόντος', 'spek-theme'),
            'menu_name' => __('Κατηγορίες προϊόντων', 'spek-theme'),
            'all_items' => __('Όλες οι κατηγορίες', 'spek-theme'),
            'edit_item' => __('Επεξεργασία κατηγορίας', 'spek-theme'),
            'view_item' => __('Προβολή κατηγορίας', 'spek-theme'),
            'update_item' => __('Ενημέρωση κατηγορίας', 'spek-theme'),
            'add_new_item' => __('Προσθήκη νέας κατηγορίας', 'spek-theme'),
            'new_item_name' => __('Νέο όνομα κατηγορίας', 'spek-theme'),
            'search_items' => __('Αναζήτηση κατηγοριών', 'spek-theme'),
        ],
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => [
            'slug' => 'product-category',
            'with_front' => false,
        ],
    ]);

    register_taxonomy('product_application', ['spek_product'], [
        'labels' => [
            'name' => __('Εφαρμογές προϊόντων', 'spek-theme'),
            'singular_name' => __('Εφαρμογή προϊόντος', 'spek-theme'),
            'menu_name' => __('Εφαρμογές', 'spek-theme'),
            'all_items' => __('Όλες οι εφαρμογές', 'spek-theme'),
            'edit_item' => __('Επεξεργασία εφαρμογής', 'spek-theme'),
            'view_item' => __('Προβολή εφαρμογής', 'spek-theme'),
            'update_item' => __('Ενημέρωση εφαρμογής', 'spek-theme'),
            'add_new_item' => __('Προσθήκη νέας εφαρμογής', 'spek-theme'),
            'new_item_name' => __('Νέο όνομα εφαρμογής', 'spek-theme'),
            'search_items' => __('Αναζήτηση εφαρμογών', 'spek-theme'),
        ],
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => [
            'slug' => 'product-application',
            'with_front' => false,
        ],
    ]);

    register_taxonomy('product_material', ['spek_product'], [
        'labels' => [
            'name' => __('Υλικά προϊόντων', 'spek-theme'),
            'singular_name' => __('Υλικό προϊόντος', 'spek-theme'),
            'menu_name' => __('Υλικά', 'spek-theme'),
            'all_items' => __('Όλα τα υλικά', 'spek-theme'),
            'edit_item' => __('Επεξεργασία υλικού', 'spek-theme'),
            'view_item' => __('Προβολή υλικού', 'spek-theme'),
            'update_item' => __('Ενημέρωση υλικού', 'spek-theme'),
            'add_new_item' => __('Προσθήκη νέου υλικού', 'spek-theme'),
            'new_item_name' => __('Νέο όνομα υλικού', 'spek-theme'),
            'search_items' => __('Αναζήτηση υλικών', 'spek-theme'),
        ],
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => [
            'slug' => 'product-material',
            'with_front' => false,
        ],
    ]);

    register_taxonomy('product_series', ['spek_product'], [
        'labels' => [
            'name' => __('Σειρές προϊόντων', 'spek-theme'),
            'singular_name' => __('Σειρά προϊόντος', 'spek-theme'),
            'menu_name' => __('Σειρές', 'spek-theme'),
            'all_items' => __('Όλες οι σειρές', 'spek-theme'),
            'edit_item' => __('Επεξεργασία σειράς', 'spek-theme'),
            'view_item' => __('Προβολή σειράς', 'spek-theme'),
            'update_item' => __('Ενημέρωση σειράς', 'spek-theme'),
            'add_new_item' => __('Προσθήκη νέας σειράς', 'spek-theme'),
            'new_item_name' => __('Νέο όνομα σειράς', 'spek-theme'),
            'search_items' => __('Αναζήτηση σειρών', 'spek-theme'),
        ],
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => [
            'slug' => 'product-series',
            'with_front' => false,
        ],
    ]);

    register_taxonomy('partner_region', ['spek_partner'], [
        'labels' => [
            'name' => __('Περιοχές συνεργατών', 'spek-theme'),
            'singular_name' => __('Περιοχή συνεργάτη', 'spek-theme'),
            'menu_name' => __('Περιοχές', 'spek-theme'),
            'add_new_item' => __('Προσθήκη νέας περιοχής', 'spek-theme'),
        ],
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => [
            'slug' => 'partner-region',
            'with_front' => false,
        ],
    ]);

    register_taxonomy('partner_type', ['spek_partner'], [
        'labels' => [
            'name' => __('Τύποι συνεργατών', 'spek-theme'),
            'singular_name' => __('Τύπος συνεργάτη', 'spek-theme'),
            'menu_name' => __('Τύποι συνεργατών', 'spek-theme'),
            'add_new_item' => __('Προσθήκη νέου τύπου', 'spek-theme'),
        ],
        'hierarchical' => true,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => [
            'slug' => 'partner-type',
            'with_front' => false,
        ],
    ]);
}

add_action('init', 'spek_register_taxonomies', 1);