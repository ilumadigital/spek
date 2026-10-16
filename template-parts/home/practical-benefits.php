<?php
/**
 * Shared SPEK practical benefits.
 * Homepage: compact horizontal strip immediately after video hero.
 * Company: existing editorial benefits band within Why SPEK.
 *
 * @package SpekTheme
 */
if (!defined('ABSPATH')) {
    exit;
}

$home_strip = !empty($args['home_strip']);
$items = [
    [
        'icon' => 'shield',
        'title' => __('Αξιόπιστη λειτουργία', 'spek-theme'),
        'text'  => __('Προϊόντα με σαφή εφαρμογή και σταθερή λειτουργία στην καθημερινή χρήση.', 'spek-theme'),
    ],
    [
        'icon' => 'award',
        'title' => __('Σταθερή ποιότητα', 'spek-theme'),
        'text'  => __('Συνεπείς προδιαγραφές και οργανωμένη γκάμα προϊόντων.', 'spek-theme'),
    ],
    [
        'icon' => 'document',
        'title' => __('Τεχνική πληροφόρηση', 'spek-theme'),
        'text'  => __('Κωδικοί, τεχνικά στοιχεία και ο τεχνικός κατάλογος για γρήγορη αναφορά.', 'spek-theme'),
    ],
    [
        'icon' => 'people',
        'title' => __('B2B υποστήριξη', 'spek-theme'),
        'text'  => __('Υποστήριξη για επαγγελματίες, συνεργάτες και σημεία πώλησης.', 'spek-theme'),
    ],
];
?>

<?php if ($home_strip) : ?>
<section class="home-practical" aria-labelledby="home-practical-heading">
    <div class="home-practical__inner">
        <h2 class="home-practical__heading" id="home-practical-heading">
            <?php esc_html_e('Στην πράξη', 'spek-theme'); ?>
        </h2>
<?php else : ?>
<div class="why-spek__practical">
    <div class="why-spek__benefits-intro" data-reveal>
        <span><?php esc_html_e('Στην πράξη', 'spek-theme'); ?></span>
    </div>
<?php endif; ?>

    <div class="<?php echo esc_attr($home_strip ? 'home-practical__grid' : 'why-spek__benefits'); ?>">
        <?php foreach ($items as $index => $item) : ?>
            <article class="<?php echo esc_attr($home_strip ? 'home-practical__item' : 'why-spek__benefit'); ?>" <?php if (!$home_strip) : ?>data-reveal style="--reveal-delay: <?php echo esc_attr(($index * 60) + 100); ?>ms;"<?php endif; ?>>
                <span class="<?php echo esc_attr($home_strip ? 'home-practical__icon' : 'why-spek__benefit-icon'); ?>" aria-hidden="true">
                    <?php if ($item['icon'] === 'shield') : ?>
                        <svg viewBox="0 0 32 32"><path d="M16 3 27 8v8c0 7-4.4 11-11 14C9.4 27 5 23 5 16V8l11-5Z"/><path d="m10 16 4 4 8-9"/></svg>
                            <?php elseif ($item['icon'] === 'award') : ?>
                        <svg viewBox="0 0 32 32"><path d="m16 3 3.1 2 3.7.1 1 3.6 2.9 2.3-1 3.7 1 3.5-2.9 2.4-1 3.5-3.7.1L16 26l-3.1-1.8-3.7-.1-1-3.5-2.9-2.4 1-3.5-1-3.7 2.9-2.3 1-3.6 3.7-.1L16 3Z"/><path d="m11 25-2 5 7-3 7 3-2-5"/></svg>
                            <?php elseif ($item['icon'] === 'document') : ?>
                        <svg viewBox="0 0 32 32"><rect x="7" y="3.5" width="18" height="25" rx="2"/><path d="M11 10h10M11 15h10M11 20h7"/></svg>
                            <?php else : ?>
                        <svg viewBox="0 0 32 32"><circle cx="16" cy="10" r="4.5"/><path d="M7 28v-3c0-6 4-9 9-9s9 3 9 9v3M6 8a4 4 0 0 0-1 8M26 8a4 4 0 0 1 1 8M3 26v-3c0-2 1-4 3-5M29 26v-3c0-2-1-4-3-5"/></svg>
                            <?php endif; ?>
                </span>
                <div class="<?php echo esc_attr($home_strip ? 'home-practical__copy' : 'why-spek__benefit-copy'); ?>">
                    <h3><?php echo esc_html($item['title']); ?></h3>
                    <p><?php echo esc_html($item['text']); ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php if ($home_strip) : ?>
    </div>
</section>
<?php else : ?>
</div>
<?php endif; ?>
