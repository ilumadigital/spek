<?php
/**
 * Site header with language-specific WordPress menus.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$primary_menu_location = function_exists('spek_menu_location') ? spek_menu_location('primary') : 'primary';
?>

<header class="site-header spek-smart-header" data-header>
    <div class="container site-header__inner">

        <div class="site-header__brand">
            <a href="<?php echo esc_url(spek_page_url('')); ?>" class="site-logo site-logo--image" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <img
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                    alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                    class="site-logo__image"
                >
            </a>
        </div>

        <nav class="site-nav" aria-label="<?php esc_attr_e('Κύρια πλοήγηση', 'spek-theme'); ?>">
            <?php if ($primary_menu_location) { wp_nav_menu(['theme_location' => $primary_menu_location, 'container' => false, 'menu_class' => 'site-nav__menu', 'menu_id' => 'primary-menu', 'fallback_cb' => 'spek_primary_menu_fallback']); } else { spek_primary_menu_fallback(['menu_class' => 'site-nav__menu', 'menu_id' => 'primary-menu']); } ?>
        </nav>

        <div class="site-header__actions">
            <div class="site-header__desktop-tools">
                <?php get_template_part('template-parts/global/language-switcher'); ?>

                <a href="<?php echo esc_url(spek_page_url('partners/')); ?>" class="button button-small button-primary site-header__cta">
                    <?php esc_html_e('Ανακαλύψτε συνεργάτη', 'spek-theme'); ?>
                </a>
            </div>

            <button
                class="menu-toggle"
                type="button"
                data-menu-toggle
                aria-controls="site-mobile-menu"
                aria-expanded="false"
                aria-label="<?php esc_attr_e('Άνοιγμα μενού', 'spek-theme'); ?>"
            >
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

    </div>
</header>

<div class="mobile-menu spek-mobile-menu" id="site-mobile-menu" data-mobile-menu aria-hidden="true">
    <div class="mobile-menu__backdrop" data-menu-close></div>

    <div class="mobile-menu__inner">
        <div class="mobile-menu__head">
            <a href="<?php echo esc_url(spek_page_url('')); ?>" class="mobile-menu__logo" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <img
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                    alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                >
            </a>

            <div class="mobile-menu__language">
                <?php get_template_part('template-parts/global/language-switcher'); ?>
            </div>
        </div>

        <nav class="mobile-menu__nav-wrap" aria-label="<?php esc_attr_e('Mobile πλοήγηση', 'spek-theme'); ?>">
            <?php if ($primary_menu_location) { wp_nav_menu(['theme_location' => $primary_menu_location, 'container' => false, 'menu_class' => 'mobile-menu__nav', 'menu_id' => 'mobile-primary-menu', 'fallback_cb' => 'spek_primary_menu_fallback']); } else { spek_primary_menu_fallback(['menu_class' => 'mobile-menu__nav', 'menu_id' => 'mobile-primary-menu']); } ?>
        </nav>

        <div class="mobile-menu__featured">
            <span class="mobile-menu__featured-brand">
                <img
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                    alt="SPEK"
                    class="spek-brand-logo"
                >
                <small>Product System</small>
            </span>
            <strong><?php esc_html_e('Βρείτε γρήγορα το σωστό προϊόν για την εγκατάσταση.', 'spek-theme'); ?></strong>
        </div>

        <div class="mobile-menu__actions">
            <a href="<?php echo esc_url(spek_page_url('partners/')); ?>" class="button button-primary button-full">
                <?php esc_html_e('Ανακαλύψτε συνεργάτη', 'spek-theme'); ?>
            </a>

            <a href="<?php echo esc_url(spek_page_url('contact/')); ?>" class="button button-secondary button-full">
                <?php esc_html_e('Επικοινωνία', 'spek-theme'); ?>
            </a>
        </div>
    </div>
</div>