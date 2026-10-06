<?php
/**
 * Product finder specs step.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="spek-finder-panel" data-step="specs">
    <div class="spek-finder__head">
        <div class="spek-finder__step-label">
            <span>03</span>
            <small><?php esc_html_e('Βήμα 3 από 3', 'spek-theme'); ?></small>
        </div>
        <h2><?php esc_html_e('Περιορίστε τα αποτελέσματα.', 'spek-theme'); ?></h2>
        <p><?php esc_html_e('Κάθε επιλογή που εμφανίζεται παρακάτω υπάρχει πραγματικά στα προϊόντα που ταιριάζουν ήδη με τα προηγούμενα βήματα.', 'spek-theme'); ?></p>
    </div>

    <div class="spek-finder-facet-state" data-specs-loading hidden>
        <span class="spek-finder-loading__spinner" aria-hidden="true"></span>
        <span><?php esc_html_e('Ενημερώνουμε τα διαθέσιμα χαρακτηριστικά…', 'spek-theme'); ?></span>
    </div>

    <div class="spek-finder-form-grid" data-specs-grid>
        <div class="spek-finder-field" data-facet-field="material">
            <div class="spek-finder-field__label">
                <label for="finder-material"><?php esc_html_e('Υλικό', 'spek-theme'); ?></label>
                <span><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></span>
            </div>
            <select id="finder-material" name="product_material">
                <option value=""><?php esc_html_e('Όλα τα διαθέσιμα υλικά', 'spek-theme'); ?></option>
            </select>
        </div>

        <div class="spek-finder-field" data-facet-field="series">
            <div class="spek-finder-field__label">
                <label for="finder-series"><?php esc_html_e('Σειρά προϊόντος', 'spek-theme'); ?></label>
                <span><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></span>
            </div>
            <select id="finder-series" name="product_series">
                <option value=""><?php esc_html_e('Όλες οι διαθέσιμες σειρές', 'spek-theme'); ?></option>
            </select>
        </div>

        <div class="spek-finder-field" data-facet-field="installation">
            <div class="spek-finder-field__label">
                <label for="finder-installation"><?php esc_html_e('Τύπος εγκατάστασης', 'spek-theme'); ?></label>
                <span><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></span>
            </div>
            <select id="finder-installation" name="product_installation_type">
                <option value=""><?php esc_html_e('Όλοι οι διαθέσιμοι τύποι', 'spek-theme'); ?></option>
            </select>
        </div>

        <div class="spek-finder-field">
            <div class="spek-finder-field__label">
                <label for="finder-search"><?php esc_html_e('Λέξη-κλειδί ή κωδικός', 'spek-theme'); ?></label>
                <span><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></span>
            </div>
            <input id="finder-search" type="search" name="product_search" placeholder="<?php esc_attr_e('π.χ. φλοτέρ, Φ40, SPK', 'spek-theme'); ?>">
        </div>
    </div>

    <div class="spek-finder-specs-note" data-specs-empty hidden>
        <span aria-hidden="true">i</span>
        <p><?php esc_html_e('Δεν υπάρχουν επιπλέον τεχνικά φίλτρα για αυτόν τον συνδυασμό. Μπορείτε να δείτε απευθείας τα αποτελέσματα.', 'spek-theme'); ?></p>
    </div>

    <div class="spek-finder-specs-note" data-specs-note>
        <span aria-hidden="true">i</span>
        <p><?php esc_html_e('Αν δεν γνωρίζετε κάποια προδιαγραφή, αφήστε το πεδίο κενό. Δεν εμφανίζουμε επιλογές που θα οδηγούσαν σε μηδενικά αποτελέσματα.', 'spek-theme'); ?></p>
    </div>

    <div class="spek-finder-actions">
        <button class="button button-secondary spek-finder-prev" type="button" data-prev-step="application">
            <span aria-hidden="true">←</span><?php esc_html_e('Πίσω', 'spek-theme'); ?>
        </button>
        <button class="button button-primary spek-finder-submit" type="button">
            <?php esc_html_e('Προβολή αποτελεσμάτων', 'spek-theme'); ?><span aria-hidden="true">→</span>
        </button>
    </div>
</div>
