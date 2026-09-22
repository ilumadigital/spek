<?php
/**
 * Site footer with hardcoded SPEK navigation.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$products_url = get_post_type_archive_link('spek_product');
$products_url = $products_url ? $products_url : spek_page_url('products/');

$footer_columns = [
    [
        'location' => 'footer_products',
        'title' => __('Πλοήγηση', 'spek-theme'),
        'links' => [
            [
                'label' => __('Αρχική', 'spek-theme'),
                'url'   => spek_page_url(''),
            ],
            [
                'label' => __('Προϊόντα', 'spek-theme'),
                'url'   => $products_url,
            ],
            [
                'label' => __('Κατάλογοι', 'spek-theme'),
                'url'   => spek_page_url('catalogues/'),
            ],
            [
                'label' => __('Σημεία Πώλησης', 'spek-theme'),
                'url'   => spek_page_url('partners/'),
            ],
        ],
    ],
    [
        'location' => 'footer_company',
        'title' => __('Εταιρεία', 'spek-theme'),
        'links' => [
            [
                'label' => __('Η εταιρεία', 'spek-theme'),
                'url'   => spek_page_url('company/'),
            ],
            [
                'label' => __('Στοιχεία εταιρείας', 'spek-theme'),
                'url'   => spek_page_url('company-details/'),
            ],
            [
                'label' => __('Συνεργάτες', 'spek-theme'),
                'url'   => spek_page_url('partners/'),
            ],
            [
                'label' => __('Καριέρα', 'spek-theme'),
                'url'   => spek_page_url('careers/'),
            ],
            [
                'label' => __('Επικοινωνία', 'spek-theme'),
                'url'   => spek_page_url('contact/'),
            ],
        ],
    ],
    [
        'location' => 'footer_support',
        'title' => __('Υποστήριξη', 'spek-theme'),
        'links' => [
            [
                'label' => __('Οδηγός επιλογής', 'spek-theme'),
                'url'   => spek_page_url('product-finder/'),
            ],
            [
                'label' => __('Τεχνικοί κατάλογοι', 'spek-theme'),
                'url'   => spek_page_url('catalogues/'),
            ],
            [
                'label' => __('B2B συνεργασία', 'spek-theme'),
                'url'   => spek_page_url('contact/'),
            ],
            [
                'label' => __('Αίτημα πληροφόρησης', 'spek-theme'),
                'url'   => spek_page_url('contact/'),
            ],
        ],
    ],
];
?>

<footer class="site-footer" id="site-footer">
    <!--<div class="container site-footer__cta">-->
    <!--    <div class="site-footer__cta-content">-->
    <!--        <span class="site-footer__eyebrow">-->
    <!--            <?php esc_html_e('SPEK SUPPORT', 'spek-theme'); ?>-->
    <!--        </span>-->

    <!--        <h2>-->
    <!--            <?php esc_html_e('Χρειάζεστε βοήθεια στην επιλογή προϊόντος;', 'spek-theme'); ?>-->
    <!--        </h2>-->

    <!--        <p>-->
    <!--            <?php esc_html_e('Βρείτε γρήγορα τη σωστή λύση για το έργο σας ή επικοινωνήστε με την ομάδα μας για τεχνική καθοδήγηση.', 'spek-theme'); ?>-->
    <!--        </p>-->
    <!--    </div>-->

    <!--    <div class="site-footer__cta-actions">-->
    <!--        <a href="<?php echo esc_url(spek_page_url('product-finder/')); ?>" class="button button-primary">-->
    <!--            <?php esc_html_e('Βρείτε προϊόν', 'spek-theme'); ?>-->
    <!--        </a>-->

    <!--        <a href="<?php echo esc_url(spek_page_url('contact/')); ?>" class="button button-light">-->
    <!--            <?php esc_html_e('Επικοινωνία', 'spek-theme'); ?>-->
    <!--        </a>-->
    <!--    </div>-->
    <!--</div>-->

    <div class="container site-footer__main">
        <div class="site-footer__brand">
            <a href="<?php echo esc_url(spek_page_url('')); ?>" class="site-footer__logo site-footer__logo--image" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?>">
                <img
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                    alt="<?php echo esc_attr(get_bloginfo('name')); ?>"
                    class="site-footer__logo-image"
                >
            </a>

<!--<div class="site-footer__badges">-->
            <!--    <span><?php esc_html_e('Technical precision', 'spek-theme'); ?></span>-->
            <!--    <span><?php esc_html_e('B2B ready', 'spek-theme'); ?></span>-->
            <!--</div>-->
        </div>

        <?php foreach ($footer_columns as $column) : ?>
            <?php $resolved_location = function_exists('spek_menu_location') ? spek_menu_location($column['location']) : $column['location']; ?>
            <div class="site-footer__column">
                <h3><?php echo esc_html($column['title']); ?></h3>

                <?php if ($resolved_location) : ?>
                    <?php wp_nav_menu(['theme_location' => $resolved_location, 'container' => false, 'menu_class' => 'footer-menu', 'fallback_cb' => false]); ?>
                <?php elseif (!function_exists('spek_allow_hardcoded_menu_fallback') || spek_allow_hardcoded_menu_fallback()) : ?>
                <ul class="footer-menu">
                    <?php foreach ($column['links'] as $link) : ?>
                        <li>
                            <a href="<?php echo esc_url($link['url']); ?>">
                                <?php echo esc_html($link['label']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="site-footer__contact">
            <h3><?php esc_html_e('Επικοινωνία', 'spek-theme'); ?></h3>

            <p>
                <?php esc_html_e('Για προϊόντα, διαθεσιμότητα, τεχνική πληροφόρηση και εμπορική συνεργασία.', 'spek-theme'); ?>
            </p>

            <a href="<?php echo esc_url(spek_page_url('contact/')); ?>" class="site-footer__contact-link">
                <?php esc_html_e('Επικοινωνήστε μαζί μας', 'spek-theme'); ?>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>

    <div class="container site-footer__bottom">
        <p>
            © <?php echo esc_html(date('Y')); ?> SPEK. <?php esc_html_e('All rights reserved.', 'spek-theme'); ?>
        </p>

        <div class="site-footer__bottom-links">
            <?php get_template_part('template-parts/global/language-switcher'); ?>
            <a href="<?php echo esc_url(spek_page_url('privacy-policy/')); ?>">
                <?php esc_html_e('Privacy Policy', 'spek-theme'); ?>
            </a>

            <a href="#page">
                <?php esc_html_e('Πάνω', 'spek-theme'); ?>
            </a>

            <a
                href="https://iluma.gr"
                class="site-footer__credit"
                target="_blank"
                rel="noopener"
            >
                Handcrafted by <strong>ILUMA Digital Agency</strong>
            </a>
        </div>
    </div>
</footer>