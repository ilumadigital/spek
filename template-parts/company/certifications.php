<?php
/**
 * Company quality and certifications.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<section class="section company-quality company-quality--overview">
    <div class="container">
        <div class="section-heading company-quality__heading" data-reveal>
            <span class="eyebrow"><?php esc_html_e('Ποιότητα', 'spek-theme'); ?></span>
            <h2><?php esc_html_e('Η ποιότητα δεν ελέγχεται μόνο στο τέλος. Χτίζεται σε κάθε στάδιο.', 'spek-theme'); ?></h2>
            <p><?php esc_html_e('Από τις πρώτες ύλες μέχρι την τελική διάθεση, η SPEK οργανώνει τις διαδικασίες της με στόχο τη συνέπεια, την αξιοπιστία και τη συνεχή βελτίωση.', 'spek-theme'); ?></p>
        </div>

        <div class="company-quality__grid">
            <article class="company-quality-card" data-reveal>
                <span>01</span>
                <h3><?php esc_html_e('Πρώτες ύλες', 'spek-theme'); ?></h3>
                <p><?php esc_html_e('Η ποιότητα ξεκινά από την επιλογή και τον έλεγχο των υλικών που μπαίνουν στην παραγωγή.', 'spek-theme'); ?></p>
            </article>
            <article class="company-quality-card" data-reveal>
                <span>02</span>
                <h3><?php esc_html_e('Παραγωγική διαδικασία', 'spek-theme'); ?></h3>
                <p><?php esc_html_e('Οργανωμένα στάδια παραγωγής και έλεγχοι υποστηρίζουν σταθερή ποιότητα και επαναληψιμότητα.', 'spek-theme'); ?></p>
            </article>
            <article class="company-quality-card company-quality-card--featured" data-reveal>
                <span>2012</span>
                <h3>ISO / DQS</h3>
                <p><?php esc_html_e('Το 2012 η εταιρεία απέκτησε πιστοποιήσεις ISO / DQS, ενισχύοντας το οργανωμένο πλαίσιο διαχείρισης ποιότητας.', 'spek-theme'); ?></p>
            </article>
            <article class="company-quality-card" data-reveal>
                <span>04</span>
                <h3><?php esc_html_e('Ανακύκλωση', 'spek-theme'); ?></h3>
                <p><?php esc_html_e('Από το 2012 εφαρμόζεται οργανωμένη διαδικασία ανακύκλωσης ως μέρος της παραγωγικής λειτουργίας.', 'spek-theme'); ?></p>
            </article>
        </div>
    </div>
</section>

<?php get_template_part('template-parts/company/exclusive-partnerships'); ?>
