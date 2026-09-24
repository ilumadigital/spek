<?php
/**
 * Why SPEK section.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$items = [
    [
        'title' => __('Αξιόπιστη λειτουργία', 'spek-theme'),
        'text'  => __('Προϊόντα με σαφή εφαρμογή και σταθερή λειτουργία στην καθημερινή χρήση.', 'spek-theme'),
    ],
    [
        'title' => __('Σταθερή ποιότητα', 'spek-theme'),
        'text'  => __('Συνεπείς προδιαγραφές και οργανωμένη γκάμα προϊόντων.', 'spek-theme'),
    ],
    [
        'title' => __('Τεχνική πληροφόρηση', 'spek-theme'),
        'text'  => __('Κωδικοί, τεχνικά στοιχεία και κατάλογοι για γρήγορη αναφορά.', 'spek-theme'),
    ],
    [
        'title' => __('B2B υποστήριξη', 'spek-theme'),
        'text'  => __('Υποστήριξη για επαγγελματίες, συνεργάτες και σημεία πώλησης.', 'spek-theme'),
    ],
];

?>

<section class="section why-spek">
    <div class="container">

        <div class="section-heading" data-reveal>
            <span class="eyebrow"><?php esc_html_e('Γιατί SPEK', 'spek-theme'); ?></span>
            <h2><?php esc_html_e('Αξιοπιστία, σαφής πληροφόρηση και επαγγελματική υποστήριξη.', 'spek-theme'); ?></h2>
            <p>
                <?php esc_html_e('Η SPEK οργανώνει τη γκάμα και τις υπηρεσίες της με βάση τις ανάγκες τεχνικών, συνεργατών και σημείων πώλησης.', 'spek-theme'); ?>
            </p>
        </div>

        <div class="why-spek__manifesto" data-reveal style="--reveal-delay: 80ms;">
            <div class="why-spek__monogram"><?php esc_html_e('ΣΠΕΚ', 'spek-theme'); ?></div>
            <div class="why-spek__manifesto-copy">
                <span class="why-spek__manifesto-label"><?php esc_html_e('Η φιλοσοφία της SPEK', 'spek-theme'); ?></span>
                <div class="why-spek__pillars">
                    <span><?php esc_html_e('Σχεδιάζουμε', 'spek-theme'); ?></span>
                    <span><?php esc_html_e('Πρωτοπορούμε', 'spek-theme'); ?></span>
                    <span><?php esc_html_e('Εξελισσόμαστε', 'spek-theme'); ?></span>
                    <span><?php esc_html_e('Κατασκευάζουμε', 'spek-theme'); ?></span>
                </div>
            </div>
        </div>

        <div class="features-grid">
            <?php foreach ($items as $index => $item) : ?>
                <div class="feature-card" data-reveal style="--reveal-delay: <?php echo esc_attr(($index * 80) + 120); ?>ms;">
                    <h3><?php echo esc_html($item['title']); ?></h3>
                    <p><?php echo esc_html($item['text']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
