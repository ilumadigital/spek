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
$address       = __('Θέση Αχλαδιά Τόλια - Δ.Δ. Βαθύ Δήμου Αυλίδος Ευβοίας, ΤΚ 32009 - Σχηματάρι, Τ.Θ. 159', 'spek-theme');
$maps_url      = 'https://maps.app.goo.gl/QnfrJtBAXDYHMEeD8';
$contact_image = get_template_directory_uri() . '/assets/images/spek-website-new-factory.png';
$form_status   = isset($_GET['contact_status'])
    ? sanitize_key(wp_unslash((string) $_GET['contact_status']))
    : '';

$product_id = isset($_GET['product_id'])
    ? absint($_GET['product_id'])
    : 0;
$product_interest = '';
$product_interest_code = '';

if ($product_id > 0 && get_post_type($product_id) === 'spek_product') {
    $product_interest = get_the_title($product_id);

    $resolved_code = function_exists('spek_get_product_field')
        ? spek_get_product_field('product_code', $product_id)
        : get_post_meta($product_id, 'product_code', true);

    if (function_exists('spek_product_value_is_empty') && !spek_product_value_is_empty($resolved_code)) {
        $product_interest_code = function_exists('spek_product_value_to_text')
            ? spek_product_value_to_text($resolved_code)
            : (string) $resolved_code;
    }
} else {
    $product_interest = isset($_GET['product'])
        ? sanitize_text_field(wp_unslash((string) $_GET['product']))
        : '';
    $product_interest_code = isset($_GET['product_code'])
        ? sanitize_text_field(wp_unslash((string) $_GET['product_code']))
        : '';
}

get_header();
?>

