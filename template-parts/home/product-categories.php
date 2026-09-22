<?php
/**
 * Homepage product categories.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$categories = [
    [
        'title' => __('Μηχανισμοί', 'spek-theme'),
        'text'  => __('Μηχανισμοί σχεδιασμένοι για σωστή εφαρμογή, καθημερινή λειτουργία και διαχρονική αξιοπιστία.', 'spek-theme'),
        'source_slug' => 'mixanismoi',
        'url'   => spek_term_url('mixanismoi'),
    ],
    [
        'title' => __('Φλοτέρ', 'spek-theme'),
        'text'  => __('Όλες οι λύσεις για έλεγχο στη στάθμη του νερού σε όλα τα καζανάκια.', 'spek-theme'),
        'source_slug' => 'floter',
        'url'   => spek_term_url('floter'),
    ],
    [
        'title' => __('Σιφόνια', 'spek-theme'),
        'text'  => __('Εύκαμπτες, πρακτικές και ανθεκτικές επιλογές αποχέτευσης για νιπτήρες, μπάνια και νεροχύτες.', 'spek-theme'),
        'source_slug' => 'sifonia',
        'url'   => spek_term_url('sifonia'),
    ],
    [
        'title' => __('Λάστιχα', 'spek-theme'),
        'text'  => __('Λύσεις στεγανοποίησης για εγκαταστάσεις με ευκολία και σταθερό αποτέλεσμα.', 'spek-theme'),
        'source_slug' => 'lastixa',
        'url'   => spek_term_url('lastixa'),
    ],
    [
        'title' => __('Καζανάκια', 'spek-theme'),
        'text'  => __('Λειτουργικά συστήματα για μπάνιο και WC, με έμφαση στην ευκολία χρήσης και την αξιοπιστία.', 'spek-theme'),
        'source_slug' => 'kazanakia',
        'url'   => spek_term_url('kazanakia'),
    ],
    [
        'title' => __('Nemo', 'spek-theme'),
        'text'  => __('Η σειρά Nemo συγκεντρώνει σύγχρονες, οικονομικές λύσεις για εφαρμογές μπάνιου και υδραυλικών.', 'spek-theme'),
        'source_slug' => 'nemo',
        'url'   => spek_term_url('nemo'),
    ],
    [
        'title' => __('Ανταλλακτικά', 'spek-theme'),
        'text'  => __('Χρήσιμες επιλογές υποστήριξης για συντήρηση, αντικατάσταση και ολοκλήρωση εγκαταστάσεων.', 'spek-theme'),
        'url'   => spek_page_url('products/'),
    ],
    [
        'title' => __('Όλη η γκάμα', 'spek-theme'),
        'text'  => __('Δείτε συγκεντρωμένα όλα τα προϊόντα SPEK και φιλτράρετε με βάση την εφαρμογή που χρειάζεστε.', 'spek-theme'),
        'url'   => spek_page_url('products/'),
    ],
];
foreach ($categories as $index => &$category) {
    if (empty($category['source_slug'])) { continue; }
    $term = get_term_by('slug', $category['source_slug'], 'product_category');
    if (!$term || is_wp_error($term)) {
        continue;
    }
    $category['title'] = $term->name;
    $category['url'] = get_term_link($term);
}
unset($category);
$categories = array_values($categories);
?>

<section class="section product-categories">
    <div class="container">

        <div class="section-heading" data-reveal>
            <span class="eyebrow"><?php esc_html_e('Product Range', 'spek-theme'); ?></span>
            <h2><?php esc_html_e('Μια πλήρης γκάμα για επαγγελματικές εγκαταστάσεις.', 'spek-theme'); ?></h2>
            <p>
                <?php esc_html_e('Οργανωμένες κατηγορίες, καθαρή τεχνική πληροφορία και εύκολη πρόσβαση στα προϊόντα που χρειάζεται ο εγκαταστάτης, ο συνεργάτης και το σημείο πώλησης.', 'spek-theme'); ?>
            </p>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $index => $category) : ?>
                <a href="<?php echo esc_url($category['url']); ?>" class="category-card" data-reveal style="--reveal-delay: <?php echo esc_attr($index * 60); ?>ms;">
                    <span class="category-card__icon" aria-hidden="true"></span>
                    <h3><?php echo esc_html($category['title']); ?></h3>
                    <p><?php echo esc_html($category['text']); ?></p>
                    <span class="category-card__link"><?php esc_html_e('Δείτε κατηγορία', 'spek-theme'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
</section>