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
            <div class="map-placeholder premium-map">
                <div class="map-grid" aria-hidden="true"></div>

                <span class="store-preview__label"><?php esc_html_e('Partner Network', 'spek-theme'); ?></span>

                <i class="store-point store-point--one" aria-hidden="true"></i>
                <i class="store-point store-point--two" aria-hidden="true"></i>
                <i class="store-point store-point--three" aria-hidden="true"></i>
            </div>
        </div>

        <div class="store-preview__content" data-reveal style="--reveal-delay: 120ms;">
            <span class="eyebrow"><?php esc_html_e('Partners', 'spek-theme'); ?></span>

            <h2><?php esc_html_e('Βρείτε σημείο πώλησης ή συνεργάτη SPEK κοντά σας.', 'spek-theme'); ?></h2>

            <p>
                <?php esc_html_e('Ο χάρτης συνεργατών βοηθά επαγγελματίες και καταναλωτές να εντοπίσουν σημεία πώλησης και συνεργάτες της SPEK με πιο άμεσο και οργανωμένο τρόπο.', 'spek-theme'); ?>
            </p>

            <div class="hero-actions">
                <a href="<?php echo esc_url(home_url('/partners/')); ?>" class="button button-primary">
                    <?php esc_html_e('Άνοιγμα χάρτη συνεργατών', 'spek-theme'); ?>
                </a>

                <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="button button-secondary">
                    <?php esc_html_e('Ζητήστε πληροφορίες', 'spek-theme'); ?>
                </a>
            </div>
        </div>

    </div>
</section>
