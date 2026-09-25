<?php
/**
 * Company production process.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$iluma_image_base = get_template_directory_uri() . '/assets/images/';

$steps = [
    [
        'number' => '01',
        'title' => __('Σχεδιασμός', 'spek-theme'),
        'text' => __('Μελετάμε την εφαρμογή και μετατρέπουμε μια πραγματική ανάγκη σε λειτουργική, παραγωγικά ώριμη λύση.', 'spek-theme'),
        'image' => 'spek-website-factory-line.png',
        'alt' => __('Μεταλλικό καλούπι σε σύγχρονο βιομηχανικό περιβάλλον', 'spek-theme'),
    ],
    [
        'number' => '02',
        'title' => __('Εργαλεία & καλούπια', 'spek-theme'),
        'text' => __('Η τεχνογνωσία στην κατασκευή καλουπιών αποτελεί μέρος της ιστορίας και της παραγωγικής ταυτότητας της SPEK.', 'spek-theme'),
        'image' => 'spek-website-factory-line2.png',
        'alt' => __('Βιομηχανικό καλούπι τοποθετημένο σε μηχανή παραγωγής', 'spek-theme'),
    ],
    [
        'number' => '03',
        'title' => __('Παραγωγή', 'spek-theme'),
        'text' => __('Σύγχρονος μηχανολογικός εξοπλισμός και οργανωμένες διαδικασίες υποστηρίζουν σταθερότητα και επαναληψιμότητα.', 'spek-theme'),
        'image' => 'spek-website-factory-line3.png',
        'alt' => __('Σύγχρονη γραμμή παραγωγής με μηχανές injection moulding', 'spek-theme'),
    ],
    [
        'number' => '04',
        'title' => __('Έλεγχος ποιότητας', 'spek-theme'),
        'text' => __('Η ποιότητα αντιμετωπίζεται ως συνεχής διαδικασία, από την πρώτη ύλη και την παραγωγή έως το τελικό προϊόν.', 'spek-theme'),
        'image' => 'spek-website-factory-line4.png',
        'alt' => __('CNC κατεργασία μεταλλικού εξαρτήματος υψηλής ακρίβειας', 'spek-theme'),
    ],
];
?>

<section class="section company-production">
    <div class="container">
        <div class="company-production__intro" data-reveal>
            <div>
                <span class="eyebrow"><?php esc_html_e('Από την ιδέα στο προϊόν', 'spek-theme'); ?></span>
                <h2><?php esc_html_e('Η παραγωγή είναι μέρος της ταυτότητάς μας.', 'spek-theme'); ?></h2>
            </div>
            <p><?php esc_html_e('Η παραγωγική γνώση δεν είναι απλώς ένα στάδιο της διαδικασίας. Είναι ο τρόπος με τον οποίο σχεδιάζουμε, εξελίσσουμε και ελέγχουμε κάθε λύση που φέρει το όνομα SPEK.', 'spek-theme'); ?></p>
        </div>

        <div class="company-production__grid company-production__grid--media">
            <?php foreach ($steps as $index => $step) : ?>
                <article class="company-process-card company-process-card--media" data-reveal style="--reveal-delay: <?php echo esc_attr($index * 70); ?>ms;">
                    <div class="company-process-card__media">
                        <img
                            src="<?php echo esc_url($iluma_image_base . $step['image']); ?>"
                            alt="<?php echo esc_attr($step['alt']); ?>"
                            loading="lazy"
                            decoding="async"
                        >
                        <span class="company-process-card__number"><?php echo esc_html($step['number']); ?></span>
                    </div>
                    <div class="company-process-card__body">
                        <h3><?php echo esc_html($step['title']); ?></h3>
                        <p><?php echo esc_html($step['text']); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="company-facility company-facility--photo" data-reveal>
            <div class="company-facility__content">
                <span class="eyebrow"><?php esc_html_e('Παραγωγική βάση', 'spek-theme'); ?></span>
                <h2><?php esc_html_e('8.100 m² αφιερωμένα στην εξέλιξη.', 'spek-theme'); ?></h2>
                <p><?php esc_html_e('Το 2008 η SPEK απέκτησε οικόπεδο 8.100 m² στο Σχηματάρι και το 2010 μεταφέρθηκε στις νέες ιδιόκτητες εγκαταστάσεις της, δημιουργώντας παράλληλα νέο κέντρο διανομής.', 'spek-theme'); ?></p>
            </div>
            <div class="company-facility__visual company-facility__visual--photo">
                <img
                    src="<?php echo esc_url($iluma_image_base . 'spek-website-new-factory.png'); ?>"
                    alt="<?php esc_attr_e('Οι ιδιόκτητες εγκαταστάσεις της SPEK στο Σχηματάρι', 'spek-theme'); ?>"
                    loading="lazy"
                    decoding="async"
                >
                <div class="company-facility__metric">
                    <strong>8.100</strong>
                    <span>m²</span>
                </div>
            </div>
        </div>

        <div class="company-production__mosaic" data-reveal>
            <figure class="company-production__mosaic-main">
                <img
                    src="<?php echo esc_url($iluma_image_base . 'spek-factory-line.png'); ?>"
                    alt="<?php esc_attr_e('Πανοραμική άποψη της παραγωγής SPEK', 'spek-theme'); ?>"
                    loading="lazy"
                    decoding="async"
                >
                <figcaption><?php esc_html_e('Οργανωμένη παραγωγή', 'spek-theme'); ?></figcaption>
            </figure>
            <figure>
                <img
                    src="<?php echo esc_url($iluma_image_base . 'spek-factory-line-2.png'); ?>"
                    alt="<?php esc_attr_e('Καλούπι σε μηχανή παραγωγής SPEK', 'spek-theme'); ?>"
                    loading="lazy"
                    decoding="async"
                >
                <figcaption><?php esc_html_e('Εργαλεία & καλούπια', 'spek-theme'); ?></figcaption>
            </figure>
            <figure>
                <img
                    src="<?php echo esc_url($iluma_image_base . 'spek-website-main.png'); ?>"
                    alt="<?php esc_attr_e('CNC κατεργασία στη γραμμή παραγωγής SPEK', 'spek-theme'); ?>"
                    loading="lazy"
                    decoding="async"
                >
                <figcaption><?php esc_html_e('Ακρίβεια κατεργασίας', 'spek-theme'); ?></figcaption>
            </figure>
        </div>
    </div>
</section>
