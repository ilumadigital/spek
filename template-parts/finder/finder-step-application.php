<?php
/**
 * Product finder application step.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$applications = get_terms(spek_language_args([
    'taxonomy' => 'product_application',
    'hide_empty' => false,
]));
?>

<div class="spek-finder-panel" data-step="application">
    <div class="spek-finder__head">
        <div class="spek-finder__step-label">
            <span>02</span>
            <small><?php esc_html_e('Βήμα 2 από 3', 'spek-theme'); ?></small>
        </div>
        <h2><?php esc_html_e('Πού θα χρησιμοποιηθεί;', 'spek-theme'); ?></h2>
        <p><?php esc_html_e('Επιλέξτε την εφαρμογή που περιγράφει καλύτερα την εγκατάσταση ή τη χρήση.', 'spek-theme'); ?></p>
    </div>

    <div class="spek-finder-options spek-finder-options--applications">
        <?php if (!empty($applications) && !is_wp_error($applications)) : ?>
            <?php foreach ($applications as $index => $application) : ?>
                <label class="spek-finder-chip">
                    <input
                        type="radio"
                        name="product_application"
                        value="<?php echo esc_attr($application->slug); ?>"
                        data-label="<?php echo esc_attr($application->name); ?>"
                    >
                    <span>
                        <i><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></i>
                        <strong><?php echo esc_html($application->name); ?></strong>
                        <b aria-hidden="true">✓</b>
                    </span>
                </label>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="spek-finder-empty"><?php esc_html_e('Δεν υπάρχουν ακόμη εφαρμογές προϊόντων.', 'spek-theme'); ?></p>
        <?php endif; ?>
    </div>

    <div class="spek-finder-actions">
        <button class="button button-secondary spek-finder-prev" type="button" data-prev-step="category">
            <span aria-hidden="true">←</span><?php esc_html_e('Πίσω', 'spek-theme'); ?>
        </button>
        <button class="button button-primary spek-finder-next" type="button" data-next-step="specs" data-requires-selection="product_application" disabled>
            <?php esc_html_e('Συνέχεια', 'spek-theme'); ?><span aria-hidden="true">→</span>
        </button>
    </div>
</div>
