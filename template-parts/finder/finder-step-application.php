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
        <span class="eyebrow"><?php esc_html_e('Βήμα 2 από 3', 'spek-theme'); ?></span>
        <h2><?php esc_html_e('Πού θα χρησιμοποιηθεί;', 'spek-theme'); ?></h2>
        <p><?php esc_html_e('Επιλέξτε εφαρμογή ώστε να περιορίσουμε τις προτάσεις.', 'spek-theme'); ?></p>
    </div>

    <div class="spek-finder-options">
        <?php if (!empty($applications) && !is_wp_error($applications)) : ?>
            <?php foreach ($applications as $application) : ?>
                <label class="spek-finder-chip">
                    <input type="radio" name="product_application" value="<?php echo esc_attr($application->slug); ?>">
                    <span><?php echo esc_html($application->name); ?></span>
                </label>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="spek-finder-empty"><?php esc_html_e('Δεν υπάρχουν ακόμη εφαρμογές προϊόντων.', 'spek-theme'); ?></p>
        <?php endif; ?>
    </div>

    <div class="spek-finder-actions">
        <button class="button button-secondary spek-finder-prev" type="button" data-prev-step="category">
            <?php esc_html_e('Πίσω', 'spek-theme'); ?>
        </button>
        <button class="button button-primary spek-finder-next" type="button" data-next-step="specs">
            <?php esc_html_e('Συνέχεια', 'spek-theme'); ?>
        </button>
    </div>
</div>