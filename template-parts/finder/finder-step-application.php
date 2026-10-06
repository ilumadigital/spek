<?php
/**
 * Product finder application / toilet-seat measurements step.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="spek-finder-panel" data-step="application">
    <div data-application-mode="default">
        <div class="spek-finder__head">
            <div class="spek-finder__step-label">
                <span>02</span>
                <small><?php esc_html_e('Βήμα 2 από 3', 'spek-theme'); ?></small>
            </div>
            <h2><?php esc_html_e('Πού θα χρησιμοποιηθεί;', 'spek-theme'); ?></h2>
            <p><?php esc_html_e('Εμφανίζονται μόνο οι εφαρμογές που υπάρχουν πραγματικά στην κατηγορία που επιλέξατε.', 'spek-theme'); ?></p>
        </div>

        <div class="spek-finder-facet-state" data-application-loading hidden>
            <span class="spek-finder-loading__spinner" aria-hidden="true"></span>
            <span><?php esc_html_e('Ελέγχουμε τις διαθέσιμες εφαρμογές…', 'spek-theme'); ?></span>
        </div>

        <div class="spek-finder-options spek-finder-options--applications" data-application-options></div>

        <div class="spek-finder-specs-note" data-application-empty hidden>
            <span aria-hidden="true">i</span>
            <p><?php esc_html_e('Για αυτή την κατηγορία δεν χρειάζεται επιπλέον επιλογή εφαρμογής. Μπορείτε να συνεχίσετε.', 'spek-theme'); ?></p>
        </div>
    </div>

    <div class="spek-finder-seat-cover" data-application-mode="seat-cover" hidden>
        <div class="spek-finder__head">
            <div class="spek-finder__step-label">
                <span>02</span>
                <small><?php esc_html_e('Βήμα 2 από 3', 'spek-theme'); ?></small>
            </div>
            <h2><?php esc_html_e('Έχετε τις διαστάσεις του καλύμματος;', 'spek-theme'); ?></h2>
            <p><?php esc_html_e('Αν γνωρίζετε το μήκος και το πλάτος, συμπληρώστε τα ώστε να περιορίσουμε τις προτάσεις. Οι εικόνες δείχνουν ακριβώς από πού γίνεται κάθε μέτρηση.', 'spek-theme'); ?></p>
        </div>

        <div class="spek-finder-options spek-finder-options--dimensions-choice">
            <label class="spek-finder-chip spek-finder-dimension-choice">
                <input type="radio" name="cover_dimensions_known" value="yes" data-label="<?php esc_attr_e('Έχω τις διαστάσεις', 'spek-theme'); ?>">
                <span>
                    <i>01</i>
                    <strong><?php esc_html_e('Ναι, τις έχω', 'spek-theme'); ?></strong>
                    <b aria-hidden="true">✓</b>
                </span>
            </label>
            <label class="spek-finder-chip spek-finder-dimension-choice">
                <input type="radio" name="cover_dimensions_known" value="no" data-label="<?php esc_attr_e('Δεν γνωρίζω τις διαστάσεις', 'spek-theme'); ?>">
                <span>
                    <i>02</i>
                    <strong><?php esc_html_e('Όχι, δεν τις γνωρίζω', 'spek-theme'); ?></strong>
                    <b aria-hidden="true">✓</b>
                </span>
            </label>
        </div>

        <div class="spek-finder-seat-cover__measurements" data-cover-measurements hidden>
            <article class="spek-finder-measure-card">
                <figure>
                    <img
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/finder/seat-cover-length.jpg'); ?>"
                        alt="<?php esc_attr_e('Οδηγός μέτρησης μήκους καλύμματος λεκάνης', 'spek-theme'); ?>"
                        loading="lazy"
                        decoding="async"
                    >
                </figure>
                <div class="spek-finder-measure-card__body">
                    <div class="spek-finder-field__label">
                        <label for="finder-cover-length"><?php esc_html_e('Μήκος καλύμματος', 'spek-theme'); ?></label>
                        <span><?php esc_html_e('σε cm', 'spek-theme'); ?></span>
                    </div>
                    <p><?php esc_html_e('Μετρήστε από το σημείο στήριξης μέχρι το μπροστινό άκρο του καλύμματος, όπως φαίνεται στην εικόνα.', 'spek-theme'); ?></p>
                    <div class="spek-finder-measure-input">
                        <input id="finder-cover-length" type="number" name="cover_length" min="20" max="70" step="0.1" inputmode="decimal" placeholder="π.χ. 44">
                        <span>cm</span>
                    </div>
                </div>
            </article>

            <article class="spek-finder-measure-card">
                <figure>
                    <img
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/finder/seat-cover-width.jpg'); ?>"
                        alt="<?php esc_attr_e('Οδηγός μέτρησης πλάτους καλύμματος λεκάνης', 'spek-theme'); ?>"
                        loading="lazy"
                        decoding="async"
                    >
                </figure>
                <div class="spek-finder-measure-card__body">
                    <div class="spek-finder-field__label">
                        <label for="finder-cover-width"><?php esc_html_e('Πλάτος καλύμματος', 'spek-theme'); ?></label>
                        <span><?php esc_html_e('σε cm', 'spek-theme'); ?></span>
                    </div>
                    <p><?php esc_html_e('Μετρήστε το κάλυμμα οριζόντια στο φαρδύτερο σημείο του, όπως φαίνεται στην εικόνα.', 'spek-theme'); ?></p>
                    <div class="spek-finder-measure-input">
                        <input id="finder-cover-width" type="number" name="cover_width" min="20" max="60" step="0.1" inputmode="decimal" placeholder="π.χ. 37">
                        <span>cm</span>
                    </div>
                </div>
            </article>
        </div>

        <div class="spek-finder-specs-note" data-cover-no-dimensions-note hidden>
            <span aria-hidden="true">i</span>
            <p><?php esc_html_e('Δεν υπάρχει πρόβλημα αν δεν γνωρίζετε τις διαστάσεις. Θα συνεχίσουμε μόνο με τα πραγματικά διαθέσιμα χαρακτηριστικά της κατηγορίας.', 'spek-theme'); ?></p>
        </div>
    </div>

    <div class="spek-finder-actions">
        <button class="button button-secondary spek-finder-prev" type="button" data-prev-step="category">
            <span aria-hidden="true">←</span><?php esc_html_e('Πίσω', 'spek-theme'); ?>
        </button>
        <button class="button button-primary spek-finder-next" type="button" data-next-step="specs" data-application-next disabled>
            <?php esc_html_e('Συνέχεια', 'spek-theme'); ?><span aria-hidden="true">→</span>
        </button>
    </div>
</div>
