<?php
/** International presence, shown after the Quality section. @package SpekTheme */
if (!defined('ABSPATH')) { exit; }
?>

<section class="section company-global company-global--premium">
    <div class="container">
        <div class="company-global__panel" data-reveal>
            <div class="company-global__inner">
                <div class="company-global__copy">
                    <span class="eyebrow"><?php esc_html_e('Διεθνής παρουσία', 'spek-theme'); ?></span>

                    <h2><?php esc_html_e('Από την Ελλάδα, σε διεθνείς αγορές', 'spek-theme'); ?></h2>

                    <p class="company-global__lead">
                        <?php esc_html_e('Η εξωστρέφεια αποτελεί μέρος της πορείας μας από το 1992. Από την ελληνική αγορά, η SPEK ανέπτυξε σταδιακά συνεργασίες και εξαγωγική δραστηριότητα σε αγορές της Ευρώπης, της Μεσογείου και της Μέσης Ανατολής.', 'spek-theme'); ?>
                    </p>

                    <div class="company-global__proof" aria-label="<?php esc_attr_e('Στοιχεία διεθνούς παρουσίας', 'spek-theme'); ?>">
                        <div class="company-global__proof-item">
                            <strong>1992</strong>
                            <span><?php esc_html_e('Πρώτες εξαγωγές', 'spek-theme'); ?></span>
                        </div>

                        <div class="company-global__regions">
                            <span><?php esc_html_e('Ευρώπη', 'spek-theme'); ?></span>
                            <span><?php esc_html_e('Μεσόγειος', 'spek-theme'); ?></span>
                            <span><?php esc_html_e('Μέση Ανατολή', 'spek-theme'); ?></span>
                        </div>
                    </div>
                </div>

                <div class="company-global__visual" aria-hidden="true">
                    <div class="company-global__map">
                        <div class="company-global__glow"></div>

                        <svg class="company-global__globe" viewBox="0 0 520 520" role="presentation">
                            <circle cx="260" cy="260" r="202"></circle>
                            <ellipse cx="260" cy="260" rx="202" ry="76"></ellipse>
                            <ellipse cx="260" cy="260" rx="202" ry="142"></ellipse>
                            <ellipse cx="260" cy="260" rx="78" ry="202"></ellipse>
                            <ellipse cx="260" cy="260" rx="144" ry="202"></ellipse>

                            <path class="company-global__route company-global__route--one" d="M248 276 C 185 210, 122 183, 84 137"></path>
                            <path class="company-global__route company-global__route--two" d="M268 266 C 341 224, 398 188, 442 150"></path>
                            <path class="company-global__route company-global__route--three" d="M270 284 C 344 316, 389 350, 432 398"></path>
                        </svg>

                        <span class="company-global__pin company-global__pin--one"></span>
                        <span class="company-global__pin company-global__pin--two"></span>
                        <span class="company-global__pin company-global__pin--three"></span>

                        <div class="company-global__hub">
                            <img
                                src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                                alt=""
                            >
                            <span><?php esc_html_e('Ελλάδα', 'spek-theme'); ?></span>
                        </div>

                        <span class="company-global__region-label company-global__region-label--eu"><?php esc_html_e('Ευρώπη', 'spek-theme'); ?></span>
                        <span class="company-global__region-label company-global__region-label--med"><?php esc_html_e('Μεσόγειος', 'spek-theme'); ?></span>
                        <span class="company-global__region-label company-global__region-label--me"><?php esc_html_e('Μέση Ανατολή', 'spek-theme'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