<main id="main" class="site-main contact-page contact-hub-page contact-form-page">

    <section class="contact-hub-hero">
        <div class="container contact-hub-hero__grid contact-hub-hero__grid--contact-form">

            <div class="contact-hub-hero__intro" data-reveal>
                <span class="eyebrow spek-brand-eyebrow">
                    <img
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                        alt="SPEK"
                        class="spek-brand-logo"
                    >
                    <span><?php esc_html_e('/ Επικοινωνία', 'spek-theme'); ?></span>
                </span>

                <h1><?php esc_html_e('Επικοινωνία με τη SPEK.', 'spek-theme'); ?></h1>

                <p>
                    <?php esc_html_e('Επιλέξτε τον σωστό τρόπο επικοινωνίας ανάλογα με το αίτημά σας. Για τεχνικά θέματα, εμπορική συνεργασία, δίκτυο συνεργατών ή εταιρικές πληροφορίες.', 'spek-theme'); ?>
                </p>

                <div class="contact-hub-hero__signature" aria-hidden="true">
                    <img
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                        alt=""
                        class="spek-brand-logo"
                    >
                    <i></i>
                    <small><?php esc_html_e('Από το 1990', 'spek-theme'); ?></small>
                </div>
            </div>

            <aside class="contact-phone-card" data-reveal style="--reveal-delay: 100ms;">
                <span class="eyebrow"><?php esc_html_e('Άμεση επικοινωνία', 'spek-theme'); ?></span>
                <h2><?php esc_html_e('Άμεση επικοινωνία.', 'spek-theme'); ?></h2>

                <a class="contact-phone-card__link" href="tel:<?php echo esc_attr($phone_href); ?>">
                    <small><?php esc_html_e('Τηλέφωνο', 'spek-theme'); ?></small>
                    <strong><?php echo esc_html($phone_display); ?></strong>
                    <span><?php esc_html_e('Κλήση', 'spek-theme'); ?> ↗</span>
                </a>
            </aside>

        </div>
    </section>

    <section class="contact-form-section" id="contact-form">
        <div class="container">

            <div class="contact-form-section__intro" data-reveal>
                <div>
                    <span class="eyebrow"><?php esc_html_e('Φόρμα επικοινωνίας', 'spek-theme'); ?></span>
                    <h2><?php esc_html_e('Στείλτε μας μήνυμα', 'spek-theme'); ?></h2>
                </div>

                <p>
                    <?php esc_html_e('Επιλέξτε τον τύπο του αιτήματος και συμπληρώστε όλα τα πεδία.', 'spek-theme'); ?>
                </p>
            </div>

            <div class="contact-form-card contact-form-card--premium contact-form-card--dynamic" data-reveal style="--reveal-delay: 80ms;">

                <?php if ($form_status === 'success') : ?>
                    <div class="contact-form-notice contact-form-notice--success" role="status">
                        <?php esc_html_e('Το μήνυμά σας στάλθηκε επιτυχώς. Η ομάδα της SPEK θα επικοινωνήσει μαζί σας.', 'spek-theme'); ?>
                    </div>
                <?php elseif (in_array($form_status, ['invalid', 'error'], true)) : ?>
                    <div class="contact-form-notice contact-form-notice--error" role="alert">
                        <?php esc_html_e('Δεν ήταν δυνατή η αποστολή. Ελέγξτε ότι όλα τα πεδία είναι σωστά συμπληρωμένα.', 'spek-theme'); ?>
                    </div>
                <?php endif; ?>

                <form
                    class="spek-contact-form"
                    action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                    method="post"
                    data-contact-form
                >
                    <input type="hidden" name="action" value="spek_contact_submit">
                    <input
                        type="hidden"
                        name="contact_lang"
                        value="<?php echo esc_attr(function_exists('spek_current_language') ? spek_current_language() : 'el'); ?>"
                    >
                    <?php wp_nonce_field('spek_contact_submit', 'spek_contact_nonce'); ?>

                    <div class="spek-contact-hp" aria-hidden="true">
                        <label for="contact-website">Website</label>
                        <input
                            id="contact-website"
                            type="text"
                            name="website"
                            value=""
                            tabindex="-1"
                            autocomplete="off"
                        >
                    </div>

                    <?php if ($product_interest !== '') : ?>
                        <div class="contact-product-context" aria-label="<?php esc_attr_e('Προϊόν ενδιαφέροντος', 'spek-theme'); ?>">
                            <div class="contact-product-context__meta">
                                <span class="contact-product-context__eyebrow"><?php esc_html_e('Προϊόν ενδιαφέροντος', 'spek-theme'); ?></span>
                                <?php if ($product_interest_code !== '') : ?>
                                    <span class="contact-product-context__code">
                                        <?php esc_html_e('Κωδικός', 'spek-theme'); ?>:
                                        <strong><?php echo esc_html($product_interest_code); ?></strong>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="form-row form-row--full contact-product-context__field">
                                <label for="contact-product-interest"><?php esc_html_e('Προϊόν', 'spek-theme'); ?></label>
                                <input
                                    id="contact-product-interest"
                                    type="text"
                                    name="product_interest"
                                    value="<?php echo esc_attr($product_interest); ?>"
                                    readonly
                                >
                            </div>

                            <input type="hidden" name="product_id" value="<?php echo esc_attr((string) $product_id); ?>">
                            <input type="hidden" name="product_code" value="<?php echo esc_attr($product_interest_code); ?>">
                        </div>
                    <?php endif; ?>

                    <fieldset class="contact-request-type">
                        <legend><?php esc_html_e('Τύπος αιτήματος', 'spek-theme'); ?></legend>

                        <div class="contact-request-options">
                            <label class="contact-request-option">
                                <input type="radio" name="request_type" value="general" required>
                                <span class="contact-request-option__surface">
                                    <span class="contact-request-option__index">A1</span>
                                    <strong><?php esc_html_e('Γενικό Αίτημα', 'spek-theme'); ?></strong>
                                    <span class="contact-request-option__check" aria-hidden="true">✓</span>
                                </span>
                            </label>

                            <label class="contact-request-option">
                                <input type="radio" name="request_type" value="partner" required>
                                <span class="contact-request-option__surface">
                                    <span class="contact-request-option__index">A2</span>
                                    <strong><?php esc_html_e('Είμαι συνεργάτης', 'spek-theme'); ?></strong>
                                    <span class="contact-request-option__check" aria-hidden="true">✓</span>
                                </span>
                            </label>
                        </div>
                    </fieldset>

                    <div class="contact-form-fields" data-contact-fields>
                        <div class="contact-form-grid">

                            <div class="form-row">
                                <label for="contact-first-name"><?php esc_html_e('Όνομα', 'spek-theme'); ?></label>
                                <input
                                    id="contact-first-name"
                                    type="text"
                                    name="first_name"
                                    maxlength="100"
                                    autocomplete="given-name"
                                    required
                                >
                            </div>

                            <div class="form-row">
                                <label for="contact-last-name"><?php esc_html_e('Επώνυμο', 'spek-theme'); ?></label>
                                <input
                                    id="contact-last-name"
                                    type="text"
                                    name="last_name"
                                    maxlength="100"
                                    autocomplete="family-name"
                                    required
                                >
                            </div>

                            <div class="form-row">
                                <label for="contact-email"><?php esc_html_e('Ηλ. ταχυδρομείο', 'spek-theme'); ?></label>
                                <input
                                    id="contact-email"
                                    type="email"
                                    name="email"
                                    maxlength="190"
                                    autocomplete="email"
                                    required
                                >
                            </div>

                            <div class="form-row">
                                <label for="contact-phone"><?php esc_html_e('Τηλέφωνο', 'spek-theme'); ?></label>
                                <input
                                    id="contact-phone"
                                    type="tel"
                                    name="phone"
                                    maxlength="50"
                                    autocomplete="tel"
                                    required
                                >
                            </div>

                            <div class="form-row form-row--full">
                                <label for="contact-subject"><?php esc_html_e('Θέμα', 'spek-theme'); ?></label>
                                <input
                                    id="contact-subject"
                                    type="text"
                                    name="subject"
                                    maxlength="200"
                                    value="<?php echo esc_attr($product_interest !== '' ? sprintf(__('Ενδιαφέρον για προϊόν: %s', 'spek-theme'), $product_interest) : ''); ?>"
                                    required
                                >
                            </div>

                            <div class="form-row form-row--full">
                                <label for="contact-message"><?php esc_html_e('Μήνυμα', 'spek-theme'); ?></label>
                                <textarea
                                    id="contact-message"
                                    name="message"
                                    maxlength="3000"
                                    required
                                ></textarea>

                                <div class="contact-message-meta">
                                    <small><?php esc_html_e('Έως 3000 χαρακτήρες', 'spek-theme'); ?></small>
                                    <span class="contact-character-counter" data-contact-counter>0 / 3000</span>
                                </div>
                            </div>

                        </div>

                        <div class="contact-form-card__footer">
                            <p><?php esc_html_e('Όλα τα πεδία είναι υποχρεωτικά.', 'spek-theme'); ?></p>

                            <button class="button button-primary" type="submit">
                                <?php esc_html_e('Αποστολή αιτήματος', 'spek-theme'); ?>
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </section>

    <section class="contact-careers-section" aria-labelledby="contact-careers-title">
        <div class="container">
            <div class="contact-careers-card" data-reveal>
                <div class="contact-careers-card__content">
                    <span class="eyebrow"><?php esc_html_e('Καριέρα', 'spek-theme'); ?></span>

                    <h2 id="contact-careers-title">
                        <?php esc_html_e('Θέλετε να γίνετε μέλος της ομάδας μας;', 'spek-theme'); ?>
                    </h2>

                    <p>
                        <?php esc_html_e('Αναζητούμε ανθρώπους με συνέπεια, διάθεση για εξέλιξη και αγάπη για τη σωστή δουλειά. Δείτε τις διαθέσιμες θέσεις και γνωρίστε τις ευκαιρίες καριέρας στη SPEK.', 'spek-theme'); ?>
                    </p>
                </div>

                <div class="contact-careers-card__action">
                    <a class="button button-light" href="<?php echo esc_url(spek_page_url('careers/')); ?>">
                        <?php esc_html_e('Δείτε τις ανοιχτές θέσεις', 'spek-theme'); ?>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-hub-location">
        <div class="contact-hub-location__media" data-reveal>
            <img
                src="<?php echo esc_url($contact_image); ?>"
                alt="<?php esc_attr_e('Παραγωγική βάση SPEK στην Σχηματάρι', 'spek-theme'); ?>"
                loading="lazy"
                decoding="async"
            >
        </div>

        <div class="contact-hub-location__content" data-reveal style="--reveal-delay: 100ms;">
            <span class="eyebrow spek-brand-eyebrow spek-brand-eyebrow--inverse">
                <img
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                    alt="SPEK"
                    class="spek-brand-logo spek-brand-logo--inverse"
                >
                <span><?php esc_html_e('· Σχηματάρι', 'spek-theme'); ?></span>
            </span>
            <h2><?php esc_html_e('Η SPEK στην Σχηματάρι.', 'spek-theme'); ?></h2>
            <p><?php esc_html_e('Η παραγωγική βάση και η εταιρική λειτουργία της SPEK συγκεντρώνονται σε ιδιόκτητες εγκαταστάσεις στην Σχηματάρι.', 'spek-theme'); ?></p>
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
