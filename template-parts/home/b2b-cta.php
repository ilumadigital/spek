<?php
/**
 * B2B CTA.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<section class="section b2b-cta">
    <div class="container b2b-cta__inner">

        <div data-reveal>
            <span class="eyebrow"><?php esc_html_e('B2B Support', 'spek-theme'); ?></span>

            <h2><?php esc_html_e('Για επαγγελματίες, συνεργάτες και σημεία πώλησης.', 'spek-theme'); ?></h2>

            <p>
                <?php esc_html_e('Η ομάδα της SPEK μπορεί να σας υποστηρίξει με πληροφορίες προϊόντων, εμπορικά αιτήματα, καταλόγους και συνεργασίες.', 'spek-theme'); ?>
            </p>
        </div>

        <div class="b2b-cta__meta" data-reveal style="--reveal-delay: 100ms;">
            <span class="support-pill"><?php esc_html_e('Product information', 'spek-theme'); ?></span>
            <span class="support-pill"><?php esc_html_e('Partner support', 'spek-theme'); ?></span>
            <span class="support-pill"><?php esc_html_e('Catalogue access', 'spek-theme'); ?></span>
            <span class="support-pill"><?php esc_html_e('Commercial requests', 'spek-theme'); ?></span>
        </div>

        <a href="<?php echo esc_url(spek_page_url('contact/')); ?>" class="button button-primary" data-reveal style="--reveal-delay: 180ms;">
            <?php esc_html_e('Επικοινωνήστε μαζί μας', 'spek-theme'); ?>
        </a>

    </div>
</section>