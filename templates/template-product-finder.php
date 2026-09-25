<?php
/**
 * Template Name: Product Finder
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main" class="site-main product-finder-page product-finder-v2">

    <section class="finder-hero">
        <div class="container finder-hero__grid">
            <div class="finder-hero__content" data-reveal>
                <span class="eyebrow spek-brand-eyebrow">
                    <img
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                        alt="SPEK"
                        class="spek-brand-logo"
                    >
                    <span><?php esc_html_e('Εύρεση Προϊόντος', 'spek-theme'); ?></span>
                </span>
                <h1><?php esc_html_e('Βρείτε το κατάλληλο προϊόν.', 'spek-theme'); ?></h1>
                <p><?php esc_html_e('Ένας γρήγορος οδηγός επιλογής για επαγγελματίες. Ορίστε κατηγορία, εφαρμογή και προαιρετικά τεχνικά χαρακτηριστικά.', 'spek-theme'); ?></p>
            </div>

            <div class="finder-hero__meta" data-reveal style="--reveal-delay: 100ms;">
                <div><strong>01</strong><span><?php esc_html_e('Κατηγορία', 'spek-theme'); ?></span></div>
                <i aria-hidden="true"></i>
                <div><strong>02</strong><span><?php esc_html_e('Εφαρμογή', 'spek-theme'); ?></span></div>
                <i aria-hidden="true"></i>
                <div><strong>03</strong><span><?php esc_html_e('Χαρακτηριστικά', 'spek-theme'); ?></span></div>
                <i aria-hidden="true"></i>
                <div><strong>04</strong><span><?php esc_html_e('Αποτελέσματα', 'spek-theme'); ?></span></div>
            </div>
        </div>
    </section>

    <section class="finder-app finder-app--v2" id="spek-product-finder">
        <div class="container">
            <div class="spek-finder spek-finder--v2" data-current-step="start">

                <aside class="spek-finder-nav" aria-label="<?php esc_attr_e('Βήματα εύρεσης προϊόντος', 'spek-theme'); ?>">
                    <div class="spek-finder-nav__brand">
                        <img
                            src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                            alt="SPEK"
                            class="spek-brand-logo spek-brand-logo--inverse"
                        >
                        <small><?php esc_html_e('Επιλογή Προϊόντος', 'spek-theme'); ?></small>
                    </div>

                    <div class="spek-finder__progress spek-finder__progress--rail">
                        <span data-progress-step="category">
                            <i>01</i><b><?php esc_html_e('Κατηγορία', 'spek-theme'); ?></b>
                            <small data-summary-category><?php esc_html_e('Δεν έχει επιλεγεί', 'spek-theme'); ?></small>
                        </span>
                        <span data-progress-step="application">
                            <i>02</i><b><?php esc_html_e('Εφαρμογή', 'spek-theme'); ?></b>
                            <small data-summary-application><?php esc_html_e('Δεν έχει επιλεγεί', 'spek-theme'); ?></small>
                        </span>
                        <span data-progress-step="specs">
                            <i>03</i><b><?php esc_html_e('Χαρακτηριστικά', 'spek-theme'); ?></b>
                            <small data-summary-specs><?php esc_html_e('Προαιρετικά', 'spek-theme'); ?></small>
                        </span>
                        <span data-progress-step="results">
                            <i>04</i><b><?php esc_html_e('Αποτελέσματα', 'spek-theme'); ?></b>
                            <small><?php esc_html_e('Προτεινόμενα προϊόντα', 'spek-theme'); ?></small>
                        </span>
                    </div>

                    <div class="spek-finder-nav__help">
                        <span><?php esc_html_e('Χρειάζεστε βοήθεια;', 'spek-theme'); ?></span>
                        <p><?php esc_html_e('Για τεχνική καθοδήγηση μπορείτε να επικοινωνήσετε με την ομάδα SPEK.', 'spek-theme'); ?></p>
                        <a href="<?php echo esc_url(spek_page_url('contact/')); ?>">
                            <?php esc_html_e('Επικοινωνία', 'spek-theme'); ?> <i aria-hidden="true">↗</i>
                        </a>
                    </div>
                </aside>

                <div class="spek-finder-workspace">
                    <div class="spek-finder-mobile-progress" aria-hidden="true">
                        <span data-mobile-step-label><?php esc_html_e('Έναρξη', 'spek-theme'); ?></span>
                        <div><i data-mobile-progress-bar></i></div>
                        <strong data-mobile-step-count>0 / 4</strong>
                    </div>

                    <?php get_template_part('template-parts/finder/finder-start'); ?>
                    <?php get_template_part('template-parts/finder/finder-step-category'); ?>
                    <?php get_template_part('template-parts/finder/finder-step-application'); ?>
                    <?php get_template_part('template-parts/finder/finder-step-specs'); ?>
                    <?php get_template_part('template-parts/finder/finder-results'); ?>
                </div>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
