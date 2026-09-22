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
            <span class="eyebrow"><?php esc_html_e('Αποτελέσματα', 'spek-theme'); ?></span>
            <h2><?php esc_html_e('Προτεινόμενα προϊόντα', 'spek-theme'); ?></h2>
            <p class="spek-finder-results-count" aria-live="polite"></p>
        </div>

        <button class="button button-secondary spek-finder-restart" type="button">
            <?php esc_html_e('Νέα αναζήτηση', 'spek-theme'); ?>
        </button>
    </div>

    <div class="spek-finder-loading" hidden>
        <?php esc_html_e('Φόρτωση αποτελεσμάτων...', 'spek-theme'); ?>
    </div>

    <div class="spek-finder-results products-grid" aria-live="polite"></div>
</div>
</div>