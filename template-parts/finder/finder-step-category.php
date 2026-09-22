<?php
/**
 * Product finder category step.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$categories = get_terms(spek_language_args([
    'taxonomy' => 'product_category',
    'hide_empty' => false,
]));
?>

<div class="spek-finder-panel" data-step="category">
    <div class="spek-finder__head">
        <div class="spek-finder__step-label">
            <span>01</span>
            <small><?php esc_html_e('Βήμα 1 από 3', 'spek-theme'); ?></small>
        </div>
        <h2><?php esc_html_e('Τι προϊόν ψάχνετε;', 'spek-theme'); ?></h2>
        <p><?php esc_html_e('Επιλέξτε τη βασική κατηγορία. Μπορείτε να αλλάξετε την επιλογή σας οποιαδήποτε στιγμή.', 'spek-theme'); ?></p>
    </div>

    <div class="spek-finder-options spek-finder-options--cards">
        <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
            <?php foreach ($categories as $index => $category) : ?>
                <label class="spek-finder-option">
                    <input
                        type="radio"
                        name="product_category"
                        value="<?php echo esc_attr($category->slug); ?>"
                        data-label="<?php echo esc_attr($category->name); ?>"
                    >
                    <span>
                        <i class="spek-finder-option__index"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></i>
                        <span class="spek-finder-option__check" aria-hidden="true">✓</span>
                        <strong><?php echo esc_html($category->name); ?></strong>
                        <em><?php echo esc_html(sprintf(_n('%d προϊόν', '%d προϊόντα', (int) $category->count, 'spek-theme'), (int) $category->count)); ?></em>
                    </span>
                </label>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="spek-finder-empty"><?php esc_html_e('Δεν υπάρχουν ακόμη κατηγορίες προϊόντων.', 'spek-theme'); ?></p>
        <?php endif; ?>
    </div>

    <div class="spek-finder-actions">
        <button class="button button-secondary spek-finder-prev" type="button" data-prev-step="start">
            <span aria-hidden="true">←</span><?php esc_html_e('Πίσω', 'spek-theme'); ?>
        </button>
        <button class="button button-primary spek-finder-next" type="button" data-next-step="application" data-requires-selection="product_category" disabled>
            <?php esc_html_e('Συνέχεια', 'spek-theme'); ?><span aria-hidden="true">→</span>
        </button>
    </div>
</div>
