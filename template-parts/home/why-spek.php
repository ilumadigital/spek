<?php
/**
 * Why SPEK section.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$values = [
    [
        'letter' => 'Σ',
        'title'  => __('Σχεδιάζουμε', 'spek-theme'),
        'text'   => __('Μελετάμε κάθε λύση με βάση την πραγματική ανάγκη της εγκατάστασης και της καθημερινής χρήσης.', 'spek-theme'),
    ],
    [
        'letter' => 'Π',
        'title'  => __('Πρωτοπορούμε', 'spek-theme'),
        'text'   => __('Αναζητούμε πρακτικές ιδέες που βελτιώνουν τα προϊόντα, τις εφαρμογές και την εμπειρία του επαγγελματία.', 'spek-theme'),
    ],
    [
        'letter' => 'Ε',
        'title'  => __('Εξελισσόμαστε', 'spek-theme'),
        'text'   => __('Επενδύουμε διαρκώς σε τεχνογνωσία, παραγωγή και νέες προϊοντικές δυνατότητες.', 'spek-theme'),
    ],
    [
        'letter' => 'Κ',
        'title'  => __('Κατασκευάζουμε', 'spek-theme'),
        'text'   => __('Μετατρέπουμε τον σχεδιασμό σε αξιόπιστα προϊόντα, με συνέπεια, έλεγχο και προσοχή στη λεπτομέρεια.', 'spek-theme'),
    ],
];

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

        <div class="why-spek__values-wrap" data-reveal style="--reveal-delay: 70ms;">
            <div class="why-spek__values-heading">
                <span class="why-spek__wordmark"><?php esc_html_e('ΣΠΕΚ', 'spek-theme'); ?></span>
                <p><?php esc_html_e('Τέσσερις λέξεις που εκφράζουν τον τρόπο με τον οποίο δουλεύουμε.', 'spek-theme'); ?></p>
            </div>

            <div class="why-spek__values">
                <?php foreach ($values as $index => $value) : ?>
                    <article class="why-spek__value" data-reveal style="--reveal-delay: <?php echo esc_attr(($index * 65) + 110); ?>ms;">
                        <span class="why-spek__value-letter" aria-hidden="true"><?php echo esc_html($value['letter']); ?></span>
                        <div class="why-spek__value-copy">
                            <h3><?php echo esc_html($value['title']); ?></h3>
                            <p><?php echo esc_html($value['text']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="why-spek__benefits-intro" data-reveal style="--reveal-delay: 150ms;">
            <span><?php esc_html_e('Στην πράξη', 'spek-theme'); ?></span>
        </div>

        <div class="features-grid">
            <?php foreach ($items as $index => $item) : ?>
                <div class="feature-card" data-reveal style="--reveal-delay: <?php echo esc_attr(($index * 70) + 180); ?>ms;">
                    <span class="feature-card__index"><?php echo esc_html(sprintf('%02d', $index + 1)); ?></span>
                    <h3><?php echo esc_html($item['title']); ?></h3>
                    <p><?php echo esc_html($item['text']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
