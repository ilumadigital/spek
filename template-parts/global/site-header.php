<?php
/**
 * Site header with a fixed primary navigation and products mega menu.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$products_url = spek_page_url('products/');

$product_menu_categories = [
    [
        'title' => __('Μηχανισμοί', 'spek-theme'),
        'text'  => __('Μηχανισμοί σχεδιασμένοι για σωστή εφαρμογή, καθημερινή λειτουργία και διαχρονική αξιοπιστία.', 'spek-theme'),
        'group' => 'mixanismoi',
        'icon'  => 'mechanisms',
    ],
    [
        'title' => __('Φλοτέρ', 'spek-theme'),
        'text'  => __('Όλες οι λύσεις για έλεγχο στη στάθμη του νερού σε όλα τα καζανάκια.', 'spek-theme'),
        'group' => 'floter',
        'icon'  => 'float',
    ],
    [
        'title' => __('Σιφόνια', 'spek-theme'),
        'text'  => __('Εύκαμπτες, πρακτικές και ανθεκτικές επιλογές αποχέτευσης για νιπτήρες, μπάνια και νεροχύτες.', 'spek-theme'),
        'group' => 'sifonia',
        'icon'  => 'siphon',
    ],
    [
        'title' => __('Καλύμματα λεκάνης', 'spek-theme'),
        'text'  => __('Καλαίσθητες και πρακτικές λύσεις για λεκάνες WC, με έμφαση στην άνεση, την αντοχή και την εύκολη εφαρμογή.', 'spek-theme'),
        'group' => 'kalymmata-lekanis',
        'icon'  => 'seat',
    ],
    [
        'title' => __('Καζανάκια', 'spek-theme'),
        'text'  => __('Λειτουργικά συστήματα για μπάνιο και WC, με έμφαση στην ευκολία χρήσης και την αξιοπιστία.', 'spek-theme'),
        'group' => 'kazanakia',
        'icon'  => 'cistern',
    ],
    [
        'title' => __('Μπάνιο', 'spek-theme'),
        'text'  => __('Επιλεγμένες λύσεις για τον εξοπλισμό μπάνιου, σχεδιασμένες για λειτουργικότητα, άνεση και καθημερινή χρήση.', 'spek-theme'),
        'group' => 'banio',
        'icon'  => 'bathroom',
    ],
    [
        'title' => __('Λάστιχα', 'spek-theme'),
        'text'  => __('Λύσεις στεγανοποίησης για εγκαταστάσεις με ευκολία και σταθερό αποτέλεσμα.', 'spek-theme'),
        'group' => 'lastixa',
        'icon'  => 'seals',
    ],
    [
        'title' => __('Διάφορα', 'spek-theme'),
        'text'  => __('Συμπληρωματικές λύσεις, ανταλλακτικά και ειδικές σειρές, μαζί με τη Nemo, για ανάγκες πέρα από τις βασικές κατηγορίες.', 'spek-theme'),
        'group' => 'diafora',
        'icon'  => 'misc',
    ],
];

if (!function_exists('spek_header_product_icon')) {
    function spek_header_product_icon(string $icon): void
    {
        spek_render_product_category_icon(
            $icon,
            'category-card__svg spek-menu-category__svg spek-category-icon'
        );
    }
}

$is_home = is_front_page() || is_home();
$is_company = is_page(['company-details', 'company']) || is_page_template('templates/template-company.php');
$is_products = is_post_type_archive('spek_product') || is_singular('spek_product') || is_tax('product_category');
$is_catalogue = is_page('catalogues') || is_page_template('templates/template-catalogues.php') || is_singular('spek_catalogue');
$is_contact = is_page('contact') || is_page_template('templates/template-contact.php');
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

        <nav class="site-nav spek-primary-nav" aria-label="<?php esc_attr_e('Κύρια πλοήγηση', 'spek-theme'); ?>">
            <ul class="site-nav__menu" id="primary-menu">
                <li class="menu-item <?php echo $is_home ? 'is-current' : ''; ?>">
                    <a href="<?php echo esc_url(spek_page_url('')); ?>">
                        <?php esc_html_e('Αρχική', 'spek-theme'); ?>
                    </a>
                </li>

                <li class="menu-item <?php echo $is_company ? 'is-current' : ''; ?>">
                    <a href="<?php echo esc_url(spek_page_url('company/')); ?>">
                        <?php esc_html_e('Η Εταιρεία', 'spek-theme'); ?>
                    </a>
                </li>

                <li class="menu-item spek-products-menu-item <?php echo $is_products ? 'is-current' : ''; ?>">
                    <a
                        href="<?php echo esc_url($products_url); ?>"
                        class="spek-products-menu-trigger"
                        aria-haspopup="true"
                        aria-expanded="false"
                        aria-controls="spek-products-mega-menu"
                        data-products-menu-trigger
                    >
                        <span><?php esc_html_e('Προϊόντα', 'spek-theme'); ?></span>
                        <svg viewBox="0 0 12 8" aria-hidden="true"><path d="m1 1 5 5 5-5"/></svg>
                    </a>

                </li>

                <li class="menu-item <?php echo $is_catalogue ? 'is-current' : ''; ?>">
                    <a href="<?php echo esc_url(spek_page_url('catalogues/')); ?>">
                        <?php esc_html_e('Κατάλογος', 'spek-theme'); ?>
                    </a>
                </li>

                <li class="menu-item <?php echo $is_contact ? 'is-current' : ''; ?>">
                    <a href="<?php echo esc_url(spek_page_url('contact/')); ?>">
                        <?php esc_html_e('Επικοινωνία', 'spek-theme'); ?>
                    </a>
                </li>
            </ul>


            <div
                class="spek-mega-menu"
                id="spek-products-mega-menu"
                aria-label="<?php esc_attr_e('Κατηγορίες προϊόντων', 'spek-theme'); ?>"
                data-products-mega-menu
            >
                <div class="spek-mega-menu__head">
                    <div>
                        <span><?php esc_html_e('Προϊόντα', 'spek-theme'); ?></span>
                        <strong><?php esc_html_e('Κατηγορίες προϊόντων', 'spek-theme'); ?></strong>
                    </div>
                    <a href="<?php echo esc_url($products_url); ?>" class="spek-mega-menu__all">
                        <?php esc_html_e('Όλα τα προϊόντα', 'spek-theme'); ?><span aria-hidden="true">→</span>
                    </a>
                </div>

                <div class="category-grid spek-mega-menu__grid">
                    <?php foreach ($product_menu_categories as $category) : ?>
                        <?php $category_url = add_query_arg('home_category_group', $category['group'], $products_url); ?>
                        <a href="<?php echo esc_url($category_url); ?>" class="category-card spek-mega-menu__card">
                            <span class="category-card__icon spek-menu-category__icon" aria-hidden="true">
                                <?php spek_header_product_icon($category['icon']); ?>
                            </span>
                            <h3><?php echo esc_html($category['title']); ?></h3>
                            <p><?php echo esc_html($category['text']); ?></p>
                            <span class="category-card__link"><?php esc_html_e('Δείτε κατηγορία', 'spek-theme'); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </nav>

        <div class="site-header__actions">
            <div class="site-header__desktop-tools">
                <?php get_template_part('template-parts/global/language-switcher'); ?>
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

        <nav class="mobile-menu__nav-wrap" aria-label="<?php esc_attr_e('Πλοήγηση κινητού', 'spek-theme'); ?>">
            <ul class="mobile-menu__nav" id="mobile-primary-menu">
                <li class="<?php echo $is_home ? 'is-current' : ''; ?>">
                    <a href="<?php echo esc_url(spek_page_url('')); ?>">
                        <?php esc_html_e('Αρχική', 'spek-theme'); ?>
                    </a>
                </li>

                <li class="<?php echo $is_company ? 'is-current' : ''; ?>">
                    <a href="<?php echo esc_url(spek_page_url('company/')); ?>">
                        <?php esc_html_e('Η Εταιρεία', 'spek-theme'); ?>
                    </a>
                </li>

                <li class="mobile-menu__products <?php echo $is_products ? 'is-current' : ''; ?>">
                    <a href="<?php echo esc_url($products_url); ?>" class="mobile-menu__products-title">
                        <?php esc_html_e('Προϊόντα', 'spek-theme'); ?>
                    </a>

                    <div class="mobile-products-grid" aria-label="<?php esc_attr_e('Κατηγορίες προϊόντων', 'spek-theme'); ?>">
                        <?php foreach ($product_menu_categories as $category) : ?>
                            <?php $category_url = add_query_arg('home_category_group', $category['group'], $products_url); ?>
                            <a href="<?php echo esc_url($category_url); ?>" class="mobile-product-category">
                                <span class="spek-menu-category__icon">
                                    <?php spek_header_product_icon($category['icon']); ?>
                                </span>
                                <strong><?php echo esc_html($category['title']); ?></strong>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </li>

                <li class="<?php echo $is_catalogue ? 'is-current' : ''; ?>">
                    <a href="<?php echo esc_url(spek_page_url('catalogues/')); ?>">
                        <?php echo esc_html(function_exists('spek_is_english') && spek_is_english() ? 'Product Catalogue' : 'Κατάλογος Προϊόντων'); ?>
                    </a>
                </li>

                <li class="<?php echo $is_contact ? 'is-current' : ''; ?>">
                    <a href="<?php echo esc_url(spek_page_url('contact/')); ?>">
                        <?php esc_html_e('Επικοινωνία', 'spek-theme'); ?>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>
