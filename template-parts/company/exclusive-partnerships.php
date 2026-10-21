<?php
/**
 * Exclusive European partners featured on the Company page.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$exclusive_partners = [
    [
        'name' => 'McAlpine',
        'country' => __('Ηνωμένο Βασίλειο', 'spek-theme'),
        'description' => __('Ηγέτης στην κατασκευή των πιο αξιόπιστων υδραυλικών προϊόντων. Σημείο αναφοράς ως το καλύτερο της αγοράς θεωρείται το σωληνωτό σιφόνι νεροχύτη, καθώς και το σιφόνι σπιράλ νιπτήρα και νεροχύτη.', 'spek-theme'),
        'logo' => 'https://mcalpine.pl/wp-content/uploads/2024/10/cropped-Logo-na-Strone.png',
        'website' => 'https://mcalpine.pl/',
        'slug' => 'mcalpine',
    ],
    [
        'name' => 'IDROSPANIA',
        'country' => __('Ισπανία', 'spek-theme'),
        'description' => __('Κορυφαία εταιρεία στην παραγωγή ευρωπαϊκών μηχανισμών και φλοτέρ για καζανάκια. Παρέχει αξιόπιστες λύσεις σε όλες τις περιπτώσεις σύνδεσης στο καζανάκι.', 'spek-theme'),
        'logo' => 'https://idrospania.es/wp-content/uploads/2021/10/logotipo-idrospania.svg',
        'website' => 'https://idrospania.es/',
        'slug' => 'idrospania',
    ],
    [
        'name' => 'JBMC S.A.',
        'country' => __('Πορτογαλία', 'spek-theme'),
        'description' => __('Άριστα, ποιοτικά, μοντέρνα καλύμματα λεκανών τουαλέτας. Τα καλύμματα καλύπτουν την πλειονότητα των κεραμικών λεκανών της αγοράς. Διαθέτει μοντέλα καλυμμάτων σε Duroplast και Thermoplast, με το σύστημα soft/close και clip-off (εύκολης αφαίρεσης).', 'spek-theme'),
        'logo' => 'https://www.jbmc.pt/wp-content/uploads/2024/02/logo-jbmc-toilet-seats.png',
        'website' => 'https://www.jbmc.pt/',
        'slug' => 'jbmc',
    ],
    [
        'name' => 'L.B. PLAST S.r.l.',
        'country' => __('Ιταλία', 'spek-theme'),
        'description' => __('Διαθέτει πλήρη γκάμα πιστοποιημένων προϊόντων για κάλυψη όλων των αναγκών στον χώρο της κουζίνας και του μπάνιου στις κατηγορίες βαλβίδες και σιφόνια νεροχυτών/νιπτήρων.', 'spek-theme'),
        'logo' => 'https://lbplast.com/wp-content/uploads/2025/06/logo-LBPlast.svg',
        'website' => 'https://lbplast.com/',
        'slug' => 'lbplast',
    ],
    [
        'name' => 'Lamaplast',
        'country' => __('Ελλάδα', 'spek-theme'),
        'description' => __('Μία από τις κορυφαίες βιομηχανίες της Ελλάδας και των Βαλκανίων στον τομέα της παραγωγής πλαστικών αντικειμένων μπάνιου – σιφονιών. Αποκλειστική διάθεση πανελλαδικά όλων των προϊόντων της εκτός Β. Ελλάδας και Κρήτης.', 'spek-theme'),
        'logo' => 'https://lamaplast.gr/wp-content/webp-express/webp-images/uploads/2021/04/logo-clean-min.png.webp',
        'website' => 'https://lamaplast.gr/',
        'slug' => 'lamaplast',
    ],
];
?>

<section id="exclusive-partnerships" class="section company-partnerships" aria-labelledby="company-partnerships-title">
    <div class="container">
        <div class="company-partnerships__shell">
            <div class="company-partnerships__heading" data-reveal>
                <div class="company-partnerships__heading-main">
                    <span class="eyebrow"><?php esc_html_e('Επιλεγμένοι ευρωπαϊκοί οίκοι', 'spek-theme'); ?></span>
                    <h2 id="company-partnerships-title"><?php esc_html_e('Αποκλειστικές συνεργασίες', 'spek-theme'); ?></h2>
                </div>
                <p>
                    <?php esc_html_e('Συνεργαζόμαστε με καταξιωμένους κατασκευαστές από την Ευρώπη, συνδυάζοντας εξειδικευμένη τεχνογνωσία, ποιοτικά προϊόντα και ολοκληρωμένες λύσεις για την ελληνική αγορά.', 'spek-theme'); ?>
                </p>
            </div>

            <div class="company-partnerships__grid">
                <?php foreach ($exclusive_partners as $index => $partner) : ?>
                    <a
                        class="company-partner-card company-partner-card--<?php echo esc_attr($partner['slug']); ?>"
                        href="<?php echo esc_url($partner['website']); ?>"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="<?php echo esc_attr(sprintf(
                            /* translators: %s: Partner company name. */
                            __('Επίσκεψη στην ιστοσελίδα της %s (νέα καρτέλα)', 'spek-theme'),
                            $partner['name']
                        )); ?>"
                        data-reveal
                        style="--reveal-delay: <?php echo esc_attr(($index % 3) * 80); ?>ms;"
                    >
                        <div class="company-partner-card__logo-stage">
                            <img
                                src="<?php echo esc_url($partner['logo']); ?>"
                                alt="<?php echo esc_attr(sprintf(
                                    /* translators: %s: Partner company name. */
                                    __('Λογότυπο %s', 'spek-theme'),
                                    $partner['name']
                                )); ?>"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>

                        <div class="company-partner-card__content">
                            <span class="company-partner-card__country"><?php echo esc_html($partner['country']); ?></span>
                            <h3><?php echo esc_html($partner['name']); ?></h3>
                            <p><?php echo esc_html($partner['description']); ?></p>
                        </div>
                        <span class="company-partner-card__visit" aria-hidden="true">
                            <?php esc_html_e('Επίσκεψη στην ιστοσελίδα', 'spek-theme'); ?>
                            <svg viewBox="0 0 20 20" fill="none" focusable="false">
                                <path d="M5 15L15 5M6 5h9v9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
