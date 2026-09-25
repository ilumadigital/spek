<?php
/**
 * Company values and international presence.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

$values = [
    [__('Αξιοπιστία', 'spek-theme'), __('Λύσεις σχεδιασμένες για πραγματική, καθημερινή χρήση και σταθερή απόδοση.', 'spek-theme')],
    [__('Εξέλιξη', 'spek-theme'), __('Συνεχής επένδυση σε τεχνολογία, παραγωγικές δυνατότητες και νέες ιδέες.', 'spek-theme')],
    [__('Συνέπεια', 'spek-theme'), __('Σταθερή προσήλωση στην ποιότητα, από τον σχεδιασμό μέχρι το τελικό προϊόν.', 'spek-theme')],
];
?>

<section class="section company-values">
    <div class="container">
        <div class="company-values__grid">
            <div class="company-values__statement" data-reveal>
                <span class="eyebrow"><?php esc_html_e('Η φιλοσοφία μας', 'spek-theme'); ?></span>
                <h2><?php esc_html_e('Σχεδιάζουμε για να λειτουργεί καλύτερα.', 'spek-theme'); ?></h2>
                <p><?php esc_html_e('Η ουσία κάθε προϊόντος βρίσκεται στη λειτουργία του. Γι’ αυτό ξεκινάμε από την πραγματική ανάγκη, επιδιώκοντας απλές, αξιόπιστες και ουσιαστικές λύσεις.', 'spek-theme'); ?></p>
            </div>

            <div class="company-values__cards">
                <?php foreach ($values as $value) : ?>
                    <article class="company-value-card" data-reveal>
                        <span aria-hidden="true"></span>
                        <h3><?php echo esc_html($value[0]); ?></h3>
                        <p><?php echo esc_html($value[1]); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
