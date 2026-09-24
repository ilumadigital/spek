<?php
/**
 * Product finder preview.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$iluma_image_base = get_template_directory_uri() . '/assets/images/iluma/webp/';
?>

<section class="section finder-preview premium-finder">
    <div class="container finder-preview__grid">

        <div class="finder-preview__content" data-reveal>
            <span class="eyebrow"><?php esc_html_e('Εύρεση Προϊόντος', 'spek-theme'); ?></span>

            <h2><?php esc_html_e('Βρείτε το κατάλληλο προϊόν σε λίγα βήματα.', 'spek-theme'); ?></h2>

            <p>
                <?php esc_html_e('Επιλέξτε κατηγορία και εφαρμογή για να δείτε τα προϊόντα SPEK που αντιστοιχούν στα κριτήριά σας.', 'spek-theme'); ?>
            </p>

            <div class="hero-actions">
                <a href="<?php echo esc_url(home_url('/product-finder/')); ?>" class="button button-primary">
                    <?php esc_html_e('Άνοιγμα Product Finder', 'spek-theme'); ?>
                </a>

                <a href="<?php echo esc_url(home_url('/products/')); ?>" class="button button-secondary">
                    <?php esc_html_e('Όλα τα προϊόντα', 'spek-theme'); ?>
                </a>
            </div>
        </div>

        <div class="finder-preview__mockup premium-finder__mockup" data-reveal style="--reveal-delay: 120ms;">
            <figure class="finder-preview__spotlight">
                <img
                    src="<?php echo esc_url($iluma_image_base . 'spek-ready-website2.webp'); ?>"
                    alt="<?php esc_attr_e('Γραμμή παραγωγής SPEK', 'spek-theme'); ?>"
                    loading="lazy"
                    decoding="async"
                >
                <figcaption><?php esc_html_e('Παραγωγική εγκατάσταση SPEK', 'spek-theme'); ?></figcaption>
            </figure>


            <div class="finder-preview__line" aria-hidden="true"></div>

            <div class="finder-step is-active">
                <span>01</span>
                <div>
                    <strong><?php esc_html_e('Επιλογή κατηγορίας', 'spek-theme'); ?></strong>
                    <small><?php esc_html_e('Επιλέξτε την κατηγορία προϊόντος.', 'spek-theme'); ?></small>
                </div>
            </div>

            <div class="finder-step">
                <span>02</span>
                <div>
                    <strong><?php esc_html_e('Επιλογή εφαρμογής', 'spek-theme'); ?></strong>
                    <small><?php esc_html_e('Ορίστε την εφαρμογή και τα βασικά χαρακτηριστικά.', 'spek-theme'); ?></small>
                </div>
            </div>

            <div class="finder-step">
                <span>03</span>
                <div>
                    <strong><?php esc_html_e('Προτάσεις προϊόντων', 'spek-theme'); ?></strong>
                    <small><?php esc_html_e('Δείτε τα προϊόντα που ταιριάζουν στα κριτήρια.', 'spek-theme'); ?></small>
                </div>
            </div>
        </div>

    </div>
</section>
