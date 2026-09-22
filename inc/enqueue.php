<?php
/**
 * Enqueue scripts and styles.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

function spek_enqueue_assets(): void
{
    wp_enqueue_style(
        'spek-main',
        SPEK_THEME_URI . '/assets/css/main.css',
        [],
        filemtime(SPEK_THEME_DIR . '/assets/css/main.css')
    );

    wp_enqueue_style('spek-multilingual', SPEK_THEME_URI . '/assets/css/multilingual.css', ['spek-main'], filemtime(SPEK_THEME_DIR . '/assets/css/multilingual.css'));

    wp_enqueue_script(
        'spek-main',
        SPEK_THEME_URI . '/assets/js/main.js',
        [],
        filemtime(SPEK_THEME_DIR . '/assets/js/main.js'),
        true
    );

    wp_localize_script('spek-main', 'spekI18n', [
        ' · σελ. ' => __(' · σελ. ', 'spek-theme'),
        '%s δεν έχουν συντεταγμένες και δεν φαίνονται στον χάρτη.' => __('%s δεν έχουν συντεταγμένες και δεν φαίνονται στον χάρτη.', 'spek-theme'),
        '0 αποτελέσματα' => __('0 αποτελέσματα', 'spek-theme'),
        'Copied' => __('Copied', 'spek-theme'),
        'Copy' => __('Copy', 'spek-theme'),
        'Άνοιγμα PDF…' => __('Άνοιγμα PDF…', 'spek-theme'),
        'Αναζήτηση ' => __('Αναζήτηση ', 'spek-theme'),
        'Αναζήτηση…' => __('Αναζήτηση…', 'spek-theme'),
        'Αποτέλεσμα' => __('Αποτέλεσμα', 'spek-theme'),
        'Βρέθηκαν %s σημεία πώλησης.' => __('Βρέθηκαν %s σημεία πώλησης.', 'spek-theme'),
        'Γίνεται αναζήτηση...' => __('Γίνεται αναζήτηση...', 'spek-theme'),
        'Γράψε τουλάχιστον 2 χαρακτήρες για αναζήτηση.' => __('Γράψε τουλάχιστον 2 χαρακτήρες για αναζήτηση.', 'spek-theme'),
        'Δεν ήταν δυνατή η απόδοση αυτής της σελίδας.' => __('Δεν ήταν δυνατή η απόδοση αυτής της σελίδας.', 'spek-theme'),
        'Δεν βρέθηκαν σημεία πώλησης για αυτή την αναζήτηση. Δοκιμάστε άλλη περιοχή, ΤΚ ή μεγαλύτερη ακτίνα.' => __('Δεν βρέθηκαν σημεία πώλησης για αυτή την αναζήτηση. Δοκιμάστε άλλη περιοχή, ΤΚ ή μεγαλύτερη ακτίνα.', 'spek-theme'),
        'Δεν βρέθηκαν σημεία πώλησης κοντά σας. Δοκιμάστε μεγαλύτερη ακτίνα.' => __('Δεν βρέθηκαν σημεία πώλησης κοντά σας. Δοκιμάστε μεγαλύτερη ακτίνα.', 'spek-theme'),
        'Δεν βρέθηκε ο κωδικός ή η ονομασία μέσα στο PDF.' => __('Δεν βρέθηκε ο κωδικός ή η ονομασία μέσα στο PDF.', 'spek-theme'),
        'Δεν δόθηκε πρόσβαση στην τοποθεσία. Συμπληρώστε περιοχή ή ΤΚ χειροκίνητα.' => __('Δεν δόθηκε πρόσβαση στην τοποθεσία. Συμπληρώστε περιοχή ή ΤΚ χειροκίνητα.', 'spek-theme'),
        'Δοκιμάστε ξανά ή αλλάξτε τα κριτήρια αναζήτησης.' => __('Δοκιμάστε ξανά ή αλλάξτε τα κριτήρια αναζήτησης.', 'spek-theme'),
        'Εντοπίζεται η θέση σας...' => __('Εντοπίζεται η θέση σας...', 'spek-theme'),
        'Κάτι πήγε στραβά' => __('Κάτι πήγε στραβά', 'spek-theme'),
        'Λήψη PDF ' => __('Λήψη PDF ', 'spek-theme'),
        'Ο browser δεν υποστηρίζει εντοπισμό τοποθεσίας.' => __('Ο browser δεν υποστηρίζει εντοπισμό τοποθεσίας.', 'spek-theme'),
        'Οδηγίες' => __('Οδηγίες', 'spek-theme'),
        'Σελίδα ' => __('Σελίδα ', 'spek-theme'),
        'Συμπληρώστε περιοχή ή ΤΚ για να εμφανιστούν αποτελέσματα.' => __('Συμπληρώστε περιοχή ή ΤΚ για να εμφανιστούν αποτελέσματα.', 'spek-theme'),
        'Φόρτωση PDF.js…' => __('Φόρτωση PDF.js…', 'spek-theme'),
    ]);

    if (is_singular('spek_catalogue')) {
        wp_enqueue_script(
            'spek-catalogue-viewer',
            SPEK_THEME_URI . '/assets/js/catalogue-viewer.js',
            [],
            filemtime(SPEK_THEME_DIR . '/assets/js/catalogue-viewer.js'),
            true
        );
    }

    if (is_page_template('templates/template-catalogues.php')) {
        wp_enqueue_script(
            'spek-catalogue-covers',
            SPEK_THEME_URI . '/assets/js/catalogue-covers.js',
            [],
            filemtime(SPEK_THEME_DIR . '/assets/js/catalogue-covers.js'),
            true
        );
    }

    if (is_page_template('templates/template-product-finder.php')) {
        wp_enqueue_script(
            'spek-product-finder',
            SPEK_THEME_URI . '/assets/js/product-finder.js',
            ['spek-main'],
            filemtime(SPEK_THEME_DIR . '/assets/js/product-finder.js'),
            true
        );

        wp_localize_script('spek-product-finder', 'spekFinder', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spek_finder_nonce'),
            'lang' => spek_current_language(),
        ]);
    }

    if (is_page_template('templates/template-partners.php')) {
        wp_enqueue_style(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            [],
            '1.9.4'
        );

        wp_enqueue_script(
            'leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            [],
            '1.9.4',
            true
        );

        wp_enqueue_script(
            'spek-store-locator',
            SPEK_THEME_URI . '/assets/js/store-locator.js',
            ['leaflet', 'spek-main'],
            SPEK_THEME_VERSION,
            true
        );
        
        $tomtom_api_key = defined('SPEK_TOMTOM_API_KEY')
            ? SPEK_TOMTOM_API_KEY
            : '';
        
        wp_localize_script(
            'spek-store-locator',
            'spekMapConfig',
            [
                'tomtomKey' => $tomtom_api_key,
            ]
        );
    }
}

function spek_enqueue_admin_assets($hook): void
{
    global $post_type;

    if ($post_type !== 'spek_product') {
        return;
    }

    wp_enqueue_style(
        'spek-admin',
        SPEK_THEME_URI . '/assets/css/admin.css',
        [],
        SPEK_THEME_VERSION
    );
}

add_action('admin_enqueue_scripts', 'spek_enqueue_admin_assets');

add_action('wp_enqueue_scripts', 'spek_enqueue_assets');