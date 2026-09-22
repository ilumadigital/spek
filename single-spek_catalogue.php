<?php
/**
 * Single Catalogue template with interactive PDF viewer.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $catalogue_id = get_the_ID();
    $pdf_url = function_exists('spek_get_catalogue_pdf_url') ? spek_get_catalogue_pdf_url($catalogue_id) : '';
    $download_name = sanitize_file_name(get_the_title($catalogue_id) . '.pdf');
    ?>
    <main id="main" class="site-main single-catalogue-page">
        <section class="catalogue-reader-hero">
            <div class="container catalogue-reader-hero__inner">
                <div>
                    <a class="catalogue-reader-back" href="<?php echo esc_url(spek_page_url('catalogues/')); ?>">
                        <span aria-hidden="true">←</span>
                        <?php esc_html_e('Όλοι οι κατάλογοι', 'spek-theme'); ?>
                    </a>
                    <span class="eyebrow"><?php esc_html_e('Online catalogue', 'spek-theme'); ?></span>
                    <h1><?php the_title(); ?></h1>
                    <div class="content-area"><?php the_content(); ?></div>
                </div>

                <?php if ($pdf_url !== '') : ?>
                    <a class="button button-primary" href="<?php echo esc_url($pdf_url); ?>" download="<?php echo esc_attr($download_name); ?>" target="_blank" rel="noopener">
                        <?php esc_html_e('Λήψη PDF', 'spek-theme'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($pdf_url !== '') : ?>
            <section class="catalogue-reader-section">
                <div class="container container--catalogue-reader">
                    <div
                        class="catalogue-viewer"
                        data-catalogue-viewer
                        data-pdf-url="<?php echo esc_url($pdf_url); ?>"
                        data-pdfjs-url="https://cdn.jsdelivr.net/npm/pdfjs-dist@6.3.289/build/pdf.min.mjs"
                        data-pdf-worker-url="https://cdn.jsdelivr.net/npm/pdfjs-dist@6.3.289/build/pdf.worker.min.mjs"
                    >
                        <div class="catalogue-viewer__toolbar">
                            <form class="catalogue-viewer__search" data-pdf-search-form role="search">
                                <label class="screen-reader-text" for="catalogue-search-<?php echo esc_attr((string) $catalogue_id); ?>">
                                    <?php esc_html_e('Αναζήτηση μέσα στον κατάλογο', 'spek-theme'); ?>
                                </label>
                                <span class="catalogue-viewer__search-icon" aria-hidden="true">⌕</span>
                                <input
                                    id="catalogue-search-<?php echo esc_attr((string) $catalogue_id); ?>"
                                    type="search"
                                    data-pdf-search-input
                                    placeholder="<?php esc_attr_e('Κωδικός ή ονομασία προϊόντος…', 'spek-theme'); ?>"
                                    autocomplete="off"
                                >
                                <button type="submit" class="catalogue-viewer__search-button">
                                    <?php esc_html_e('Αναζήτηση', 'spek-theme'); ?>
                                </button>
                            </form>

                            <div class="catalogue-viewer__search-results" data-pdf-search-results hidden>
                                <button type="button" data-pdf-match-prev aria-label="<?php esc_attr_e('Προηγούμενο αποτέλεσμα', 'spek-theme'); ?>">‹</button>
                                <span data-pdf-search-status></span>
                                <button type="button" data-pdf-match-next aria-label="<?php esc_attr_e('Επόμενο αποτέλεσμα', 'spek-theme'); ?>">›</button>
                            </div>

                            <div class="catalogue-viewer__tools">
                                <span class="catalogue-viewer__page-status" data-pdf-page-status aria-live="polite">— / —</span>

                                <div class="catalogue-viewer__tool-group" aria-label="<?php esc_attr_e('Zoom', 'spek-theme'); ?>">
                                    <button type="button" data-pdf-zoom-out aria-label="<?php esc_attr_e('Σμίκρυνση', 'spek-theme'); ?>">−</button>
                                    <button type="button" data-pdf-zoom-reset aria-label="<?php esc_attr_e('Προσαρμογή στη σελίδα', 'spek-theme'); ?>">100%</button>
                                    <button type="button" data-pdf-zoom-in aria-label="<?php esc_attr_e('Μεγέθυνση', 'spek-theme'); ?>">+</button>
                                </div>

                                <button type="button" class="catalogue-viewer__fullscreen" data-pdf-fullscreen>
                                    <span aria-hidden="true">⛶</span>
                                    <span><?php esc_html_e('Πλήρης οθόνη', 'spek-theme'); ?></span>
                                </button>

                                <a class="catalogue-viewer__download" href="<?php echo esc_url($pdf_url); ?>" download="<?php echo esc_attr($download_name); ?>" target="_blank" rel="noopener">
                                    <span aria-hidden="true">↓</span>
                                    <span><?php esc_html_e('PDF', 'spek-theme'); ?></span>
                                </a>
                            </div>
                        </div>

                        <div class="catalogue-viewer__notice" data-pdf-notice hidden></div>

                        <div class="catalogue-viewer__stage" data-pdf-stage tabindex="0">
                            <button class="catalogue-viewer__nav catalogue-viewer__nav--prev" type="button" data-pdf-prev aria-label="<?php esc_attr_e('Προηγούμενη σελίδα', 'spek-theme'); ?>">
                                <span aria-hidden="true">‹</span>
                            </button>

                            <div class="catalogue-viewer__spread" data-pdf-spread aria-live="polite"></div>

                            <button class="catalogue-viewer__nav catalogue-viewer__nav--next" type="button" data-pdf-next aria-label="<?php esc_attr_e('Επόμενη σελίδα', 'spek-theme'); ?>">
                                <span aria-hidden="true">›</span>
                            </button>

                            <div class="catalogue-viewer__loading" data-pdf-loading>
                                <span class="catalogue-viewer__spinner" aria-hidden="true"></span>
                                <strong><?php esc_html_e('Φόρτωση καταλόγου…', 'spek-theme'); ?></strong>
                                <small data-pdf-loading-status><?php esc_html_e('Προετοιμασία PDF', 'spek-theme'); ?></small>
                            </div>
                        </div>

                        <div class="catalogue-viewer__hint">
                            <?php esc_html_e('Χρησιμοποίησε τα βελάκια του πληκτρολογίου ή swipe για ξεφύλλισμα.', 'spek-theme'); ?>
                        </div>

                        <div class="catalogue-viewer__fallback" data-pdf-fallback hidden>
                            <p><?php esc_html_e('Ο διαδραστικός viewer δεν μπόρεσε να φορτωθεί. Μπορείς να ανοίξεις το PDF απευθείας.', 'spek-theme'); ?></p>
                            <a class="button button-primary" href="<?php echo esc_url($pdf_url); ?>" target="_blank" rel="noopener">
                                <?php esc_html_e('Άνοιγμα PDF', 'spek-theme'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </section>
        <?php else : ?>
            <section class="section">
                <div class="container">
                    <div class="catalogue-missing-pdf">
                        <h2><?php esc_html_e('Δεν έχει συνδεθεί PDF', 'spek-theme'); ?></h2>
                        <p><?php esc_html_e('Πρόσθεσε το PDF από το πεδίο “PDF καταλόγου” στην επεξεργασία του καταλόγου.', 'spek-theme'); ?></p>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </main>
    <?php
endwhile;

get_footer();
