<?php
/**
 * Homepage product categories.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$products_url = spek_page_url('products/');

$categories = [
    [
        'title' => __('Μηχανισμοί', 'spek-theme'),
        'text'  => __('Μηχανισμοί σχεδιασμένοι για σωστή εφαρμογή, καθημερινή λειτουργία και διαχρονική αξιοπιστία.', 'spek-theme'),
        'group' => 'mixanismoi',
        'icon'  => 'mechanisms',
    ],
    [
        'title' => __('Φλοτέρ', 'spek-theme'),
        'text'  => __('Όλες οι λύσεις για έλεγχο στη στάθμη του νερού σε όλα τα καζανάκια.', 'spek-theme'),
        'group' => 'floter',
        'icon'  => 'float',
    ],
    [
        'title' => __('Σιφόνια', 'spek-theme'),
        'text'  => __('Εύκαμπτες, πρακτικές και ανθεκτικές επιλογές αποχέτευσης για νιπτήρες, μπάνια και νεροχύτες.', 'spek-theme'),
        'group' => 'sifonia',
        'icon'  => 'siphon',
    ],
    [
        'title' => __('Καλύμματα λεκάνης', 'spek-theme'),
        'text'  => __('Καλαίσθητες και πρακτικές λύσεις για λεκάνες WC, με έμφαση στην άνεση, την αντοχή και την εύκολη εφαρμογή.', 'spek-theme'),
        'group' => 'kalymmata-lekanis',
        'icon'  => 'seat',
    ],
    [
        'title' => __('Καζανάκια', 'spek-theme'),
        'text'  => __('Λειτουργικά συστήματα για μπάνιο και WC, με έμφαση στην ευκολία χρήσης και την αξιοπιστία.', 'spek-theme'),
        'group' => 'kazanakia',
        'icon'  => 'cistern',
    ],
    [
        'title' => __('Μπάνιο', 'spek-theme'),
        'text'  => __('Επιλεγμένες λύσεις για τον εξοπλισμό μπάνιου, σχεδιασμένες για λειτουργικότητα, άνεση και καθημερινή χρήση.', 'spek-theme'),
        'group' => 'banio',
        'icon'  => 'bathroom',
    ],
    [
        'title' => __('Λάστιχα', 'spek-theme'),
        'text'  => __('Λύσεις στεγανοποίησης για εγκαταστάσεις με ευκολία και σταθερό αποτέλεσμα.', 'spek-theme'),
        'group' => 'lastixa',
        'icon'  => 'seals',
    ],
    [
        'title' => __('Διάφορα', 'spek-theme'),
        'text'  => __('Συμπληρωματικές λύσεις, ανταλλακτικά και ειδικές σειρές, μαζί με τη Nemo, για ανάγκες πέρα από τις βασικές κατηγορίες.', 'spek-theme'),
        'group' => 'diafora',
        'icon'  => 'misc',
    ],
];

?>

<section id="home-product-range" class="section product-categories">
    <div class="container">

        <div class="section-heading" data-reveal>
            <span class="eyebrow"><?php esc_html_e('Γκάμα Προϊόντων', 'spek-theme'); ?></span>
            <h2><?php esc_html_e('Προϊόντα για επαγγελματικές εφαρμογές.', 'spek-theme'); ?></h2>
            <p>
                <?php esc_html_e('Η γκάμα SPEK είναι οργανωμένη ανά κατηγορία, με σαφείς κωδικούς και τεχνικές πληροφορίες για εύκολη επιλογή.', 'spek-theme'); ?>
            </p>
        </div>

        <div class="category-grid">
            <?php foreach ($categories as $index => $category) : ?>
                <?php $category_url = add_query_arg('home_category_group', $category['group'], $products_url); ?>
                <a href="<?php echo esc_url($category_url); ?>" class="category-card" data-reveal style="--reveal-delay: <?php echo esc_attr($index * 60); ?>ms;">
                    <span class="category-card__icon" aria-hidden="true">
                        <?php spek_render_product_category_icon($category['icon'], 'category-card__svg spek-category-icon'); ?>
                    </span>
                    <h3><?php echo esc_html($category['title']); ?></h3>
                    <p><?php echo esc_html($category['text']); ?></p>
                    <span class="category-card__link"><?php esc_html_e('Δείτε κατηγορία', 'spek-theme'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
</section>
