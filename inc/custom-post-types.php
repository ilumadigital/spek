<?php
/**
 * Custom Post Types.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

function spek_register_custom_post_types(): void
{
    register_post_type('spek_product', [
        'labels' => [
            'name' => __('Προϊόντα', 'spek-theme'),
            'singular_name' => __('Προϊόν', 'spek-theme'),
            'menu_name' => __('Προϊόντα', 'spek-theme'),
            'name_admin_bar' => __('Προϊόν', 'spek-theme'),
            'add_new' => __('Προσθήκη νέου', 'spek-theme'),
            'add_new_item' => __('Προσθήκη νέου προϊόντος', 'spek-theme'),
            'new_item' => __('Νέο προϊόν', 'spek-theme'),
            'edit_item' => __('Επεξεργασία προϊόντος', 'spek-theme'),
            'view_item' => __('Προβολή προϊόντος', 'spek-theme'),
            'all_items' => __('Προϊόντα', 'spek-theme'),
            'search_items' => __('Αναζήτηση προϊόντων', 'spek-theme'),
            'not_found' => __('Δεν βρέθηκαν προϊόντα', 'spek-theme'),
            'not_found_in_trash' => __('Δεν βρέθηκαν προϊόντα στον κάδο', 'spek-theme'),
            'featured_image' => __('Εικόνα προϊόντος', 'spek-theme'),
            'set_featured_image' => __('Ορισμός εικόνας προϊόντος', 'spek-theme'),
            'remove_featured_image' => __('Αφαίρεση εικόνας προϊόντος', 'spek-theme'),
            'use_featured_image' => __('Χρήση ως εικόνα προϊόντος', 'spek-theme'),
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => [
            'slug' => 'products',
            'with_front' => false,
        ],
        'menu_icon' => 'dashicons-products',
        'supports' => [
            'title',
            'editor',
            'thumbnail',
            'revisions',
        ],
        'show_in_rest' => true,
    ]);

    register_post_type('spek_partner', [
        'labels' => [
            'name'                  => __('Συνεργάτες / Σημεία Πώλησης', 'spek-theme'),
            'singular_name'         => __('Συνεργάτης', 'spek-theme'),
            'menu_name'             => __('Σημεία Πώλησης', 'spek-theme'),
            'name_admin_bar'        => __('Συνεργάτης', 'spek-theme'),
            'add_new'               => __('Προσθήκη νέου', 'spek-theme'),
            'add_new_item'          => __('Προσθήκη συνεργάτη', 'spek-theme'),
            'new_item'              => __('Νέος συνεργάτης', 'spek-theme'),
            'edit_item'             => __('Επεξεργασία συνεργάτη', 'spek-theme'),
            'view_item'             => __('Προβολή συνεργάτη', 'spek-theme'),
            'all_items'             => __('Όλοι οι συνεργάτες', 'spek-theme'),
            'search_items'          => __('Αναζήτηση συνεργατών', 'spek-theme'),
            'not_found'             => __('Δεν βρέθηκαν συνεργάτες', 'spek-theme'),
            'not_found_in_trash'    => __('Δεν βρέθηκαν συνεργάτες στον κάδο', 'spek-theme'),
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'has_archive'         => false,
        'rewrite'             => false,
        'menu_icon'           => 'dashicons-location-alt',
        'supports'            => [
            'title',
            'revisions',
        ],
    ]);

    register_post_type('spek_catalogue', [
        'labels' => [
            'name' => __('Catalogues', 'spek-theme'),
            'singular_name' => __('Catalogue', 'spek-theme'),
            'add_new_item' => __('Add New Catalogue', 'spek-theme'),
            'edit_item' => __('Edit Catalogue', 'spek-theme'),
        ],
        'public' => true,
        'has_archive' => false,
        'rewrite' => [
            'slug' => 'catalogues',
            'with_front' => false,
        ],
        'menu_icon' => 'dashicons-media-document',
        'supports' => [
            'title',
            'editor',
            'excerpt',
            'thumbnail',
            'revisions',
        ],
        'show_in_rest' => true,
    ]);

    register_post_type('spek_career', [
        'labels' => [
            'name' => __('Careers', 'spek-theme'),
            'singular_name' => __('Career', 'spek-theme'),
            'add_new_item' => __('Add New Career Position', 'spek-theme'),
            'edit_item' => __('Edit Career Position', 'spek-theme'),
        ],
        'public' => true,
        'has_archive' => false,
        'rewrite' => [
            'slug' => 'careers',
            'with_front' => false,
        ],
        'menu_icon' => 'dashicons-businessperson',
        'supports' => [
            'title',
            'editor',
            'excerpt',
            'revisions',
        ],
        'show_in_rest' => true,
    ]);
}

add_action('init', 'spek_register_custom_post_types', 0);