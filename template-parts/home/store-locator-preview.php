<?php
/**
 * Store locator preview.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

?>

<section class="section store-preview">
    <div class="container store-preview__grid">

        <div class="store-preview__visual" data-reveal>
            <div class="map-placeholder premium-map store-preview__illustrated-map">
                <!-- Decorative Athens-inspired map. Three pins are illustrative,
                     never partner locations or real geocoded coordinates. -->
                <img
                    class="store-preview__map-art"
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/home-partners-athens-illustration.svg'); ?>"
                    alt=""
                    width="1200"
                    height="700"
                    loading="lazy"
                    decoding="async"
                    aria-hidden="true"
                >
                <span class="store-preview__label"><?php esc_html_e('Δίκτυο Συνεργατών', 'spek-theme'); ?></span>
            </div>
        </div>

        <div class="store-preview__content" data-reveal style="--reveal-delay: 120ms;">
            <span class="eyebrow"><?php esc_html_e('Συνεργάτες', 'spek-theme'); ?></span>

            <h2><?php esc_html_e('Βρείτε συνεργάτη ή σημείο πώλησης SPEK', 'spek-theme'); ?></h2>

            <p>
                <?php esc_html_e('Αναζητήστε το δίκτυο συνεργατών και σημείων πώλησης της SPEK ανά περιοχή.', 'spek-theme'); ?>
            </p>

            <div class="hero-actions">
                <a href="<?php echo esc_url(home_url('/partners/')); ?>" class="button button-primary">
                    <?php esc_html_e('Εύρεση συνεργάτη', 'spek-theme'); ?>
                </a>

                <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="button button-secondary">
                    <?php esc_html_e('Επικοινωνία', 'spek-theme'); ?>
                </a>
            </div>
        </div>

    </div>
</section>
