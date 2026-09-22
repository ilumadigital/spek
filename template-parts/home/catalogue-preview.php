<?php
/**
 * Catalogue preview section.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$iluma_image_base = get_template_directory_uri() . '/assets/images/iluma/webp/';
?>

<section class="section catalogue-preview">
    <div class="container catalogue-preview__box premium-catalogue-box">

        <div class="catalogue-preview__content" data-reveal>
            <span class="eyebrow"><?php esc_html_e('Catalogue', 'spek-theme'); ?></span>

            <h2><?php esc_html_e('Ο κατάλογος SPEK γίνεται εργαλείο δουλειάς.', 'spek-theme'); ?></h2>

            <p>
                <?php esc_html_e('Περιηγηθείτε στις κατηγορίες, δείτε προϊόντα και αποκτήστε γρήγορη πρόσβαση σε τεχνικές πληροφορίες που βοηθούν στην επιλογή, στην πώληση και στην εγκατάσταση.', 'spek-theme'); ?>
            </p>

            <div class="hero-actions">
                <a href="<?php echo esc_url(home_url('/catalogues/')); ?>" class="button button-primary">
                    <?php esc_html_e('Άνοιγμα καταλόγων', 'spek-theme'); ?>
                </a>

                <a href="<?php echo esc_url(home_url('/products/')); ?>" class="button button-secondary">
                    <?php esc_html_e('Δείτε προϊόντα', 'spek-theme'); ?>
                </a>
            </div>
        </div>

        <div class="catalogue-preview__visual" data-reveal style="--reveal-delay: 120ms;">
            <img
                class="catalogue-preview__photo"
                src="<?php echo esc_url($iluma_image_base . 'spek-ready-website1-1-1.webp'); ?>"
                alt="<?php esc_attr_e('Η εγκατάσταση της SPEK', 'spek-theme'); ?>"
                loading="lazy"
                decoding="async"
            >

            <div class="catalogue-sheet catalogue-sheet--back"></div>

            <div class="catalogue-sheet catalogue-sheet--front">
                <span><?php esc_html_e('SPEK Catalogue', 'spek-theme'); ?></span>
                <strong><?php esc_html_e('Products. Codes. Applications.', 'spek-theme'); ?></strong>
                <small><?php esc_html_e('Καθαρή δομή προϊόντων για γρήγορη επαγγελματική αναζήτηση.', 'spek-theme'); ?></small>
            </div>

            <div class="catalogue-chip catalogue-chip--one">
                <?php esc_html_e('Technical info', 'spek-theme'); ?>
            </div>

            <div class="catalogue-chip catalogue-chip--two">
                <?php esc_html_e('B2B ready', 'spek-theme'); ?>
            </div>
        </div>

    </div>
</section>
