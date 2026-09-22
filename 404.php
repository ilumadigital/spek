<?php
/**
 * 404 template.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

$products_url = get_post_type_archive_link('spek_product');
if (!$products_url) {
    $products_url = spek_page_url('');
}
?>

<main id="main" class="site-main spek-404">
    <section class="spek-404__section" aria-labelledby="spek-404-title">
        <div class="container spek-404__container">
            <div class="spek-404__content">
                <span class="eyebrow spek-404__eyebrow"><?php esc_html_e('Σφάλμα 404', 'spek-theme'); ?></span>

                <div class="spek-404__number" aria-hidden="true">
                    <span>4</span>
                    <span class="spek-404__zero">
                        <span class="spek-404__zero-core"></span>
                    </span>
                    <span>4</span>
                </div>

                <h1 id="spek-404-title"><?php esc_html_e('Η σελίδα που ψάχνετε δεν βρίσκεται εδώ.', 'spek-theme'); ?></h1>

                <p class="spek-404__lead">
                    <?php esc_html_e('Ίσως η διεύθυνση άλλαξε ή ο σύνδεσμος δεν είναι πλέον διαθέσιμος. Μπορείτε να επιστρέψετε στην αρχική ή να συνεχίσετε στον κατάλογο προϊόντων.', 'spek-theme'); ?>
                </p>

                <div class="spek-404__actions">
                    <a class="button button-primary" href="<?php echo esc_url(spek_page_url('')); ?>">
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M3 11.5 12 4l9 7.5v8a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php esc_html_e('Επιστροφή στην αρχική', 'spek-theme'); ?>
                    </a>

                    <a class="button button-secondary" href="<?php echo esc_url($products_url); ?>">
                        <?php esc_html_e('Δείτε τα προϊόντα', 'spek-theme'); ?>
                        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M5 12h14M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                </div>
            </div>

            <div class="spek-404__visual" aria-hidden="true">
                <div class="spek-404__orb spek-404__orb--one"></div>
                <div class="spek-404__orb spek-404__orb--two"></div>
                <div class="spek-404__plate">
                    <span class="spek-404__plate-line spek-404__plate-line--one"></span>
                    <span class="spek-404__plate-line spek-404__plate-line--two"></span>
                    <span class="spek-404__plate-line spek-404__plate-line--three"></span>
                    <span class="spek-404__plate-dot"></span>
                    <span class="spek-404__plate-label">SPEK</span>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer();
