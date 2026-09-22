<?php
/**
 * Product finder specs step.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$materials = get_terms(spek_language_args([
    'taxonomy' => 'product_material',
    'hide_empty' => false,
]));

$series = get_terms(spek_language_args([
    'taxonomy' => 'product_series',
    'hide_empty' => false,
]));
?>

<div class="spek-finder-panel" data-step="specs">
    <div class="spek-finder__head">
        <div class="spek-finder__step-label">
            <span>03</span>
            <small><?php esc_html_e('Βήμα 3 από 3', 'spek-theme'); ?></small>
        </div>
        <h2><?php esc_html_e('Περιορίστε τα αποτελέσματα.', 'spek-theme'); ?></h2>
        <p><?php esc_html_e('Τα παρακάτω πεδία είναι προαιρετικά. Συμπληρώστε μόνο όσα γνωρίζετε.', 'spek-theme'); ?></p>
    </div>

    <div class="spek-finder-form-grid">
        <div class="spek-finder-field">
            <div class="spek-finder-field__label">
                <label for="finder-material"><?php esc_html_e('Υλικό', 'spek-theme'); ?></label>
                <span><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></span>
            </div>
            <select id="finder-material" name="product_material">
                <option value=""><?php esc_html_e('Όλα τα υλικά', 'spek-theme'); ?></option>
                <?php if (!empty($materials) && !is_wp_error($materials)) : ?>
                    <?php foreach ($materials as $material) : ?>
                        <option value="<?php echo esc_attr($material->slug); ?>"><?php echo esc_html($material->name); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="spek-finder-field">
            <div class="spek-finder-field__label">
                <label for="finder-series"><?php esc_html_e('Σειρά / Brand', 'spek-theme'); ?></label>
                <span><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></span>
            </div>
            <select id="finder-series" name="product_series">
                <option value=""><?php esc_html_e('Όλες οι σειρές', 'spek-theme'); ?></option>
                <?php if (!empty($series) && !is_wp_error($series)) : ?>
                    <?php foreach ($series as $item) : ?>
                        <option value="<?php echo esc_attr($item->slug); ?>"><?php echo esc_html($item->name); ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div class="spek-finder-field">
            <div class="spek-finder-field__label">
                <label for="finder-installation"><?php esc_html_e('Τύπος εγκατάστασης', 'spek-theme'); ?></label>
                <span><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></span>
            </div>
            <input id="finder-installation" type="text" name="product_installation_type" placeholder="<?php esc_attr_e('π.χ. εσωτερική, επιτοίχια, universal', 'spek-theme'); ?>">
        </div>

        <div class="spek-finder-field">
            <div class="spek-finder-field__label">
                <label for="finder-search"><?php esc_html_e('Λέξη-κλειδί ή κωδικός', 'spek-theme'); ?></label>
                <span><?php esc_html_e('Προαιρετικό', 'spek-theme'); ?></span>
            </div>
            <input id="finder-search" type="search" name="product_search" placeholder="<?php esc_attr_e('π.χ. φλοτέρ, Φ40, SPK', 'spek-theme'); ?>">
        </div>
    </div>

    <div class="spek-finder-specs-note">
        <span aria-hidden="true">i</span>
        <p><?php esc_html_e('Αν δεν γνωρίζετε κάποια προδιαγραφή, αφήστε το πεδίο κενό. Ο Finder θα χρησιμοποιήσει τις προηγούμενες επιλογές.', 'spek-theme'); ?></p>
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
