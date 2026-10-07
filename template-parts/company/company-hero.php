<?php
/**
 * Company hero and key facts.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$featured_image = get_the_post_thumbnail_url(get_queried_object_id(), 'spek_hero');
$default_image_path = get_template_directory() . '/assets/images/spek-factory-line-blue.png';
$default_image_url  = get_template_directory_uri() . '/assets/images/spek-factory-line-blue.png';
$hero_image = file_exists($default_image_path) ? $default_image_url : $featured_image;
$intro_image_path = get_template_directory() . '/assets/images/spek-website-new-factory.png';
$intro_image_url  = get_template_directory_uri() . '/assets/images/spek-website-new-factory.png';
?>

<section class="company-hero">
    <div class="container company-hero__grid">
        <div class="company-hero__content" data-reveal>
            <span class="eyebrow"><?php esc_html_e('Η εταιρεία μας', 'spek-theme'); ?></span>
            <h1><?php esc_html_e('Ελληνική τεχνογνωσία. Σύγχρονη παραγωγή.', 'spek-theme'); ?></h1>
            <p class="company-hero__lead"><?php esc_html_e('Από το 1990, η SPEK σχεδιάζει και παράγει αξιόπιστες λύσεις για υδραυλικές εφαρμογές και είδη υγιεινής, συνδυάζοντας εμπειρία, παραγωγική γνώση και διαρκή εξέλιξη.', 'spek-theme'); ?></p>

        </div>

        <div class="company-hero__visual<?php echo $hero_image ? ' has-image' : ''; ?>" data-reveal>
            <?php if ($hero_image) : ?>
                <img
                    src="<?php echo esc_url($hero_image); ?>"
                    alt="<?php esc_attr_e('Οι εγκαταστάσεις της SPEK στο Σχηματάρι', 'spek-theme'); ?>"
                    loading="eager"
                    decoding="async"
                >
            <?php else : ?>
                <div class="company-hero__visual-mark" aria-hidden="true">
                    <img
                        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                        alt=""
                    >
                </div>
            <?php endif; ?>

            <div class="company-hero__visual-card">
                <span><?php esc_html_e('Από το', 'spek-theme'); ?></span>
                <strong>1990</strong>
                <small><?php esc_html_e('σχεδιάζουμε, παράγουμε, εξελισσόμαστε', 'spek-theme'); ?></small>
            </div>
        </div>
    </div>

    <div class="container company-facts" data-reveal>
        <article class="company-fact">
            <strong>1990</strong>
            <span><?php esc_html_e('Έτος ίδρυσης', 'spek-theme'); ?></span>
        </article>
        <article class="company-fact">
            <strong>8.100 m²</strong>
            <span><?php esc_html_e('Ιδιόκτητη παραγωγική βάση στο Σχηματάρι', 'spek-theme'); ?></span>
        </article>
        <article class="company-fact">
            <strong>1992</strong>
            <span><?php esc_html_e('Έναρξη εξαγωγικής δραστηριότητας', 'spek-theme'); ?></span>
        </article>
        <article class="company-fact">
            <strong>35+ <?php esc_html_e('χρόνια', 'spek-theme'); ?></strong>
            <span><?php esc_html_e('εμπειρίας και συνεχούς εξέλιξης', 'spek-theme'); ?></span>
        </article>
    </div>
</section>

<section class="section company-intro">
    <div class="container">
        <div class="company-intro__grid">
            <div data-reveal>
                <span class="eyebrow"><?php esc_html_e('Η SPEK σήμερα', 'spek-theme'); ?></span>
                <h2><?php esc_html_e('Από την ιδέα, στο προϊόν. Από την εμπειρία, στην εξέλιξη.', 'spek-theme'); ?></h2>
            </div>
            <div class="company-intro__copy" data-reveal>
                <p><?php esc_html_e('Η SPEK ΜΕΤΑΛΛΟΠΛΑΣΤΙΚΗ ΑΒΕΕ δραστηριοποιείται στη μελέτη, την κατασκευή και την παραγωγή εξαρτημάτων, μηχανισμών και συστημάτων για εφαρμογές ειδών υγιεινής και υδραυλικών εγκαταστάσεων.', 'spek-theme'); ?></p>
                <p><?php esc_html_e('Η τεχνογνωσία στην κατασκευή καλουπιών και στη διαχείριση των παραγωγικών διαδικασιών αποτέλεσε από την πρώτη ημέρα τον πυρήνα της εταιρείας. Σήμερα, αυτή η γνώση εξελίσσεται μέσα από σύγχρονο εξοπλισμό, οργανωμένες διαδικασίες και διαρκή επένδυση στην ποιότητα.', 'spek-theme'); ?></p>
                <p><?php esc_html_e('Με παρουσία στην ελληνική αγορά και εξαγωγική δραστηριότητα, στόχος μας παραμένει σταθερός: να δημιουργούμε αξιόπιστες, ανταγωνιστικές λύσεις που ανταποκρίνονται στις πραγματικές ανάγκες επαγγελματιών και τελικών χρηστών.', 'spek-theme'); ?></p>
            </div>
        </div>

        <?php if (file_exists($intro_image_path)) : ?>
            <figure class="company-intro__media" data-reveal style="--reveal-delay: 100ms;">
                <img
                    src="<?php echo esc_url($intro_image_url); ?>"
                    alt="<?php esc_attr_e('Πανοραμική άποψη των εγκαταστάσεων SPEK', 'spek-theme'); ?>"
                    loading="lazy"
                    decoding="async"
                >
                <figcaption>
                    <span class="company-intro__brand-lockup">
                        <img
                            src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/brand/spek_logo.png'); ?>"
                            alt="SPEK"
                            class="spek-brand-logo spek-brand-logo--inverse"
                        >
                        <small>· <?php esc_html_e('Σχηματάρι', 'spek-theme'); ?></small>
                    </span>
                    <strong><?php esc_html_e('8.100 m² ιδιόκτητης παραγωγικής βάσης', 'spek-theme'); ?></strong>
                </figcaption>
            </figure>
        <?php endif; ?>
    </div>
</section>
