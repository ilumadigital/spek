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
        'title' => __('Τεχνική λογική', 'spek-theme'),
        'text'  => __('Κάθε προϊόν πρέπει να εξυπηρετεί καθαρά την εφαρμογή του: σωστή λειτουργία, εύκολη εγκατάσταση και αξιόπιστη χρήση.', 'spek-theme'),
    ],
    [
        'title' => __('Συνέπεια στην ποιότητα', 'spek-theme'),
        'text'  => __('Η γκάμα SPEK είναι οργανωμένη γύρω από καθημερινές ανάγκες της αγοράς, με λύσεις που μπορούν να υποστηρίξουν επαγγελματίες και συνεργάτες.', 'spek-theme'),
    ],
    [
        'title' => __('Καθαρή πληροφόρηση', 'spek-theme'),
        'text'  => __('Κατάλογοι, κατηγορίες, κωδικοί και Product Finder βοηθούν τον χρήστη να κινηθεί γρήγορα και με σιγουριά.', 'spek-theme'),
    ],
    [
        'title' => __('Υποστήριξη B2B', 'spek-theme'),
        'text'  => __('Για σημεία πώλησης, τεχνικούς και συνεργάτες, η SPEK παρέχει εμπορική και προϊοντική υποστήριξη με επαγγελματική προσέγγιση.', 'spek-theme'),
    ],
];

?>

<section class="section why-spek">
    <div class="container">

        <div class="section-heading" data-reveal>
            <span class="eyebrow"><?php esc_html_e('Why SPEK', 'spek-theme'); ?></span>
            <h2><?php esc_html_e('Σχεδιασμένο για την αγορά. Χτισμένο για καθημερινή χρήση.', 'spek-theme'); ?></h2>
            <p>
                <?php esc_html_e('Η αξία ενός προϊόντος υδραυλικών φαίνεται στην εγκατάσταση, στη λειτουργία και στην εμπιστοσύνη που δημιουργεί στον επαγγελματία.', 'spek-theme'); ?>
            </p>
        </div>

        <div class="features-grid">
            <?php foreach ($items as $index => $item) : ?>
                <div class="feature-card" data-reveal style="--reveal-delay: <?php echo esc_attr($index * 80); ?>ms;">
                    <h3><?php echo esc_html($item['title']); ?></h3>
                    <p><?php echo esc_html($item['text']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
