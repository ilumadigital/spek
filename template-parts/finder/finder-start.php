<?php
/**
 * Product finder start screen.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="spek-finder" data-current-step="start">
    <div class="spek-finder__intro spek-finder-panel is-active" data-step="start">
        <span class="eyebrow"><?php esc_html_e('SPEK Product Finder', 'spek-theme'); ?></span>

        <h2><?php esc_html_e('Βρείτε γρήγορα το σωστό προϊόν', 'spek-theme'); ?></h2>

        <p>
            <?php esc_html_e('Επιλέξτε κατηγορία, εφαρμογή και βασικά χαρακτηριστικά. Ο οδηγός θα φιλτράρει τα προϊόντα και θα σας δείξει τις πιο κοντινές επιλογές.', 'spek-theme'); ?>
        </p>

        <div class="spek-finder__progress" aria-label="<?php esc_attr_e('Product finder progress', 'spek-theme'); ?>">
            <span class="is-active">1</span>
            <span>2</span>
            <span>3</span>
            <span>4</span>
        </div>

        <button class="button button-primary spek-finder-next" type="button" data-next-step="category">
            <?php esc_html_e('Ξεκινήστε', 'spek-theme'); ?>
        </button>
    </div>