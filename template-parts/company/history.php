<?php
/**
 * Company history as editorial chapters.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$chapters = [
    [
        'number' => '01',
        'years' => '1990–1996',
        'title' => __('Η αρχή', 'spek-theme'),
        'text' => __('Η SPEK ιδρύεται το 1990, φέρνοντας μαζί εμπειρία στην κατασκευή καλουπιών και βαθιά γνώση της αγοράς υδραυλικών και ειδών υγιεινής. Τα πρώτα χρόνια σηματοδοτούνται από νέα προϊόντα, την πρώτη εξαγωγική δραστηριότητα και την ανάπτυξη ιδιόκτητων λύσεων.', 'spek-theme'),
        'milestones' => [__('Ίδρυση της εταιρείας', 'spek-theme'), __('Πρώτες εξαγωγές το 1992', 'spek-theme'), __('Νέα προϊόντα και διπλώματα ευρεσιτεχνίας', 'spek-theme')],
    ],
    [
        'number' => '02',
        'years' => '1997–2008',
        'title' => __('Διεύρυνση της παραγωγής', 'spek-theme'),
        'text' => __('Η γκάμα εμπλουτίζεται και οι παραγωγικές δυνατότητες διευρύνονται. Η εταιρεία επενδύει σε νέες κατηγορίες προϊόντων και προετοιμάζει την επόμενη φάση ανάπτυξής της.', 'spek-theme'),
        'milestones' => [__('Νέες σειρές προϊόντων', 'spek-theme'), __('Διεύρυνση παραγωγικής γκάμας', 'spek-theme'), __('Αγορά οικοπέδου 8.100 m² στο Σχηματάρι', 'spek-theme')],
    ],
    [
        'number' => '03',
        'years' => '2010–2013',
        'title' => __('Νέα παραγωγική βάση', 'spek-theme'),
        'text' => __('Η μεταφορά στις νέες ιδιόκτητες εγκαταστάσεις ανοίγει έναν νέο κύκλο για τη SPEK. Δημιουργείται νέο κέντρο διανομής, αποκτάται σύγχρονος μηχανολογικός εξοπλισμός και ενισχύονται οι διαδικασίες ποιότητας και ανακύκλωσης.', 'spek-theme'),
        'milestones' => [__('Νέες ιδιόκτητες εγκαταστάσεις', 'spek-theme'), __('Νέο κέντρο διανομής', 'spek-theme'), __('ISO / DQS και οργανωμένη διαδικασία ανακύκλωσης', 'spek-theme')],
    ],
    [
        'number' => '04',
        'years' => __('2013–σήμερα', 'spek-theme'),
        'title' => __('Η επόμενη γενιά SPEK', 'spek-theme'),
        'text' => __('Νέες σειρές, νέα προϊόντα και συνεχής επέκταση της παραγωγικής υποδομής. Η εξέλιξη παραμένει ενεργή διαδικασία, με επίκεντρο την τεχνολογία, την ποιότητα, την εξωστρέφεια και την ελληνική παραγωγή.', 'spek-theme'),
        'milestones' => [__('Σειρά NEMO και νέα προϊόντα', 'spek-theme'), __('Συνεχής αναβάθμιση παραγωγής', 'spek-theme'), __('Μελέτη και επέκταση παραγωγικών εγκαταστάσεων', 'spek-theme')],
    ],
];
?>

<!--<section class="section company-history">-->
<!--    <div class="container">-->
<!--        <div class="section-heading company-history__heading" data-reveal>-->
<!--            <span class="eyebrow"><?php esc_html_e('Η διαδρομή μας', 'spek-theme'); ?></span>-->
<!--            <h2><?php esc_html_e('Τέσσερα κεφάλαια εξέλιξης.', 'spek-theme'); ?></h2>-->
<!--            <p><?php esc_html_e('Όχι ένα συμβατικό timeline, αλλά οι σημαντικότερες περίοδοι που διαμόρφωσαν τη SPEK όπως είναι σήμερα.', 'spek-theme'); ?></p>-->
<!--        </div>-->

<!--        <div class="company-chapters">-->
<!--            <?php foreach ($chapters as $index => $chapter) : ?>-->
<!--                <article class="company-chapter<?php echo $index % 2 ? ' company-chapter--offset' : ''; ?>" data-reveal>-->
<!--                    <div class="company-chapter__top">-->
<!--                        <span class="company-chapter__number"><?php echo esc_html($chapter['number']); ?></span>-->
<!--                        <span class="company-chapter__years"><?php echo esc_html($chapter['years']); ?></span>-->
<!--                    </div>-->
<!--                    <h3><?php echo esc_html($chapter['title']); ?></h3>-->
<!--                    <p><?php echo esc_html($chapter['text']); ?></p>-->
<!--                    <ul>-->
<!--                        <?php foreach ($chapter['milestones'] as $milestone) : ?>-->
<!--                            <li><?php echo esc_html($milestone); ?></li>-->
<!--                        <?php endforeach; ?>-->
<!--                    </ul>-->
<!--                </article>-->
<!--            <?php endforeach; ?>-->
<!--        </div>-->
<!--    </div>-->
<!--</section>-->

<section id="history" class="section company-history">
    <div class="container">

        <div class="section-heading company-history__heading" data-reveal>
            <span class="eyebrow">
                <?php esc_html_e('Η διαδρομή μας', 'spek-theme'); ?>
            </span>

            <h2>
                <?php esc_html_e('Μια πορεία διαρκούς εξέλιξης.', 'spek-theme'); ?>
            </h2>

            <p>
                <?php esc_html_e(
                    'Από την ίδρυση της SPEK έως σήμερα, κάθε περίοδος της ιστορίας μας σηματοδοτεί ένα νέο βήμα στην παραγωγή, την τεχνογνωσία και την εξωστρέφεια.',
                    'spek-theme'
                ); ?>
            </p>
        </div>

        <div class="company-timeline">

            <div class="company-timeline__line" aria-hidden="true"></div>

            <?php foreach ($chapters as $index => $chapter) : ?>

                <article
                    class="company-timeline__item <?php echo $index % 2 ? 'company-timeline__item--right' : 'company-timeline__item--left'; ?>"
                    data-reveal
                    data-delay="<?php echo esc_attr($index * 100); ?>"
                >

                    <div class="company-timeline__marker" aria-hidden="true">
                        <span></span>
                    </div>

                    <div class="company-timeline__year">
                        <?php echo esc_html($chapter['years']); ?>
                    </div>

                    <div class="company-timeline__card">

                        <span class="company-timeline__chapter-number" aria-hidden="true">
                            <?php echo esc_html($chapter['number']); ?>
                        </span>

                        <div class="company-timeline__card-header">
                            <span class="company-timeline__label">
                                <?php
                                printf(
                                    esc_html__('Κεφάλαιο %s', 'spek-theme'),
                                    esc_html($chapter['number'])
                                );
                                ?>
                            </span>

                            <span class="company-timeline__period">
                                <?php echo esc_html($chapter['years']); ?>
                            </span>
                        </div>

                        <h3>
                            <?php echo esc_html($chapter['title']); ?>
                        </h3>

                        <p class="company-timeline__description">
                            <?php echo esc_html($chapter['text']); ?>
                        </p>

                        <?php if (!empty($chapter['milestones'])) : ?>
                            <div class="company-timeline__milestones">
                                <?php foreach ($chapter['milestones'] as $milestone) : ?>
                                    <div class="company-timeline__milestone">
                                        <span class="company-timeline__milestone-icon" aria-hidden="true">
                                            <svg viewBox="0 0 20 20" fill="none">
                                                <path d="M5 10.5L8.2 13.5L15 6.5"
                                                      stroke="currentColor"
                                                      stroke-width="1.7"
                                                      stroke-linecap="round"
                                                      stroke-linejoin="round"/>
                                            </svg>
                                        </span>

                                        <span>
                                            <?php echo esc_html($milestone); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </article>

            <?php endforeach; ?>

        </div>

    </div>
</section>
