<?php
/**
 * CTA strip.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<section class="section cta-strip">
    <div class="container cta-strip__inner">
        <div>
            <span class="eyebrow"><?php esc_html_e('Need help?', 'spek-theme'); ?></span>
            <h2><?php esc_html_e('Χρειάζεστε βοήθεια στην επιλογή προϊόντος;', 'spek-theme'); ?></h2>
            <p><?php esc_html_e('Η ομάδα της SPEK μπορεί να σας καθοδηγήσει στην κατάλληλη λύση για την εφαρμογή σας.', 'spek-theme'); ?></p>
        </div>

        <a href="<?php echo esc_url(spek_page_url('contact/')); ?>" class="button button-light">
            <?php esc_html_e('Επικοινωνία', 'spek-theme'); ?>
        </a>
    </div>
</section>