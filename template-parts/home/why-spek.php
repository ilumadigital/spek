<?php
/**
 * SPEK values: the same editorial water-flow section is shared by Home & Company.
 * Four SPEK initials in the value badges; unnumbered, clean benefits below.
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
        'image_file' => 'spek-value-1.webp',
        'image' => 'https://images.unsplash.com/photo-1608303588026-884930af2559?auto=format&fit=crop&w=740&q=82',
    ],
    [
        'letter' => 'Π',
        'title'  => __('Πρωτοπορούμε', 'spek-theme'),
        'text'   => __('Αναζητούμε πρακτικές ιδέες που βελτιώνουν τα προϊόντα, τις εφαρμογές και την εμπειρία του επαγγελματία.', 'spek-theme'),
        'image_file' => 'spek-value-2.webp',
        'image' => 'https://images.unsplash.com/photo-1689942007817-679c21614b25?auto=format&fit=crop&w=740&q=82',
    ],
    [
        'letter' => 'Ε',
        'title'  => __('Εξελισσόμαστε', 'spek-theme'),
        'text'   => __('Επενδύουμε διαρκώς σε τεχνογνωσία, παραγωγή και νέες προϊοντικές δυνατότητες.', 'spek-theme'),
        'image_file' => 'spek-value-3.webp',
        'image' => 'https://images.unsplash.com/photo-1764835746713-34a671e73569?auto=format&fit=crop&w=740&q=82',
    ],
    [
        'letter' => 'Κ',
        'title'  => __('Κατασκευάζουμε', 'spek-theme'),
        'text'   => __('Μετατρέπουμε τον σχεδιασμό σε αξιόπιστα προϊόντα, με συνέπεια, έλεγχο και προσοχή στη λεπτομέρεια.', 'spek-theme'),
        'image_file' => 'spek-value-4.webp',
        'image' => 'https://images.unsplash.com/photo-1698031610524-c35e7ebbba2c?auto=format&fit=crop&w=740&q=82',
    ],
];

?>

<section class="section why-spek why-spek--flow" aria-labelledby="spek-values-heading">
    <div class="container why-spek__container">
        <header class="why-spek__intro" data-reveal>
            <span class="eyebrow"><?php esc_html_e('Σχετικά με τη SPEK', 'spek-theme'); ?></span>
            <h2 id="spek-values-heading" class="screen-reader-text"><?php esc_html_e('Οι αξίες της SPEK', 'spek-theme'); ?></h2>
            <p><?php esc_html_e('Η SPEK οργανώνει τη γκάμα και τις υπηρεσίες της με βάση τις ανάγκες τεχνικών, συνεργατών και σημείων πώλησης.', 'spek-theme'); ?></p>
        </header>

        <div class="why-spek__values-heading" data-reveal>
            <img class="why-spek__wordmark-logo"
                src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                alt="<?php esc_attr_e('SPEK', 'spek-theme'); ?>"
                width="228" height="78" loading="lazy" decoding="async">
            <p><?php esc_html_e('Τέσσερις λέξεις που εκφράζουν τον τρόπο με τον οποίο δουλεύουμε.', 'spek-theme'); ?></p>
        </div>

        <div class="why-spek__stage">
            <!-- Decorative water and connector lines belong ONLY to the four values. -->
            <svg class="why-spek__flow-water" viewBox="0 0 1440 470" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="spekValuesWater" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0" stop-color="#d7f1fc" stop-opacity=".04"/>
                        <stop offset=".45" stop-color="#67c0e5" stop-opacity=".28"/>
                        <stop offset="1" stop-color="#d7edf9" stop-opacity=".03"/>
                    </linearGradient>
                </defs>
                <path d="M-45 315 C150 125 260 395 424 252 S714 125 867 290 S1155 395 1480 180"
                    stroke="url(#spekValuesWater)" stroke-width="74" fill="none"/>
                <path d="M-30 310 C150 135 259 379 424 247 S709 137 861 285 S1160 385 1460 183"
                    stroke="#8acbed" stroke-opacity=".46" stroke-width="2.5" fill="none"/>
                <path d="M-30 324 C150 155 259 399 424 261 S709 150 861 299 S1160 405 1460 201"
                    stroke="#c2eaf9" stroke-opacity=".78" stroke-width="2" fill="none"/>
                <path d="M-30 295 C150 116 259 370 424 229 S709 118 861 270 S1160 365 1460 166"
                    stroke="#d0effa" stroke-opacity=".72" stroke-width="1.5" fill="none"/>
            </svg>
            <svg class="why-spek__flow-connector" viewBox="0 0 1440 400" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                <path d="M180 146 C285 62 305 298 430 242 S650 83 755 154 S1006 305 1120 179 S1284 100 1380 170"
                    stroke="#12395e" stroke-width="1.7" stroke-opacity=".75" fill="none"/>
                <circle cx="430" cy="242" r="6.5" fill="#c49b4e" stroke="#fff" stroke-width="3"/>
                <circle cx="755" cy="154" r="6.5" fill="#12395e" stroke="#fff" stroke-width="3"/>
                <circle cx="1120" cy="179" r="6.5" fill="#c49b4e" stroke="#fff" stroke-width="3"/>
            </svg>
            <div class="why-spek__values">
                <?php foreach ($values as $index => $value) : ?>
                    <article class="why-spek__value why-spek__value--<?php echo esc_attr($index + 1); ?>" data-reveal style="--reveal-delay: <?php echo esc_attr(($index * 65) + 90); ?>ms;">
                        <div class="why-spek__value-picture" aria-hidden="true">
                            <?php
                                $local_image = 'assets/images/values/' . $value['image_file'];
                                $image_url = file_exists(get_template_directory() . '/' . $local_image)
                                    ? get_template_directory_uri() . '/' . $local_image
                                    : $value['image'];
                                ?>
                            <img src="<?php echo esc_url($image_url); ?>" alt="" loading="lazy" decoding="async">
                        </div>
                        <span class="why-spek__value-letter" aria-hidden="true"><?php echo esc_html($value['letter']); ?></span>
                        <div class="why-spek__value-copy">
                            <h3><?php echo esc_html($value['title']); ?></h3>
                            <p><?php echo esc_html($value['text']); ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <?php
        // Home shows this band directly under the hero video, not twice.
        if (!is_front_page() && !(function_exists('spek_is_english_home_request') && spek_is_english_home_request())) {
            get_template_part('template-parts/home/practical-benefits');
        }
        ?>

    </div>
</section>
