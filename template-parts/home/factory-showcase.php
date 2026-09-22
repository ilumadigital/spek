<?php
/**
 * Homepage manufacturing / facilities image slider.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$iluma_image_base = get_template_directory_uri() . '/assets/images/iluma/webp/';

$slides = [
    [
        'image' => 'spek-ready-website1-16-9.webp',
        'eyebrow' => __('Εγκαταστάσεις SPEK', 'spek-theme'),
        'title' => __('Η παραγωγική μας βάση στο Σχηματάρι', 'spek-theme'),
        'text' => __('Ιδιόκτητες εγκαταστάσεις που συγκεντρώνουν παραγωγή, τεχνογνωσία και διανομή σε ένα οργανωμένο περιβάλλον.', 'spek-theme'),
        'alt' => __('Οι εγκαταστάσεις της SPEK στο Σχηματάρι', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-ready-website1.webp',
        'eyebrow' => __('SPEK Facilities', 'spek-theme'),
        'title' => __('Ένα σύγχρονο περιβάλλον παραγωγής', 'spek-theme'),
        'text' => __('Η παραγωγική βάση της SPEK συνδέει τις διαφορετικές λειτουργίες της εταιρείας σε έναν ενιαίο χώρο.', 'spek-theme'),
        'alt' => __('Πανοραμική άποψη των εγκαταστάσεων της SPEK', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-ready-website1-1-1.webp',
        'eyebrow' => __('Made in Greece', 'spek-theme'),
        'title' => __('Παραγωγή με ελληνική βάση και διεθνή προσανατολισμό', 'spek-theme'),
        'text' => __('Από το Σχηματάρι, η SPEK αναπτύσσει προϊόντα και συνεργασίες για την ελληνική και τη διεθνή αγορά.', 'spek-theme'),
        'alt' => __('Οι εγκαταστάσεις SPEK και το φυσικό τοπίο του Σχηματαρίου', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-ready-website2.webp',
        'eyebrow' => __('Παραγωγή', 'spek-theme'),
        'title' => __('Σύγχρονος μηχανολογικός εξοπλισμός', 'spek-theme'),
        'text' => __('Οργανωμένες γραμμές παραγωγής για σταθερότητα, επαναληψιμότητα και έλεγχο σε κάθε στάδιο.', 'spek-theme'),
        'alt' => __('Γραμμή παραγωγής με μηχανές injection moulding', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-ready-website3.webp',
        'eyebrow' => __('Καλούπια', 'spek-theme'),
        'title' => __('Τεχνογνωσία που ξεκινά από το εργαλείο', 'spek-theme'),
        'text' => __('Η εμπειρία στην κατασκευή και διαχείριση καλουπιών αποτελεί βασικό κομμάτι της παραγωγικής ταυτότητας της SPEK.', 'spek-theme'),
        'alt' => __('Βιομηχανικό καλούπι σε μηχανή παραγωγής', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-ready-website4.webp',
        'eyebrow' => __('Ακρίβεια', 'spek-theme'),
        'title' => __('Κατεργασία με έμφαση στη λεπτομέρεια', 'spek-theme'),
        'text' => __('Η ακρίβεια στην κατεργασία των μεταλλικών μερών υποστηρίζει την ποιότητα και τη συνέπεια του τελικού προϊόντος.', 'spek-theme'),
        'alt' => __('CNC κατεργασία μεταλλικού εξαρτήματος', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-bg-001.webp',
        'eyebrow' => __('Tooling', 'spek-theme'),
        'title' => __('Καλούπια σχεδιασμένα για σταθερή παραγωγή', 'spek-theme'),
        'text' => __('Η ποιότητα του τελικού προϊόντος ξεκινά από την ακρίβεια του εργαλείου και τον σωστό έλεγχο της διαδικασίας.', 'spek-theme'),
        'alt' => __('Μεταλλικό καλούπι σε σύγχρονο χώρο παραγωγής', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-bg-0001.webp',
        'eyebrow' => __('Injection Moulding', 'spek-theme'),
        'title' => __('Παραγωγή με ελεγχόμενη διαδικασία', 'spek-theme'),
        'text' => __('Η συνέπεια στην παραγωγή βασίζεται στον σωστό συνδυασμό εξοπλισμού, εργαλείων και τεχνικής γνώσης.', 'spek-theme'),
        'alt' => __('Καλούπι τοποθετημένο σε μηχανή injection moulding', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-bg-002.webp',
        'eyebrow' => __('Precision Machining', 'spek-theme'),
        'title' => __('Ακρίβεια σε κάθε κατεργασία', 'spek-theme'),
        'text' => __('Η μηχανουργική κατεργασία υποστηρίζει τη δημιουργία εργαλείων και εξαρτημάτων με ελεγχόμενες διαστάσεις.', 'spek-theme'),
        'alt' => __('CNC κατεργασία μεταλλικού καλουπιού', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-bg-003.webp',
        'eyebrow' => __('Engineering', 'spek-theme'),
        'title' => __('Το καλούπι ως κρίσιμο μέρος της λύσης', 'spek-theme'),
        'text' => __('Η παραγωγική τεχνογνωσία της SPEK συνδέει τον σχεδιασμό με το εργαλείο και το τελικό προϊόν.', 'spek-theme'),
        'alt' => __('Βιομηχανικό καλούπι με υδραυλικές συνδέσεις', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-bg-004.webp',
        'eyebrow' => __('Manufacturing Line', 'spek-theme'),
        'title' => __('Οργανωμένη ροή παραγωγής', 'spek-theme'),
        'text' => __('Η διάταξη του εξοπλισμού και των θέσεων εργασίας υποστηρίζει μια καθαρή και αποτελεσματική παραγωγική ροή.', 'spek-theme'),
        'alt' => __('Σειρά σύγχρονων μηχανών injection moulding', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-bg-005.webp',
        'eyebrow' => __('Mould Technology', 'spek-theme'),
        'title' => __('Τεχνική γνώση μέσα στη μηχανή', 'spek-theme'),
        'text' => __('Κάθε καλούπι λειτουργεί ως ένας ακριβής μηχανισμός που πρέπει να αποδίδει σταθερά σε κάθε κύκλο παραγωγής.', 'spek-theme'),
        'alt' => __('Κοντινή άποψη καλουπιού σε μηχανή παραγωγής', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-bg-006.webp',
        'eyebrow' => __('Manufacturing', 'spek-theme'),
        'title' => __('Παραγωγική οργάνωση σε κάθε στάδιο', 'spek-theme'),
        'text' => __('Ο εξοπλισμός και η ροή της παραγωγής οργανώνονται ώστε κάθε διαδικασία να είναι καθαρή, ελεγχόμενη και επαναλήψιμη.', 'spek-theme'),
        'alt' => __('Πανοραμική άποψη σύγχρονου χώρου παραγωγής', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-bg-007.webp',
        'eyebrow' => __('Engineering', 'spek-theme'),
        'title' => __('Από τον σχεδιασμό στην παραγωγική λύση', 'spek-theme'),
        'text' => __('Σχεδιασμός, εργαλεία και παραγωγή λειτουργούν ως μία ενιαία διαδικασία εξέλιξης.', 'spek-theme'),
        'alt' => __('Μεταλλικό καλούπι σε σύγχρονο βιομηχανικό περιβάλλον', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-bg-008.webp',
        'eyebrow' => __('Precision', 'spek-theme'),
        'title' => __('Τεχνολογία που υπηρετεί την αξιοπιστία', 'spek-theme'),
        'text' => __('Κάθε στάδιο παραγωγής υποστηρίζει έναν κοινό στόχο: προϊόντα με σταθερή λειτουργία και αξιόπιστη καθημερινή χρήση.', 'spek-theme'),
        'alt' => __('CNC μηχανή κατά τη διάρκεια κατεργασίας μεταλλικού εξαρτήματος', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-bg-009.webp',
        'eyebrow' => __('Production Floor', 'spek-theme'),
        'title' => __('Σύγχρονη παραγωγή με καθαρή οργάνωση', 'spek-theme'),
        'text' => __('Ο εξοπλισμός παραγωγής λειτουργεί σε οργανωμένο περιβάλλον με έμφαση στη συνέπεια και τον έλεγχο.', 'spek-theme'),
        'alt' => __('Σύγχρονη γραμμή παραγωγής σε φωτεινό βιομηχανικό χώρο', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-bg-010.webp',
        'eyebrow' => __('Injection Technology', 'spek-theme'),
        'title' => __('Το εργαλείο στο κέντρο της παραγωγής', 'spek-theme'),
        'text' => __('Η σωστή συνεργασία μηχανής και καλουπιού αποτελεί βασική προϋπόθεση για σταθερό αποτέλεσμα.', 'spek-theme'),
        'alt' => __('Μηχανή injection moulding με μεταλλικό καλούπι', 'spek-theme'),
        'position' => 'center center',
    ],
]
?>

<section class="section spek-visual-story" aria-labelledby="spek-visual-story-title">
    <div class="container">
        <div class="spek-visual-story__header" data-reveal>
            <div>
                <span class="eyebrow"><?php esc_html_e('Inside SPEK', 'spek-theme'); ?></span>
                <h2 id="spek-visual-story-title"><?php esc_html_e('Εκεί όπου η τεχνογνωσία γίνεται προϊόν.', 'spek-theme'); ?></h2>
            </div>

            <div class="spek-visual-story__intro">
                <p><?php esc_html_e('Μια ματιά στις εγκαταστάσεις, τον εξοπλισμό και την παραγωγική διαδικασία πίσω από τα προϊόντα SPEK.', 'spek-theme'); ?></p>
                <a class="spek-visual-story__link" href="<?php echo esc_url(home_url('/company/')); ?>">
                    <?php esc_html_e('Γνωρίστε την εταιρεία', 'spek-theme'); ?>
                    <span aria-hidden="true">↗</span>
                </a>
            </div>
        </div>

        <div class="spek-media-slider" data-spek-slider data-autoplay="true" data-reveal style="--reveal-delay: 90ms;">
            <div class="spek-media-slider__viewport" data-slider-viewport>
                <div class="spek-media-slider__track" data-slider-track>
                    <?php foreach ($slides as $index => $slide) : ?>
                        <figure class="spek-media-slider__slide<?php echo $index === 0 ? ' is-active' : ''; ?>" data-slider-slide>
                            <img
                                src="<?php echo esc_url($iluma_image_base . $slide['image']); ?>"
                                alt="<?php echo esc_attr($slide['alt']); ?>"
                                loading="lazy"
                                decoding="async"
                                style="object-position: <?php echo esc_attr($slide['position']); ?>;"
                            >
                            <figcaption class="spek-media-slider__caption">
                                <span class="spek-media-slider__eyebrow"><?php echo esc_html($slide['eyebrow']); ?></span>
                                <strong><?php echo esc_html($slide['title']); ?></strong>
                                <p><?php echo esc_html($slide['text']); ?></p>
                            </figcaption>
                        </figure>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="spek-media-slider__controls">
                <div class="spek-media-slider__status" aria-live="polite">
                    <span data-slider-current>01</span>
                    <i aria-hidden="true"></i>
                    <span><?php echo esc_html(str_pad((string) count($slides), 2, '0', STR_PAD_LEFT)); ?></span>
                </div>

                <div class="spek-media-slider__dots" data-slider-dots aria-label="<?php esc_attr_e('Επιλογή διαφάνειας', 'spek-theme'); ?>"></div>

                <div class="spek-media-slider__arrows">
                    <button type="button" class="spek-media-slider__arrow" data-slider-prev aria-label="<?php esc_attr_e('Προηγούμενη εικόνα', 'spek-theme'); ?>">
                        <span aria-hidden="true">←</span>
                    </button>
                    <button type="button" class="spek-media-slider__arrow" data-slider-next aria-label="<?php esc_attr_e('Επόμενη εικόνα', 'spek-theme'); ?>">
                        <span aria-hidden="true">→</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
