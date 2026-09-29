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

<div class="spek-finder__intro spek-finder-panel is-active" data-step="start">
    <div class="spek-finder-start__content">
        <span class="eyebrow"><?php esc_html_e('Εργαλείο Επιλογής Προϊόντος', 'spek-theme'); ?></span>
        <h2><?php esc_html_e('Από την ανάγκη, στο σωστό προϊόν.', 'spek-theme'); ?></h2>
        <p><?php esc_html_e('Το εργαλείο εύρεσης προϊόντος οργανώνει την αναζήτηση σε τρία απλά βήματα και εμφανίζει τις πιο σχετικές επιλογές της γκάμας SPEK.', 'spek-theme'); ?></p>

        <div class="spek-finder-start__features">
            <div><span>01</span><strong><?php esc_html_e('Γρήγορη επιλογή', 'spek-theme'); ?></strong><small><?php esc_html_e('Χωρίς περιττά φίλτρα.', 'spek-theme'); ?></small></div>
            <div><span>02</span><strong><?php esc_html_e('Τεχνική ακρίβεια', 'spek-theme'); ?></strong><small><?php esc_html_e('Με βάση την εφαρμογή σας.', 'spek-theme'); ?></small></div>
            <div><span>03</span><strong><?php esc_html_e('Άμεσα αποτελέσματα', 'spek-theme'); ?></strong><small><?php esc_html_e('Με πρόσβαση στο προϊόν.', 'spek-theme'); ?></small></div>
        </div>

        <div class="spek-finder-start__actions">
            <button class="button button-primary spek-finder-next" type="button" data-next-step="category">
                <?php esc_html_e('Έναρξη επιλογής', 'spek-theme'); ?><span aria-hidden="true">→</span>
            </button>
            <small><?php esc_html_e('3 βήματα · λιγότερο από 1 λεπτό', 'spek-theme'); ?></small>
        </div>
    </div>

</div>
