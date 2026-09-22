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
        <span class="eyebrow"><?php esc_html_e('Βήμα 1 από 3', 'spek-theme'); ?></span>
        <h2><?php esc_html_e('Τι προϊόν ψάχνετε;', 'spek-theme'); ?></h2>
        <p><?php esc_html_e('Διαλέξτε βασική κατηγορία προϊόντος.', 'spek-theme'); ?></p>
    </div>

    <div class="spek-finder-options spek-finder-options--cards">
        <?php if (!empty($categories) && !is_wp_error($categories)) : ?>
            <?php foreach ($categories as $category) : ?>
                <label class="spek-finder-option">
                    <input type="radio" name="product_category" value="<?php echo esc_attr($category->slug); ?>">
                    <span>
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
            <?php esc_html_e('Πίσω', 'spek-theme'); ?>
        </button>
        <button class="button button-primary spek-finder-next" type="button" data-next-step="application">
            <?php esc_html_e('Συνέχεια', 'spek-theme'); ?>
        </button>
    </div>
</div>