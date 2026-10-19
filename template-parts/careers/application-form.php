<?php
/**
 * Career application form.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$lang = function_exists('spek_current_language') ? spek_current_language() : 'el';
$status = isset($_GET['career_status']) ? sanitize_key(wp_unslash((string) $_GET['career_status'])) : '';
$selected_position = isset($_GET['career_position']) ? absint($_GET['career_position']) : 0;

$positions = get_posts(spek_language_args([
    'post_type' => 'spek_career',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'menu_order title',
    'order' => 'ASC',
]));

$privacy_url = get_privacy_policy_url();
if (!$privacy_url) {
    $privacy_url = spek_page_url('privacy-policy/');
}

$notices = [
    'success' => [
        'class' => 'is-success',
        'title' => __('Η αίτησή σας στάλθηκε.', 'spek-theme'),
        'text' => __('Ευχαριστούμε για το ενδιαφέρον σας. Η ομάδα της SPEK θα εξετάσει τα στοιχεία σας και θα επικοινωνήσει μαζί σας εφόσον υπάρχει κατάλληλη ευκαιρία.', 'spek-theme'),
    ],
    'invalid' => [
        'class' => 'is-error',
        'title' => __('Ελέγξτε τα υποχρεωτικά πεδία.', 'spek-theme'),
        'text' => __('Συμπληρώστε τα στοιχεία επικοινωνίας, τον τύπο απασχόλησης, το βιογραφικό και την αποδοχή της ενημέρωσης απορρήτου.', 'spek-theme'),
    ],
    'upload_error' => [
        'class' => 'is-error',
        'title' => __('Υπήρξε πρόβλημα με τα αρχεία.', 'spek-theme'),
        'text' => __('Χρησιμοποιήστε PDF, DOC ή DOCX και ελέγξτε τα επιτρεπόμενα μεγέθη αρχείων.', 'spek-theme'),
    ],
    'too_many_files' => [
        'class' => 'is-error',
        'title' => __('Πάρα πολλές συστατικές επιστολές.', 'spek-theme'),
        'text' => __('Μπορείτε να επισυνάψετε έως 5 συστατικές επιστολές.', 'spek-theme'),
    ],
    'error' => [
        'class' => 'is-error',
        'title' => __('Δεν ήταν δυνατή η αποστολή της αίτησης.', 'spek-theme'),
        'text' => __('Δοκιμάστε ξανά σε λίγο ή επικοινωνήστε με τη SPEK.', 'spek-theme'),
    ],
];
?>

<section id="career-application" class="career-application" aria-labelledby="career-application-title">
    <div class="career-application__intro">
        <span class="eyebrow"><?php esc_html_e('Αίτηση Καριέρας', 'spek-theme'); ?></span>
        <h2 id="career-application-title"><?php esc_html_e('Στείλτε μας το βιογραφικό σας.', 'spek-theme'); ?></h2>
        <p><?php esc_html_e('Συμπληρώστε τα βασικά στοιχεία που χρειαζόμαστε για την πρώτη αξιολόγηση. Τα προαιρετικά πεδία μπορείτε να τα αφήσετε κενά.', 'spek-theme'); ?></p>
    </div>

    <?php if ($status !== '' && isset($notices[$status])) : ?>
        <div class="career-form-notice <?php echo esc_attr($notices[$status]['class']); ?>" role="status">
            <strong><?php echo esc_html($notices[$status]['title']); ?></strong>
            <p><?php echo esc_html($notices[$status]['text']); ?></p>
        </div>
    <?php endif; ?>

    <form class="career-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="spek_career_apply">
        <input type="hidden" name="career_lang" value="<?php echo esc_attr($lang); ?>">
        <?php wp_nonce_field('spek_career_apply', 'spek_career_nonce'); ?>

        <div class="career-honeypot" aria-hidden="true">
            <label for="career-website">Website</label>
            <input id="career-website" type="text" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="career-form__section">
            <div class="career-form__section-head">
                <span>01</span>
                <div>
                    <h3><?php esc_html_e('Στοιχεία υποψηφίου', 'spek-theme'); ?></h3>
                    <p><?php esc_html_e('Τα στοιχεία επικοινωνίας είναι απαραίτητα για να μπορέσουμε να απαντήσουμε στην αίτησή σας.', 'spek-theme'); ?></p>
                </div>
            </div>

            <div class="career-form__grid">
                <div class="career-field">
                    <label for="career-first-name"><?php esc_html_e('Όνομα', 'spek-theme'); ?> <em>*</em></label>
                    <input id="career-first-name" type="text" name="first_name" autocomplete="given-name" required>
                </div>

                <div class="career-field">
                    <label for="career-last-name"><?php esc_html_e('Επώνυμο', 'spek-theme'); ?> <em>*</em></label>
                    <input id="career-last-name" type="text" name="last_name" autocomplete="family-name" required>
                </div>

                <div class="career-field">
                    <label for="career-email"><?php esc_html_e('Ηλ. ταχυδρομείο', 'spek-theme'); ?> <em>*</em></label>
                    <input id="career-email" type="email" name="email" autocomplete="email" required>
                </div>

                <div class="career-field">
                    <label for="career-phone"><?php esc_html_e('Τηλέφωνο', 'spek-theme'); ?> <em>*</em></label>
                    <input id="career-phone" type="tel" name="phone" autocomplete="tel" required>
                </div>

                <div class="career-field career-field--full">
                    <label for="career-city"><?php esc_html_e('Πόλη / Περιοχή', 'spek-theme'); ?> <small><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></small></label>
                    <input id="career-city" type="text" name="city" autocomplete="address-level2">
                </div>
            </div>
        </div>

        <div class="career-form__section">
            <div class="career-form__section-head">
                <span>02</span>
                <div>
                    <h3><?php esc_html_e('Επαγγελματικό ενδιαφέρον', 'spek-theme'); ?></h3>
                    <p><?php esc_html_e('Ο τύπος απασχόλησης είναι υποχρεωτικός. Η θέση και ο τομέας είναι προαιρετικά.', 'spek-theme'); ?></p>
                </div>
            </div>

            <fieldset class="career-employment">
                <legend><?php esc_html_e('Τύπος απασχόλησης', 'spek-theme'); ?> <em>*</em></legend>
                <div class="career-employment__options">
                    <label>
                        <input type="radio" name="employment_type" value="full_time" required>
                        <span><strong><?php esc_html_e('Πλήρης απασχόληση', 'spek-theme'); ?></strong></span>
                    </label>
                    <label>
                        <input type="radio" name="employment_type" value="part_time" required>
                        <span><strong><?php esc_html_e('Μερική απασχόληση', 'spek-theme'); ?></strong></span>
                    </label>
                </div>
            </fieldset>

            <div class="career-form__grid">
                <div class="career-field">
                    <label for="career-position"><?php esc_html_e('Θέση ενδιαφέροντος', 'spek-theme'); ?> <small><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></small></label>
                    <select id="career-position" name="position_id">
                        <option value="0"><?php esc_html_e('Γενική αίτηση', 'spek-theme'); ?></option>
                        <?php foreach ($positions as $position) : ?>
                            <option value="<?php echo esc_attr((string) $position->ID); ?>" <?php selected($selected_position, $position->ID); ?>>
                                <?php echo esc_html(get_the_title($position)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="career-field">
                    <label for="career-department"><?php esc_html_e('Τομέας', 'spek-theme'); ?> <small><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></small></label>
                    <select id="career-department" name="department">
                        <option value=""><?php esc_html_e('Δεν έχω προτίμηση', 'spek-theme'); ?></option>
                        <option value="production"><?php esc_html_e('Παραγωγή', 'spek-theme'); ?></option>
                        <option value="warehouse_logistics"><?php esc_html_e('Αποθήκη & Εφοδιαστική', 'spek-theme'); ?></option>
                        <option value="technical"><?php esc_html_e('Τεχνικό / Μηχανικό', 'spek-theme'); ?></option>
                        <option value="quality"><?php esc_html_e('Ποιότητα', 'spek-theme'); ?></option>
                        <option value="sales"><?php esc_html_e('Πωλήσεις / Εξυπηρέτηση', 'spek-theme'); ?></option>
                        <option value="administration"><?php esc_html_e('Διοίκηση / Οικονομικά', 'spek-theme'); ?></option>
                        <option value="other"><?php esc_html_e('Άλλο', 'spek-theme'); ?></option>
                    </select>
                </div>

                <div class="career-field career-field--full">
                    <label for="career-scientific-interest"><?php echo esc_html($lang === 'en' ? 'Scientific / professional interests' : 'Επιστημονικό / επαγγελματικό ενδιαφέρον'); ?> <small><?php echo esc_html($lang === 'en' ? 'Optional' : 'Προαιρετικό'); ?></small></label>
                    <input id="career-scientific-interest" type="text" name="scientific_interest" maxlength="250">
                </div>

                <div class="career-field">
                    <label for="career-experience"><?php esc_html_e('Έτη σχετικής εμπειρίας', 'spek-theme'); ?> <small><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></small></label>
                    <input id="career-experience" type="number" name="experience_years" min="0" max="60" step="1" inputmode="numeric">
                </div>

                <div class="career-field">
                    <label for="career-available"><?php esc_html_e('Διαθεσιμότητα από', 'spek-theme'); ?> <small><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></small></label>
                    <input id="career-available" type="date" name="available_from">
                </div>

                <div class="career-field">
                    <label for="career-linkedin">LinkedIn <small><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></small></label>
                    <input id="career-linkedin" type="url" name="linkedin" placeholder="https://linkedin.com/in/…" inputmode="url">
                </div>

                <div class="career-field">
                    <label for="career-portfolio"><?php esc_html_e('Δείγμα εργασιών / προσωπική σελίδα', 'spek-theme'); ?> <small><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></small></label>
                    <input id="career-portfolio" type="url" name="portfolio" placeholder="https://…" inputmode="url">
                </div>
            </div>
        </div>

        <div class="career-form__section">
            <div class="career-form__section-head">
                <span>03</span>
                <div>
                    <h3><?php esc_html_e('Έγγραφα αίτησης', 'spek-theme'); ?></h3>
                    <p><?php esc_html_e('Το βιογραφικό είναι υποχρεωτικό. Οι συστατικές επιστολές είναι προαιρετικές.', 'spek-theme'); ?></p>
                </div>
            </div>

            <div class="career-upload-grid">
                <label class="career-upload">
                    <span class="career-upload__title"><?php esc_html_e('Βιογραφικό', 'spek-theme'); ?> <em>*</em></span>
                    <span class="career-upload__help"><?php esc_html_e('PDF, DOC ή DOCX · έως 8 MB', 'spek-theme'); ?></span>
                    <input type="file" name="resume" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                </label>

                <label class="career-upload">
                    <span class="career-upload__title"><?php esc_html_e('Συστατικές επιστολές', 'spek-theme'); ?> <small><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></small></span>
                    <span class="career-upload__help"><?php esc_html_e('Έως 5 αρχεία · PDF, DOC ή DOCX · έως 5 MB το καθένα', 'spek-theme'); ?></span>
                    <input type="file" name="references[]" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" multiple>
                </label>
            </div>

            <div class="career-field career-field--full career-message">
                <label for="career-message"><?php esc_html_e('Συνοδευτικό σημείωμα', 'spek-theme'); ?> <small><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></small></label>
                <textarea id="career-message" name="message" rows="7" maxlength="3000" placeholder="<?php esc_attr_e('Πείτε μας σύντομα για την εμπειρία σας ή τι σας ενδιαφέρει στη SPEK.', 'spek-theme'); ?>"></textarea>
            </div>
        </div>

        <div class="career-form__privacy">
            <label>
                <input type="checkbox" name="privacy_ack" value="1" required>
                <span>
                    <?php
                    printf(
                        wp_kses(
                            __('Έχω διαβάσει την <a href="%s" target="_blank" rel="noopener">Πολιτική Απορρήτου</a> και κατανοώ ότι τα στοιχεία μου θα χρησιμοποιηθούν για την αξιολόγηση της αίτησής μου. *', 'spek-theme'),
                            ['a' => ['href' => [], 'target' => [], 'rel' => []]]
                        ),
                        esc_url($privacy_url)
                    );
                    ?>
                </span>
            </label>

            <p><?php esc_html_e('Μην αποστέλλετε ειδικές κατηγορίες προσωπικών δεδομένων ή άλλα στοιχεία που δεν είναι απαραίτητα για την αξιολόγηση της υποψηφιότητάς σας.', 'spek-theme'); ?></p>
        </div>

        <div class="career-form__footer">
            <p><strong>*</strong> <?php esc_html_e('Υποχρεωτικό πεδίο', 'spek-theme'); ?></p>
            <button class="button button-primary" type="submit"><?php esc_html_e('Αποστολή αίτησης', 'spek-theme'); ?></button>
        </div>
    </form>
</section>
