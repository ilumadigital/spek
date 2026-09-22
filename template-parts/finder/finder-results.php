<?php
/**
 * Product finder results.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="spek-finder-panel" data-step="results">
    <div class="spek-finder__head spek-finder__head--results">
        <div>
            <div class="spek-finder__step-label">
                <span>04</span>
                <small><?php esc_html_e('Αποτελέσματα', 'spek-theme'); ?></small>
            </div>
            <h2><?php esc_html_e('Προτεινόμενα προϊόντα.', 'spek-theme'); ?></h2>
            <p class="spek-finder-results-count" aria-live="polite"></p>
        </div>

        <button class="button button-secondary spek-finder-restart" type="button">
            <span aria-hidden="true">↻</span><?php esc_html_e('Νέα αναζήτηση', 'spek-theme'); ?>
        </button>
    </div>

    <div class="spek-finder-results-summary">
        <span data-result-summary-category></span>
        <span data-result-summary-application></span>
        <span data-result-summary-specs></span>
    </div>

    <div class="spek-finder-loading" hidden>
        <span class="spek-finder-loading__spinner" aria-hidden="true"></span>
        <div>
            <strong><?php esc_html_e('Αναζήτηση προϊόντων SPEK', 'spek-theme'); ?></strong>
            <small><?php esc_html_e('Εφαρμόζουμε τα κριτήρια επιλογής σας…', 'spek-theme'); ?></small>
        </div>
    </div>

    <div class="spek-finder-results products-grid" aria-live="polite"></div>
</div>
