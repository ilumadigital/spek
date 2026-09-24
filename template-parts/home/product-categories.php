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

if (!function_exists('spek_home_category_icon')) {
    function spek_home_category_icon(string $icon): void
    {
        echo '<svg class="category-card__svg" viewBox="0 0 64 64" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="2.35" stroke-linecap="round" stroke-linejoin="round">';

        switch ($icon) {
            case 'mechanisms':
                echo '<path d="M14 16h36v34H14z"/><path d="M20 13h24v5H20z"/><path d="M29 20h8v23h-8z"/><path d="M27 43h12v5H27z"/><circle cx="44" cy="31" r="5"/><path d="M39 31h-5"/>';
                break;
            case 'float':
                echo '<circle cx="19" cy="41" r="11"/><path d="M26 34 42 21"/><circle cx="44" cy="19" r="3"/><path d="M47 19h7v8h-8"/><path d="M48 27v18h-7V25"/><path d="M51 16h8v6h-8"/>';
                break;
            case 'siphon':
                echo '<path d="M21 11h12v8H21z"/><path d="M24 19v8"/><path d="M20 27h16v17a8 8 0 0 1-16 0z"/><path d="M36 32h12v8h-8"/><path d="M48 31h8v10h-8"/>';
                break;
            case 'seat':
                echo '<ellipse cx="32" cy="23" rx="15" ry="18"/><path d="M21 39h22"/><path d="M24 39v5h16v-5"/><ellipse cx="32" cy="49" rx="17" ry="9"/><ellipse cx="32" cy="49" rx="10" ry="4.5"/>';
                break;
            case 'cistern':
                echo '<rect x="13" y="14" width="38" height="29" rx="4"/><path d="M11 17h42"/><path d="M28 11h8v5h-8z"/><circle cx="44" cy="27" r="4"/><path d="M48 27h7"/><path d="M28 43h8v10h-8"/>';
                break;
            case 'bathroom':
                echo '<path d="M18 43h31v8H18z"/><path d="M45 43V18a7 7 0 0 0-7-7h-9"/><path d="M29 11v7"/><path d="M23 18h12"/><path d="M24 22v3m5-3v3m5-3v3"/><circle cx="45" cy="34" r="4"/><path d="M45 38v5"/>';
                break;
            case 'seals':
                echo '<ellipse cx="27" cy="32" rx="13" ry="18"/><ellipse cx="27" cy="32" rx="7" ry="11"/><ellipse cx="43" cy="45" rx="10" ry="5"/><path d="M48 18c4 5 6 8 6 11a6 6 0 0 1-12 0c0-3 2-6 6-11z"/>';
                break;
            default:
                echo '<path d="M12 29h40v23H12z"/><path d="M15 29l6-9h22l6 9"/><circle cx="23" cy="27" r="6"/><path d="M35 20h7v9h-7z"/><path d="M39 16v4m-6 0h12"/><path d="M20 39h9m7 0h8m-22 7h20"/>';
                break;
        }

        echo '</svg>';
    }
}
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
                        <?php spek_home_category_icon($category['icon']); ?>
                    </span>
                    <h3><?php echo esc_html($category['title']); ?></h3>
                    <p><?php echo esc_html($category['text']); ?></p>
                    <span class="category-card__link"><?php esc_html_e('Δείτε κατηγορία', 'spek-theme'); ?></span>
                </a>
            <?php endforeach; ?>
        </div>

    </div>
</section>
