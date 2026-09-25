<?php
/**
 * Template Name: Company Details
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$gr_name    = 'SPEK ΜΕΤΑΛΛΟΠΛΑΣΤΙΚΗ Α.Β.Ε.Ε.';
$gr_office  = 'ΘΕΣΗ ΑΧΛΑΔΙΑ ΤΟΛΙΑ - Δ.Δ. ΒΑΘΥ ΔΗΜΟΥ ΑΥΛΙΔΟΣ ΕΥΒΟΙΑΣ, ΤΚ 32009 - ΣΧΗΜΑΤΑΡΙ, Τ.Θ. 159';
$gr_vat     = '084016352';
$gr_tax     = 'ΧΑΛΚΙΔΟΣ';
$gr_gemi    = '8056001000';

$en_name    = 'SPEK METALPLASTIC S.A.';
$en_office  = 'THESI ACHLADIA TOLIA - P.O BOX No 159, PC 32009, SCHIMATARI GREECE';
$en_vat     = 'EL084016352';
$en_tax     = 'CHALKIDOS';
$en_gemi    = '8056001000';

$is_english = function_exists('spek_is_english') && spek_is_english();

$copy_button = static function (string $value, string $label, string $success_label): void {
    ?>
    <button
        type="button"
        class="company-copy-button"
        data-copy-value="<?php echo esc_attr($value); ?>"
        data-copy-label="<?php echo esc_attr($label); ?>"
        data-copy-success="<?php echo esc_attr($success_label); ?>"
        aria-label="<?php echo esc_attr($label); ?>"
        title="<?php echo esc_attr($label); ?>"
    >
        <svg class="company-copy-button__copy" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <rect x="8" y="8" width="11" height="11" rx="2"></rect>
            <path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"></path>
        </svg>
        <svg class="company-copy-button__check" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="m5 12 4 4L19 6"></path>
        </svg>
    </button>
    <?php
};

get_header();
?>

<main id="main" class="site-main company-details-page">


    <section class="section company-details-section">
        <div class="container">

            <div class="company-details-intro" data-reveal>
                <div>
                    <span class="eyebrow"><?php esc_html_e('Corporate Identity', 'spek-theme'); ?></span>
                    <h2><?php esc_html_e('Εταιρική ταυτότητα, με σαφήνεια.', 'spek-theme'); ?></h2>
                </div>

                <p><?php esc_html_e('Τα επίσημα στοιχεία της εταιρείας συγκεντρωμένα για άμεση αναφορά.', 'spek-theme'); ?></p>
            </div>

            <div class="company-details-grid company-details-grid--single">

                <?php if (!$is_english) : ?>
                <article class="company-details-card company-details-card--gr" data-reveal data-delay="80">
                    <div class="company-details-card__head">
                        <div>
                            <span class="company-details-card__language" title="<?php esc_attr_e('Ελληνικά', 'spek-theme'); ?>">
                                <img
                                    src="https://flagcdn.com/w40/gr.png"
                                    srcset="https://flagcdn.com/w80/gr.png 2x"
                                    width="40"
                                    height="27"
                                    alt="<?php esc_attr_e('Ελληνική σημαία', 'spek-theme'); ?>"
                                >
                            </span>
                            <p class="company-details-card__kicker"><?php esc_html_e('Ελληνικά στοιχεία', 'spek-theme'); ?></p>
                        </div>

                        <div class="company-details-card__brand" aria-label="<?php esc_attr_e('SPEK', 'spek-theme'); ?>">
                            <img
                                src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                                alt="SPEK"
                                class="company-details-card__logo"
                            >
                        </div>
                    </div>

                    <div class="company-details-card__primary">
                        <span class="company-details-label"><?php esc_html_e('Επωνυμία', 'spek-theme'); ?></span>
                        <div class="company-details-value-row company-details-value-row--primary">
                            <h3><?php echo esc_html($gr_name); ?></h3>
                            <?php $copy_button($gr_name, __('Αντιγραφή επωνυμίας', 'spek-theme'), __('Αντιγράφηκε', 'spek-theme')); ?>
                        </div>
                    </div>

                    <div class="company-details-card__address">
                        <span class="company-details-label"><?php esc_html_e('Έδρα', 'spek-theme'); ?></span>
                        <div class="company-details-value-row">
                            <address><?php echo esc_html($gr_office); ?></address>
                            <?php $copy_button($gr_office, __('Αντιγραφή έδρας', 'spek-theme'), __('Αντιγράφηκε', 'spek-theme')); ?>
                        </div>
                    </div>

                    <dl class="company-details-meta">
                        <div>
                            <dt><?php esc_html_e('ΑΦΜ', 'spek-theme'); ?></dt>
                            <dd>
                                <span><?php echo esc_html($gr_vat); ?></span>
                                <?php $copy_button($gr_vat, __('Αντιγραφή ΑΦΜ', 'spek-theme'), __('Αντιγράφηκε', 'spek-theme')); ?>
                            </dd>
                        </div>
                        <div>
                            <dt><?php esc_html_e('Δ.Ο.Υ.', 'spek-theme'); ?></dt>
                            <dd>
                                <span><?php echo esc_html($gr_tax); ?></span>
                                <?php $copy_button($gr_tax, __('Αντιγραφή Δ.Ο.Υ.', 'spek-theme'), __('Αντιγράφηκε', 'spek-theme')); ?>
                            </dd>
                        </div>
                        <div>
                            <dt><?php esc_html_e('Αρ. ΓΕΜΗ', 'spek-theme'); ?></dt>
                            <dd>
                                <span><?php echo esc_html($gr_gemi); ?></span>
                                <?php $copy_button($gr_gemi, __('Αντιγραφή αριθμού ΓΕΜΗ', 'spek-theme'), __('Αντιγράφηκε', 'spek-theme')); ?>
                            </dd>
                        </div>
                    </dl>
                </article>

                <?php else : ?>
                <article class="company-details-card company-details-card--en" data-reveal data-delay="160">
                    <div class="company-details-card__head">
                        <div>
                            <span class="company-details-card__language" title="<?php esc_attr_e('English', 'spek-theme'); ?>">
                                <img
                                    src="https://flagcdn.com/w40/gb.png"
                                    srcset="https://flagcdn.com/w80/gb.png 2x"
                                    width="40"
                                    height="20"
                                    alt="<?php esc_attr_e('United Kingdom flag', 'spek-theme'); ?>"
                                >
                            </span>
                            <p class="company-details-card__kicker"><?php esc_html_e('English details', 'spek-theme'); ?></p>
                        </div>

                        <div class="company-details-card__brand" aria-label="<?php esc_attr_e('SPEK', 'spek-theme'); ?>">
                            <img
                                src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                                alt="SPEK"
                                class="company-details-card__logo"
                            >
                        </div>
                    </div>

                    <div class="company-details-card__primary">
                        <span class="company-details-label"><?php esc_html_e('Company Name', 'spek-theme'); ?></span>
                        <div class="company-details-value-row company-details-value-row--primary">
                            <h3><?php echo esc_html($en_name); ?></h3>
                            <?php $copy_button($en_name, __('Copy company name', 'spek-theme'), __('Copied', 'spek-theme')); ?>
                        </div>
                    </div>

                    <div class="company-details-card__address">
                        <span class="company-details-label"><?php esc_html_e('Registered Office', 'spek-theme'); ?></span>
                        <div class="company-details-value-row">
                            <address><?php echo esc_html($en_office); ?></address>
                            <?php $copy_button($en_office, __('Copy registered office', 'spek-theme'), __('Copied', 'spek-theme')); ?>
                        </div>
                    </div>

                    <dl class="company-details-meta">
                        <div>
                            <dt><?php esc_html_e('VAT No', 'spek-theme'); ?></dt>
                            <dd>
                                <span><?php echo esc_html($en_vat); ?></span>
                                <?php $copy_button($en_vat, __('Copy VAT number', 'spek-theme'), __('Copied', 'spek-theme')); ?>
                            </dd>
                        </div>
                        <div>
                            <dt><?php esc_html_e('Tax Office', 'spek-theme'); ?></dt>
                            <dd>
                                <span><?php echo esc_html($en_tax); ?></span>
                                <?php $copy_button($en_tax, __('Copy tax office', 'spek-theme'), __('Copied', 'spek-theme')); ?>
                            </dd>
                        </div>
                        <div>
                            <dt><?php esc_html_e('GEMI No', 'spek-theme'); ?></dt>
                            <dd>
                                <span><?php echo esc_html($en_gemi); ?></span>
                                <?php $copy_button($en_gemi, __('Copy GEMI number', 'spek-theme'), __('Copied', 'spek-theme')); ?>
                            </dd>
                        </div>
                    </dl>
                </article>
                <?php endif; ?>

            </div>

            <div class="company-details-footer" data-reveal data-delay="220">
                <div class="company-details-footer__copy">
                    <span class="company-details-footer__icon" aria-hidden="true">i</span>
                    <div>
                        <strong><?php esc_html_e('Χρειάζεστε περισσότερες πληροφορίες;', 'spek-theme'); ?></strong>
                        <p><?php esc_html_e('Η ομάδα της SPEK είναι στη διάθεσή σας για εταιρικά και εμπορικά αιτήματα.', 'spek-theme'); ?></p>
                    </div>
                </div>

                <a href="<?php echo esc_url(spek_page_url('contact/')); ?>" class="button button-secondary">
                    <?php esc_html_e('Επικοινωνία', 'spek-theme'); ?>
                </a>
            </div>

        </div>
    </section>

</main>

<?php
get_footer();
