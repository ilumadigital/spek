<?php
/**
 * Template Name: Contact
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$phone_display = '+30 22620 75000';
$phone_href    = '+302262075000';
$email         = 'info@spek.gr';
$address       = __('Θέση Αχλαδιά Τόλια, Βαθύ Αυλίδος, Ευβοίας 32009, Τ.Θ. 159, Ελλάδα', 'spek-theme');
$maps_url      = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($address);
$contact_image = get_template_directory_uri() . '/assets/images/iluma/webp/spek-ready-website1-16-9.webp';

$product_mail    = 'mailto:' . $email . '?subject=' . rawurlencode('SPEK - Product & Technical Support');
$commercial_mail = 'mailto:' . $email . '?subject=' . rawurlencode('SPEK - Commercial & B2B Cooperation');

get_header();
?>

<main id="main" class="site-main contact-page contact-hub-page">

    <section class="contact-hub-hero">
        <div class="container contact-hub-hero__grid">

            <div class="contact-hub-hero__intro" data-reveal>
                <span class="eyebrow"><?php esc_html_e('SPEK / Contact', 'spek-theme'); ?></span>

                <h1><?php esc_html_e('Επικοινωνία με τη SPEK.', 'spek-theme'); ?></h1>

                <p>
                    <?php esc_html_e('Επιλέξτε τον σωστό τρόπο επικοινωνίας ανάλογα με το αίτημά σας. Για τεχνικά θέματα, εμπορική συνεργασία, δίκτυο συνεργατών ή εταιρικές πληροφορίες.', 'spek-theme'); ?>
                </p>

                <div class="contact-hub-hero__signature" aria-hidden="true">
                    <span>SPEK</span>
                    <i></i>
                    <small>Since 1990</small>
                </div>
            </div>

            <div class="contact-hub-directory" data-reveal style="--reveal-delay: 100ms;">
                <div class="contact-hub-directory__head">
                    <span><?php esc_html_e('Contact Directory', 'spek-theme'); ?></span>
                    <h2><?php esc_html_e('Πώς μπορούμε να βοηθήσουμε;', 'spek-theme'); ?></h2>
                </div>

                <div class="contact-hub-directory__list">

                    <a class="contact-route contact-route--primary" href="<?php echo esc_url($product_mail); ?>">
                        <span class="contact-route__number">01</span>
                        <div class="contact-route__copy">
                            <small><?php esc_html_e('Product Support', 'spek-theme'); ?></small>
                            <strong><?php esc_html_e('Προϊόντα & Τεχνική Υποστήριξη', 'spek-theme'); ?></strong>
                            <p><?php esc_html_e('Κωδικοί, εφαρμογές και τεχνικές πληροφορίες για τα προϊόντα SPEK.', 'spek-theme'); ?></p>
                        </div>
                        <span class="contact-route__action"><?php esc_html_e('Αποστολή email', 'spek-theme'); ?> ↗</span>
                    </a>

                    <a class="contact-route" href="<?php echo esc_url($commercial_mail); ?>">
                        <span class="contact-route__number">02</span>
                        <div class="contact-route__copy">
                            <small><?php esc_html_e('Commercial', 'spek-theme'); ?></small>
                            <strong><?php esc_html_e('Εμπορική & B2B Συνεργασία', 'spek-theme'); ?></strong>
                            <p><?php esc_html_e('Διαθεσιμότητα, εμπορικά αιτήματα και νέες επαγγελματικές συνεργασίες.', 'spek-theme'); ?></p>
                        </div>
                        <span class="contact-route__action"><?php esc_html_e('Επικοινωνία', 'spek-theme'); ?> ↗</span>
                    </a>

                    <a class="contact-route" href="<?php echo esc_url(spek_page_url('partners/')); ?>">
                        <span class="contact-route__number">03</span>
                        <div class="contact-route__copy">
                            <small><?php esc_html_e('Partner Network', 'spek-theme'); ?></small>
                            <strong><?php esc_html_e('Δίκτυο Συνεργατών', 'spek-theme'); ?></strong>
                            <p><?php esc_html_e('Βρείτε συνεργάτη ή σημείο πώλησης SPEK ανά περιοχή.', 'spek-theme'); ?></p>
                        </div>
                        <span class="contact-route__action"><?php esc_html_e('Εύρεση συνεργάτη', 'spek-theme'); ?> →</span>
                    </a>

                    <a class="contact-route" href="<?php echo esc_url(spek_page_url('company-details/')); ?>">
                        <span class="contact-route__number">04</span>
                        <div class="contact-route__copy">
                            <small><?php esc_html_e('Corporate', 'spek-theme'); ?></small>
                            <strong><?php esc_html_e('Εταιρικές Πληροφορίες', 'spek-theme'); ?></strong>
                            <p><?php esc_html_e('Επίσημα στοιχεία εταιρείας για επαγγελματική και εταιρική χρήση.', 'spek-theme'); ?></p>
                        </div>
                        <span class="contact-route__action"><?php esc_html_e('Προβολή στοιχείων', 'spek-theme'); ?> →</span>
                    </a>

                </div>
            </div>

        </div>
    </section>

    <section class="contact-direct">
        <div class="container">
            <div class="contact-direct__bar" data-reveal>
                <div class="contact-direct__heading">
                    <span class="eyebrow"><?php esc_html_e('Direct Contact', 'spek-theme'); ?></span>
                    <h2><?php esc_html_e('Άμεση επικοινωνία.', 'spek-theme'); ?></h2>
                </div>

                <div class="contact-direct__links">
                    <a href="tel:<?php echo esc_attr($phone_href); ?>">
                        <small><?php esc_html_e('Τηλέφωνο', 'spek-theme'); ?></small>
                        <strong><?php echo esc_html($phone_display); ?></strong>
                        <span><?php esc_html_e('Κλήση', 'spek-theme'); ?> ↗</span>
                    </a>

                    <a href="mailto:<?php echo esc_attr($email); ?>">
                        <small><?php esc_html_e('Email', 'spek-theme'); ?></small>
                        <strong><?php echo esc_html($email); ?></strong>
                        <span><?php esc_html_e('Αποστολή email', 'spek-theme'); ?> ↗</span>
                    </a>

                    <a href="<?php echo esc_url($maps_url); ?>" target="_blank" rel="noopener">
                        <small><?php esc_html_e('Έδρα', 'spek-theme'); ?></small>
                        <strong><?php esc_html_e('Σχηματάρι, Ελλάδα', 'spek-theme'); ?></strong>
                        <span><?php esc_html_e('Οδηγίες πρόσβασης', 'spek-theme'); ?> ↗</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-hub-location">
        <div class="contact-hub-location__media" data-reveal>
            <img
                src="<?php echo esc_url($contact_image); ?>"
                alt="<?php esc_attr_e('Παραγωγική βάση SPEK στο Σχηματάρι', 'spek-theme'); ?>"
                loading="lazy"
                decoding="async"
            >
        </div>

        <div class="contact-hub-location__content" data-reveal style="--reveal-delay: 100ms;">
            <span class="eyebrow"><?php esc_html_e('SPEK · Schimatari', 'spek-theme'); ?></span>
            <h2><?php esc_html_e('Η SPEK στο Σχηματάρι.', 'spek-theme'); ?></h2>
            <p><?php esc_html_e('Η παραγωγική βάση και η εταιρική λειτουργία της SPEK συγκεντρώνονται σε ιδιόκτητες εγκαταστάσεις στο Σχηματάρι.', 'spek-theme'); ?></p>
            <address><?php echo esc_html($address); ?></address>

            <div class="contact-hub-location__actions">
                <a class="button button-light" href="<?php echo esc_url($maps_url); ?>" target="_blank" rel="noopener">
                    <?php esc_html_e('Προβολή στον χάρτη', 'spek-theme'); ?>
                </a>
                <a class="button button-light" href="<?php echo esc_url(spek_page_url('company/')); ?>">
                    <?php esc_html_e('Η εταιρεία', 'spek-theme'); ?>
                </a>
            </div>
        </div>
    </section>

</main>

<?php
get_footer();
