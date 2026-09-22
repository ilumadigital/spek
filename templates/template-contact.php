<?php
/**
 * Template Name: Contact
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="main" class="site-main contact-page">

    <section class="page-hero">
        <div class="container">
            <span class="eyebrow"><?php esc_html_e('Contact', 'spek-theme'); ?></span>
            <h1><?php esc_html_e('Επικοινωνία', 'spek-theme'); ?></h1>
            <p><?php esc_html_e('Επικοινωνήστε με την ομάδα της SPEK για πληροφορίες προϊόντων, συνεργασίες ή εμπορικά αιτήματα.', 'spek-theme'); ?></p>
        </div>
    </section>

    <section class="section contact-section">
        <div class="container contact-grid">

            <div class="contact-info-card">
                <h2><?php esc_html_e('Στοιχεία επικοινωνίας', 'spek-theme'); ?></h2>

                <ul class="contact-list">
                    <li>
                        <strong><?php esc_html_e('Τηλέφωνο', 'spek-theme'); ?></strong>
                        <span>+30 000 0000000</span>
                    </li>

                    <li>
                        <strong><?php esc_html_e('Email', 'spek-theme'); ?></strong>
                        <span>info@spek.gr</span>
                    </li>

                    <li>
                        <strong><?php esc_html_e('Διεύθυνση', 'spek-theme'); ?></strong>
                        <span><?php esc_html_e('Συμπληρώστε τη διεύθυνση της εταιρείας.', 'spek-theme'); ?></span>
                    </li>
                </ul>
            </div>

            <div class="contact-form-card">
                <h2><?php esc_html_e('Στείλτε μας μήνυμα', 'spek-theme'); ?></h2>

                <form class="spek-form" action="#" method="post">
                    <div class="form-row">
                        <label for="name"><?php esc_html_e('Ονοματεπώνυμο', 'spek-theme'); ?></label>
                        <input id="name" type="text" name="name">
                    </div>

                    <div class="form-row">
                        <label for="email"><?php esc_html_e('Email', 'spek-theme'); ?></label>
                        <input id="email" type="email" name="email">
                    </div>

                    <div class="form-row">
                        <label for="message"><?php esc_html_e('Μήνυμα', 'spek-theme'); ?></label>
                        <textarea id="message" name="message" rows="5"></textarea>
                    </div>

                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Αποστολή', 'spek-theme'); ?>
                    </button>
                </form>
            </div>

        </div>
    </section>

</main>

<?php
get_footer();