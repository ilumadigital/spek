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
            <span class="eyebrow"><?php esc_html_e('B2B Υποστήριξη', 'spek-theme'); ?></span>

            <h2><?php esc_html_e('Επαγγελματική υποστήριξη από τη SPEK.', 'spek-theme'); ?></h2>

            <p>
                <?php esc_html_e('Για πληροφορίες προϊόντων, τεχνικό κατάλογο, διαθεσιμότητα και εμπορική συνεργασία, επικοινωνήστε με την ομάδα μας.', 'spek-theme'); ?>
            </p>
        </div>

        <div class="b2b-cta__meta" data-reveal style="--reveal-delay: 100ms;">
            <span class="support-pill"><?php esc_html_e('Πληροφορίες προϊόντων', 'spek-theme'); ?></span>
            <span class="support-pill"><?php esc_html_e('Υποστήριξη συνεργατών', 'spek-theme'); ?></span>
            <span class="support-pill"><?php esc_html_e('Τεχνικός κατάλογος', 'spek-theme'); ?></span>
            <span class="support-pill"><?php esc_html_e('Εμπορικά αιτήματα', 'spek-theme'); ?></span>
        </div>

        <a href="<?php echo esc_url(spek_page_url('contact/')); ?>" class="button button-primary" data-reveal style="--reveal-delay: 180ms;">
            <?php esc_html_e('Επικοινωνία', 'spek-theme'); ?>
        </a>

    </div>
</section>