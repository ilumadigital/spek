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
            <span class="eyebrow"><?php esc_html_e('Product Finder', 'spek-theme'); ?></span>

            <h2><?php esc_html_e('Ο πιο γρήγορος τρόπος να φτάσετε στο σωστό προϊόν.', 'spek-theme'); ?></h2>

            <p>
                <?php esc_html_e('Ο ψηφιακός οδηγός SPEK μετατρέπει την επιλογή προϊόντος σε μια καθαρή διαδικασία: κατηγορία, εφαρμογή, βασικές προδιαγραφές και άμεση πρόταση προϊόντων.', 'spek-theme'); ?>
            </p>

            <div class="hero-actions">
                <a href="<?php echo esc_url(home_url('/product-finder/')); ?>" class="button button-primary">
                    <?php esc_html_e('Ξεκινήστε τον οδηγό', 'spek-theme'); ?>
                </a>

                <a href="<?php echo esc_url(home_url('/products/')); ?>" class="button button-secondary">
                    <?php esc_html_e('Προβολή όλων των προϊόντων', 'spek-theme'); ?>
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
                <figcaption><?php esc_html_e('Σύγχρονος παραγωγικός εξοπλισμός SPEK', 'spek-theme'); ?></figcaption>
            </figure>


            <div class="finder-preview__line" aria-hidden="true"></div>

            <div class="finder-step is-active">
                <span>01</span>
                <div>
                    <strong><?php esc_html_e('Επιλογή κατηγορίας', 'spek-theme'); ?></strong>
                    <small><?php esc_html_e('Ξεκινήστε από τον τύπο προϊόντος που χρειάζεστε.', 'spek-theme'); ?></small>
                </div>
            </div>

            <div class="finder-step">
                <span>02</span>
                <div>
                    <strong><?php esc_html_e('Επιλογή εφαρμογής', 'spek-theme'); ?></strong>
                    <small><?php esc_html_e('Ορίστε χρήση, περιβάλλον και βασικές ανάγκες εγκατάστασης.', 'spek-theme'); ?></small>
                </div>
            </div>

            <div class="finder-step">
                <span>03</span>
                <div>
                    <strong><?php esc_html_e('Προτάσεις προϊόντων', 'spek-theme'); ?></strong>
                    <small><?php esc_html_e('Δείτε άμεσα τις πιο σχετικές λύσεις SPEK.', 'spek-theme'); ?></small>
                </div>
            </div>
        </div>

    </div>
</section>
