<?php
/**
 * Homepage manufacturing / facilities image slider.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$iluma_image_base = get_template_directory_uri() . '/assets/images/';

$slides = [
    [
        'image' => 'spek-factory-line-blue.png',
        'eyebrow' => __('Παραγωγή', 'spek-theme'),
        'title' => __('Σύγχρονο περιβάλλον παραγωγής', 'spek-theme'),
        'text' => __('Οργανωμένες γραμμές παραγωγής και τεχνική γνώση σε ένα σύγχρονο βιομηχανικό περιβάλλον.', 'spek-theme'),
        'alt' => __('Γραμμή παραγωγής SPEK', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-factory-line-2-blue.png',
        'eyebrow' => __('Υποδομή', 'spek-theme'),
        'title' => __('Παραγωγική βάση με καθαρή οργάνωση', 'spek-theme'),
        'text' => __('Εξοπλισμός, ροή εργασίας και παραγωγικές διαδικασίες λειτουργούν ως ένα ενιαίο σύστημα.', 'spek-theme'),
        'alt' => __('Παραγωγικός χώρος SPEK', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-factory-line-3-blue.png',
        'eyebrow' => __('Τεχνογνωσία', 'spek-theme'),
        'title' => __('Η λεπτομέρεια πίσω από κάθε προϊόν', 'spek-theme'),
        'text' => __('Η παραγωγική εμπειρία της SPEK συνδέει τον σχεδιασμό, το εργαλείο και το τελικό αποτέλεσμα.', 'spek-theme'),
        'alt' => __('Τεχνική παραγωγική διαδικασία SPEK', 'spek-theme'),
        'position' => 'center center',
    ],
    [
        'image' => 'spek-website-factory-line-4-blue.png',
        'eyebrow' => __('Ποιότητα', 'spek-theme'),
        'title' => __('Συνέπεια σε κάθε στάδιο παραγωγής', 'spek-theme'),
        'text' => __('Κάθε στάδιο οργανώνεται με στόχο σταθερή ποιότητα, επαναληψιμότητα και αξιόπιστη λειτουργία.', 'spek-theme'),
        'alt' => __('Σύγχρονη γραμμή παραγωγής SPEK', 'spek-theme'),
        'position' => 'center center',
    ],
];
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
