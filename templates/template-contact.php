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

$contact_status = isset($_GET['contact']) ? sanitize_key(wp_unslash($_GET['contact'])) : '';

$inquiry_types = [
    'product'     => __('Πληροφορίες προϊόντων', 'spek-theme'),
    'technical'   => __('Τεχνική πληροφόρηση', 'spek-theme'),
    'commercial'  => __('Εμπορική συνεργασία', 'spek-theme'),
    'partners'    => __('Δίκτυο συνεργατών / σημεία πώλησης', 'spek-theme'),
    'other'       => __('Άλλο αίτημα', 'spek-theme'),
];

get_header();
?>

<main id="main" class="site-main contact-page">

    <section class="contact-hero">
        <div class="container contact-hero__grid">
            <div class="contact-hero__content" data-reveal>
                <span class="eyebrow"><?php esc_html_e('SPEK / B2B Contact', 'spek-theme'); ?></span>

                <h1><?php esc_html_e('Επικοινωνία με τη SPEK.', 'spek-theme'); ?></h1>

                <p class="contact-hero__lead">
                    <?php esc_html_e('Για προϊόντα, τεχνικές πληροφορίες, διαθεσιμότητα και εμπορικές συνεργασίες, επικοινωνήστε απευθείας με την ομάδα μας.', 'spek-theme'); ?>
                </p>

                <div class="contact-hero__quick">
                    <a class="contact-quick-link" href="tel:<?php echo esc_attr($phone_href); ?>">
                        <span><?php esc_html_e('Τηλέφωνο', 'spek-theme'); ?></span>
                        <strong><?php echo esc_html($phone_display); ?></strong>
                        <i aria-hidden="true">↗</i>
                    </a>

                    <a class="contact-quick-link" href="mailto:<?php echo esc_attr($email); ?>">
                        <span><?php esc_html_e('Email', 'spek-theme'); ?></span>
                        <strong><?php echo esc_html($email); ?></strong>
                        <i aria-hidden="true">↗</i>
                    </a>
                </div>
            </div>

            <figure class="contact-hero__visual" data-reveal style="--reveal-delay: 120ms;">
                <img
                    src="<?php echo esc_url($contact_image); ?>"
                    alt="<?php esc_attr_e('Οι εγκαταστάσεις της SPEK στο Σχηματάρι', 'spek-theme'); ?>"
                    loading="eager"
                    decoding="async"
                >

                <figcaption>
                    <span><?php esc_html_e('Παραγωγή & έδρα', 'spek-theme'); ?></span>
                    <strong><?php esc_html_e('Σχηματάρι · Ελλάδα', 'spek-theme'); ?></strong>
                </figcaption>
            </figure>
        </div>
    </section>

    <section class="section contact-main">
        <div class="container">

            <div class="contact-main__intro" data-reveal>
                <div>
                    <span class="eyebrow"><?php esc_html_e('Business Contact', 'spek-theme'); ?></span>
                    <h2><?php esc_html_e('Μιλήστε με την κατάλληλη ομάδα.', 'spek-theme'); ?></h2>
                </div>

                <p>
                    <?php esc_html_e('Στείλτε το αίτημά σας με τα βασικά στοιχεία που χρειαζόμαστε. Έτσι μπορούμε να το προωθήσουμε άμεσα στο κατάλληλο τμήμα.', 'spek-theme'); ?>
                </p>
            </div>

            <div class="contact-layout">

                <aside class="contact-details" data-reveal>
                    <div class="contact-details__head">
                        <span><?php esc_html_e('SPEK Μεταλλοπλαστική Α.Β.Ε.Ε.', 'spek-theme'); ?></span>
                        <h2><?php esc_html_e('Στοιχεία επικοινωνίας', 'spek-theme'); ?></h2>
                    </div>

                    <div class="contact-details__list">
                        <a class="contact-detail-row" href="tel:<?php echo esc_attr($phone_href); ?>">
                            <span class="contact-detail-row__index">01</span>
                            <div>
                                <small><?php esc_html_e('Τηλέφωνο', 'spek-theme'); ?></small>
                                <strong><?php echo esc_html($phone_display); ?></strong>
                            </div>
                            <i aria-hidden="true">↗</i>
                        </a>

                        <a class="contact-detail-row" href="mailto:<?php echo esc_attr($email); ?>">
                            <span class="contact-detail-row__index">02</span>
                            <div>
                                <small><?php esc_html_e('Email', 'spek-theme'); ?></small>
                                <strong><?php echo esc_html($email); ?></strong>
                            </div>
                            <i aria-hidden="true">↗</i>
                        </a>

                        <a class="contact-detail-row contact-detail-row--address" href="<?php echo esc_url($maps_url); ?>" target="_blank" rel="noopener">
                            <span class="contact-detail-row__index">03</span>
                            <div>
                                <small><?php esc_html_e('Έδρα / Παραγωγική βάση', 'spek-theme'); ?></small>
                                <strong><?php echo esc_html($address); ?></strong>
                            </div>
                            <i aria-hidden="true">↗</i>
                        </a>
                    </div>

                    <div class="contact-details__services">
                        <span><?php esc_html_e('Εξυπηρετούμε αιτήματα για', 'spek-theme'); ?></span>
                        <ul>
                            <li><?php esc_html_e('Προϊόντα & τεχνικές πληροφορίες', 'spek-theme'); ?></li>
                            <li><?php esc_html_e('Διαθεσιμότητα & εμπορικά αιτήματα', 'spek-theme'); ?></li>
                            <li><?php esc_html_e('B2B συνεργασίες & δίκτυο συνεργατών', 'spek-theme'); ?></li>
                        </ul>
                    </div>

                    <a class="contact-details__corporate" href="<?php echo esc_url(spek_page_url('company-details/')); ?>">
                        <span>
                            <small><?php esc_html_e('Corporate Identity', 'spek-theme'); ?></small>
                            <strong><?php esc_html_e('Επίσημα εταιρικά στοιχεία', 'spek-theme'); ?></strong>
                        </span>
                        <i aria-hidden="true">→</i>
                    </a>
                </aside>

                <div class="contact-form-card contact-form-card--premium" data-reveal style="--reveal-delay: 100ms;">
                    <div class="contact-form-card__head">
                        <span class="eyebrow"><?php esc_html_e('Contact Form', 'spek-theme'); ?></span>
                        <h2><?php esc_html_e('Στείλτε το αίτημά σας.', 'spek-theme'); ?></h2>
                        <p><?php esc_html_e('Συμπληρώστε τα στοιχεία σας και το θέμα επικοινωνίας.', 'spek-theme'); ?></p>
                    </div>

                    <?php if ($contact_status === 'success') : ?>
                        <div class="contact-form-notice contact-form-notice--success" role="status">
                            <?php esc_html_e('Το μήνυμά σας στάλθηκε. Η ομάδα της SPEK θα επικοινωνήσει μαζί σας.', 'spek-theme'); ?>
                        </div>
                    <?php elseif ($contact_status === 'error') : ?>
                        <div class="contact-form-notice contact-form-notice--error" role="alert">
                            <?php esc_html_e('Δεν ήταν δυνατή η αποστολή. Ελέγξτε τα στοιχεία σας ή επικοινωνήστε μαζί μας τηλεφωνικά ή μέσω email.', 'spek-theme'); ?>
                        </div>
                    <?php endif; ?>

                    <form class="spek-form spek-contact-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                        <input type="hidden" name="action" value="spek_contact_submit">
                        <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink()); ?>">
                        <?php wp_nonce_field('spek_contact_submit', 'spek_contact_nonce'); ?>

                        <div class="spek-contact-hp" aria-hidden="true">
                            <label for="website">Website</label>
                            <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="contact-form-grid">
                            <div class="form-row">
                                <label for="contact-name"><?php esc_html_e('Ονοματεπώνυμο', 'spek-theme'); ?> *</label>
                                <input id="contact-name" type="text" name="name" autocomplete="name" required>
                            </div>

                            <div class="form-row">
                                <label for="contact-company"><?php esc_html_e('Εταιρεία / Οργανισμός', 'spek-theme'); ?></label>
                                <input id="contact-company" type="text" name="company" autocomplete="organization">
                            </div>

                            <div class="form-row">
                                <label for="contact-email"><?php esc_html_e('Email', 'spek-theme'); ?> *</label>
                                <input id="contact-email" type="email" name="email" autocomplete="email" required>
                            </div>

                            <div class="form-row">
                                <label for="contact-phone"><?php esc_html_e('Τηλέφωνο', 'spek-theme'); ?></label>
                                <input id="contact-phone" type="tel" name="phone" autocomplete="tel">
                            </div>

                            <div class="form-row form-row--full">
                                <label for="contact-topic"><?php esc_html_e('Θέμα επικοινωνίας', 'spek-theme'); ?> *</label>
                                <select id="contact-topic" name="topic" required>
                                    <option value=""><?php esc_html_e('Επιλέξτε θέμα', 'spek-theme'); ?></option>
                                    <?php foreach ($inquiry_types as $value => $label) : ?>
                                        <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-row form-row--full">
                                <label for="contact-message"><?php esc_html_e('Μήνυμα', 'spek-theme'); ?> *</label>
                                <textarea id="contact-message" name="message" rows="6" required></textarea>
                            </div>
                        </div>

                        <label class="contact-consent">
                            <input type="checkbox" name="privacy" value="1" required>
                            <span>
                                <?php
                                printf(
                                    wp_kses(
                                        __('Έχω διαβάσει και αποδέχομαι την <a href="%s">Πολιτική Απορρήτου</a>.', 'spek-theme'),
                                        ['a' => ['href' => []]]
                                    ),
                                    esc_url(spek_page_url('privacy-policy/'))
                                );
                                ?>
                            </span>
                        </label>

                        <div class="contact-form-card__footer">
                            <p><?php esc_html_e('Τα στοιχεία χρησιμοποιούνται αποκλειστικά για την απάντηση στο αίτημά σας.', 'spek-theme'); ?></p>

                            <button type="submit" class="button button-primary">
                                <?php esc_html_e('Αποστολή αιτήματος', 'spek-theme'); ?>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </section>

    <section class="contact-location" aria-label="<?php esc_attr_e('SPEK Σχηματάρι', 'spek-theme'); ?>">
        <div class="contact-location__media" data-reveal>
            <img
                src="<?php echo esc_url($contact_image); ?>"
                alt="<?php esc_attr_e('Παραγωγική βάση SPEK στο Σχηματάρι', 'spek-theme'); ?>"
                loading="lazy"
                decoding="async"
            >
        </div>

        <div class="contact-location__content" data-reveal style="--reveal-delay: 100ms;">
            <span class="eyebrow"><?php esc_html_e('SPEK · Schimatari', 'spek-theme'); ?></span>
            <h2><?php esc_html_e('Παραγωγή, εταιρική λειτουργία και υποστήριξη σε μία οργανωμένη βάση.', 'spek-theme'); ?></h2>
            <p><?php echo esc_html($address); ?></p>

            <div class="contact-location__actions">
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
