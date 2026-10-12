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

    if (is_singular('spek_product')) {
        wp_add_inline_style(
            'spek-main',
            '
            .single-product-page .single-product-summary h1 {
                font-size: 26px !important;
                line-height: 1.15 !important;
                letter-spacing: -0.025em !important;
            }

            .single-product-page .product-gallery {
                height: 100% !important;
            }

            .single-product-page .product-gallery--has-thumbs {
                display: block !important;
            }

            .single-product-page .product-gallery--has-thumbs .product-gallery__main {
                position: relative !important;
                padding-left: 126px !important;
            }

            .single-product-page .product-gallery__thumbs {
                position: absolute !important;
                left: 20px !important;
                top: 50% !important;
                z-index: 8 !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 10px !important;
                width: 78px !important;
                max-height: calc(100% - 40px) !important;
                margin: 0 !important;
                overflow-x: hidden !important;
                overflow-y: auto !important;
                transform: translateY(-50%) !important;
                scrollbar-width: thin;
            }

            .single-product-page .product-gallery__thumb {
                position: relative !important;
                flex: 0 0 auto !important;
                width: 76px !important;
                height: 76px !important;
                padding: 7px !important;
                overflow: hidden !important;
                border: 1px solid rgba(232, 224, 214, 0.98) !important;
                border-radius: 14px !important;
                background: #fff !important;
                box-shadow: 0 5px 16px rgba(17, 24, 32, 0.06) !important;
                cursor: pointer !important;
            }

            .single-product-page .product-gallery__thumb:hover,
            .single-product-page .product-gallery__thumb.is-active {
                border-color: rgba(15, 76, 129, 0.62) !important;
                box-shadow: 0 8px 22px rgba(15, 76, 129, 0.12) !important;
            }

            .single-product-page .product-gallery__thumb img {
                width: 100% !important;
                height: 100% !important;
                object-fit: contain !important;
            }

            @media (max-width: 760px) {
                .single-product-page .single-product-summary h1 {
                    font-size: 26px !important;
                }

                .single-product-page .product-gallery--has-thumbs .product-gallery__main {
                    padding-left: 24px !important;
                    padding-bottom: 104px !important;
                }

                .single-product-page .product-gallery__thumbs {
                    left: 50% !important;
                    right: auto !important;
                    top: auto !important;
                    bottom: 16px !important;
                    width: calc(100% - 32px) !important;
                    max-height: none !important;
                    flex-direction: row !important;
                    justify-content: flex-start !important;
                    overflow-x: auto !important;
                    overflow-y: hidden !important;
                    transform: translateX(-50%) !important;
                }

                .single-product-page .product-gallery__thumb {
                    width: 64px !important;
                    height: 64px !important;
                }
            }
            '
        );
    }

    if (
        is_front_page()
        || (function_exists('spek_is_english_home_request') && spek_is_english_home_request())
        || is_page_template('templates/template-company.php')
    ) {
        wp_enqueue_style(
            'spek-home-client-refresh',
            SPEK_THEME_URI . '/assets/css/home-client-refresh.css',
            ['spek-main', 'spek-multilingual'],
            filemtime(SPEK_THEME_DIR . '/assets/css/home-client-refresh.css')
        );
        // Shared premium SPEK values flow (Home + Company), loaded LAST to
        // override the previous four-box layout without affecting other pages.
        wp_enqueue_style(
            'spek-why-spek-flow',
            SPEK_THEME_URI . '/assets/css/why-spek-flow.css',
            ['spek-home-client-refresh'],
            filemtime(SPEK_THEME_DIR . '/assets/css/why-spek-flow.css')
        );
    }

    wp_enqueue_script(
        'spek-main',
        SPEK_THEME_URI . '/assets/js/main.js',
        [],
        filemtime(SPEK_THEME_DIR . '/assets/js/main.js'),
        true
    );


    if (
        is_post_type_archive('spek_product')
        || is_tax(['product_category', 'product_application', 'product_material', 'product_series'])
    ) {
        wp_enqueue_style(
            'spek-product-filters',
            SPEK_THEME_URI . '/assets/css/product-filters.css',
            ['spek-main', 'spek-multilingual'],
            filemtime(SPEK_THEME_DIR . '/assets/css/product-filters.css')
        );

        wp_enqueue_script(
            'spek-product-filters',
            SPEK_THEME_URI . '/assets/js/product-filters.js',
            ['spek-main'],
            filemtime(SPEK_THEME_DIR . '/assets/js/product-filters.js'),
            true
        );
    }

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
        'Έναρξη' => __('Έναρξη', 'spek-theme'),
        'Κατηγορία' => __('Κατηγορία', 'spek-theme'),
        'Εφαρμογή' => __('Εφαρμογή', 'spek-theme'),
        'Διαστάσεις' => __('Διαστάσεις', 'spek-theme'),
        'Χωρίς διαστάσεις' => __('Χωρίς διαστάσεις', 'spek-theme'),
        'Αναμονή διαστάσεων' => __('Αναμονή διαστάσεων', 'spek-theme'),
        'Όλα τα διαθέσιμα υλικά' => __('Όλα τα διαθέσιμα υλικά', 'spek-theme'),
        'Όλες οι διαθέσιμες σειρές' => __('Όλες οι διαθέσιμες σειρές', 'spek-theme'),
        'Όλοι οι διαθέσιμοι τύποι' => __('Όλοι οι διαθέσιμοι τύποι', 'spek-theme'),
        'Χαρακτηριστικά' => __('Χαρακτηριστικά', 'spek-theme'),
        'Αποτελέσματα' => __('Αποτελέσματα', 'spek-theme'),
        'Δεν έχει επιλεγεί' => __('Δεν έχει επιλεγεί', 'spek-theme'),
        'Δεν απαιτείται' => __('Δεν απαιτείται', 'spek-theme'),
        'Χωρίς επιπλέον φίλτρα' => __('Χωρίς επιπλέον φίλτρα', 'spek-theme'),
        'Φόρτωση PDF.js…' => __('Φόρτωση PDF.js…', 'spek-theme'),
    ]);

    if (is_page_template('templates/template-careers.php')) {
        wp_enqueue_style(
            'spek-careers',
            SPEK_THEME_URI . '/assets/css/careers.css',
            ['spek-main', 'spek-multilingual'],
            filemtime(SPEK_THEME_DIR . '/assets/css/careers.css')
        );
    }

    if (is_page_template('templates/template-contact.php')) {
        wp_enqueue_style(
            'spek-contact-form',
            SPEK_THEME_URI . '/assets/css/contact-form.css',
            ['spek-main', 'spek-multilingual'],
            filemtime(SPEK_THEME_DIR . '/assets/css/contact-form.css')
        );

        wp_enqueue_script(
            'spek-contact-form',
            SPEK_THEME_URI . '/assets/js/contact-form.js',
            ['spek-main'],
            filemtime(SPEK_THEME_DIR . '/assets/js/contact-form.js'),
            true
        );
    }

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
        // Browser-only key, restricted in Google Cloud to the SPEK domain and
        // Maps JavaScript API (+ Geocoding API for geographic text search).
        // Keep the existing SPEK_GOOGLE_MAPS_API_KEY for server-side Places.
        $google_browser_key = defined('SPEK_GOOGLE_MAPS_BROWSER_KEY')
            ? trim((string) SPEK_GOOGLE_MAPS_BROWSER_KEY)
            : '';

        if ($google_browser_key !== '') {
            wp_enqueue_style(
                'spek-google-locator',
                SPEK_THEME_URI . '/assets/css/store-locator-google.css',
                ['spek-main'],
                filemtime(SPEK_THEME_DIR . '/assets/css/store-locator-google.css')
            );

            wp_enqueue_script(
                'spek-google-markerclusterer',
                'https://unpkg.com/@googlemaps/markerclusterer@2.6.2/dist/index.min.js',
                [],
                null,
                true
            );

            wp_enqueue_script(
                'spek-store-locator',
                SPEK_THEME_URI . '/assets/js/store-locator-google.js',
                ['spek-main', 'spek-google-markerclusterer'],
                filemtime(SPEK_THEME_DIR . '/assets/js/store-locator-google.js'),
                true
            );

            // Browser key is public by design: secure it with HTTP referrer and
            // API restrictions in Google Cloud, never put the server key here.
            wp_localize_script('spek-store-locator', 'spekMapConfig', [
                'googleBrowserKey' => $google_browser_key,
            ]);
        } else {
            // Safe fallback until the website owner adds the new browser key.
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
                filemtime(SPEK_THEME_DIR . '/assets/js/store-locator.js'),
                true
            );

            $tomtom_api_key = defined('SPEK_TOMTOM_API_KEY')
                ? SPEK_TOMTOM_API_KEY
                : '';

            wp_localize_script('spek-store-locator', 'spekMapConfig', [
                'tomtomKey' => $tomtom_api_key,
            ]);
        }
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
        filemtime(SPEK_THEME_DIR . '/assets/css/admin.css')
    );

    // Load the WordPress media modal before our product-gallery handlers.
    // The metabox HTML renders earlier than footer scripts, so inline binding
    // there silently failed when wp.media was still undefined.
    if (in_array($hook, ['post.php', 'post-new.php'], true)) {
        wp_enqueue_media();
        wp_enqueue_script(
            'spek-product-admin-gallery',
            SPEK_THEME_URI . '/assets/js/product-admin-gallery.js',
            ['jquery', 'jquery-ui-sortable', 'media-views'],
            filemtime(SPEK_THEME_DIR . '/assets/js/product-admin-gallery.js'),
            true
        );
        wp_localize_script('spek-product-admin-gallery', 'spekProductGalleryI18n', [
            'replace'         => __('Αλλαγή', 'spek-theme'),
            'remove'          => __('Αφαίρεση', 'spek-theme'),
            'up'              => __('Πριν', 'spek-theme'),
            'down'            => __('Μετά', 'spek-theme'),
            'galleryTitle'    => __('Επιλογή εικόνων gallery', 'spek-theme'),
            'useSelected'     => __('Χρήση επιλεγμένων εικόνων', 'spek-theme'),
            'replaceTitle'    => __('Αλλαγή εικόνας gallery', 'spek-theme'),
            'useImage'        => __('Χρήση εικόνας', 'spek-theme'),
            'alreadySelected' => __('Η εικόνα υπάρχει ήδη στο gallery.', 'spek-theme'),
        ]);
    }
}

add_action('admin_enqueue_scripts', 'spek_enqueue_admin_assets');

add_action('wp_enqueue_scripts', 'spek_enqueue_assets');