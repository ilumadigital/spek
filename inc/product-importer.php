<?php
/**
 * SPEK Product CSV Importer.
 *
 * Imports legacy product CSV exports into the custom spek_product CPT.
 * The import runs in small AJAX batches to avoid nginx/PHP gateway timeouts.
 * Import progress is stored in a small JSON state file instead of WordPress transients,
 * so object-cache/transient eviction cannot interrupt long imports.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add importer below Products in wp-admin.
 */
function spek_product_importer_menu(): void
{
    add_submenu_page(
        'edit.php?post_type=spek_product',
        __('Εισαγωγή προϊόντων', 'spek-theme'),
        __('Εισαγωγή CSV', 'spek-theme'),
        'manage_options',
        'spek-product-importer',
        'spek_render_product_importer_page'
    );
}
add_action('admin_menu', 'spek_product_importer_menu');

/**
 * Render importer page.
 */
function spek_render_product_importer_page(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη σελίδα.', 'spek-theme'));
    }

    $ajax_nonce = wp_create_nonce('spek_product_import_ajax');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Εισαγωγή προϊόντων από CSV', 'spek-theme'); ?></h1>

        <?php $spek_sku_diag = function_exists('spek_import_product_sku_diagnostics') ? spek_import_product_sku_diagnostics() : []; ?>
        <div class="notice notice-info inline" style="max-width:1100px">
            <p><strong>SPEK Importer:</strong> <?php echo esc_html((string) ($spek_sku_diag['build'] ?? 'legacy / diagnostic unavailable')); ?></p>
            <?php if ($spek_sku_diag) : ?>
                <p style="margin-top:0">
                    <?php echo esc_html(sprintf(
                        'DB spek_product: %d · indexed products: %d · unique SKU: %d · product_code rows: %d',
                        (int) ($spek_sku_diag['product_count'] ?? 0),
                        (int) ($spek_sku_diag['indexed_product_count'] ?? 0),
                        (int) ($spek_sku_diag['unique_sku_count'] ?? 0),
                        (int) (($spek_sku_diag['key_counts']['product_code'] ?? 0))
                    )); ?>
                </p>
                <p style="margin-top:0"><strong>Probe SKU 40204:</strong> <?php echo esc_html(!empty($spek_sku_diag['probe_40204']) ? implode(', ', array_map('intval', $spek_sku_diag['probe_40204'])) : 'not found'); ?></p>
                <?php if (!empty($spek_sku_diag['candidate_keys'])) : ?>
                    <p style="margin-top:0"><strong>SKU/code meta keys:</strong>
                        <?php
                        $pairs = [];
                        foreach ((array) $spek_sku_diag['candidate_keys'] as $key => $qty) { $pairs[] = $key . '=' . (int) $qty; }
                        echo esc_html(implode(' · ', $pairs));
                        ?>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <p>
            <?php esc_html_e('Ο importer συγχρονίζει με ασφάλεια τα προϊόντα με βάση το SKU. Για τη Φάση Β μπορείτε να ενημερώσετε μόνο τις αγγλικές μεταφράσεις ή να εκτελέσετε πλήρη συγχρονισμό Greek + English.', 'spek-theme'); ?>
        </p>

        <div class="notice notice-info inline">
            <p>
                <strong><?php esc_html_e('Αναμενόμενες στήλες:', 'spek-theme'); ?></strong>
                Title, SKU, Content, Product categories, product_short_description, product_dimensions, product_material, product_color, product_application_text, product_compatibility, product_installation_type, title_en, content_en, import_action, review_status.
                <?php esc_html_e('Οι γραμμές με import_action διαφορετικό από import ή review_status διαφορετικό από ready παραλείπονται. Δεν δημιουργείται δεύτερο English product: οι αγγλικές τιμές αποθηκεύονται στο ίδιο canonical προϊόν. Στη λειτουργία “Μόνο αγγλικές μεταφράσεις” δεν αλλάζει κανένα ελληνικό title/content/meta, δεν δημιουργούνται προϊόντα και δεν αλλάζουν taxonomy assignments. Πριν από πραγματική εγγραφή απαιτείται επιτυχημένο dry-run του ίδιου ακριβώς αρχείου και της ίδιας λειτουργίας.', 'spek-theme'); ?>
            </p>
        </div>

        <form id="spek-product-import-form" enctype="multipart/form-data">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="spek_product_csv"><?php esc_html_e('CSV αρχείο', 'spek-theme'); ?></label>
                    </th>
                    <td>
                        <input
                            type="file"
                            id="spek_product_csv"
                            name="spek_product_csv"
                            accept=".csv,text/csv,text/plain"
                            required
                        >
                        <p class="description">
                            <?php esc_html_e('CSV UTF-8. Υποστηρίζονται κόμμα, ελληνικό/ευρωπαϊκό ερωτηματικό (;) και tab ως διαχωριστικά.', 'spek-theme'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="spek_product_import_mode"><?php esc_html_e('Τύπος συγχρονισμού', 'spek-theme'); ?></label></th>
                    <td>
                        <select id="spek_product_import_mode" name="import_mode">
                            <option value="translations" selected><?php esc_html_e('Μόνο αγγλικές μεταφράσεις (προτεινόμενο)', 'spek-theme'); ?></option>
                            <option value="full"><?php esc_html_e('Πλήρης συγχρονισμός Greek + English', 'spek-theme'); ?></option>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Μόνο EN: ενημερώνει title/content/τεχνικά EN και English taxonomy names μόνο σε υπάρχοντα SKU. Πλήρης: ενημερώνει τα ελληνικά δεδομένα και μπορεί να δημιουργήσει τα SKU που λείπουν.', 'spek-theme'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Εκτέλεση', 'spek-theme'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" id="spek_product_import_dry_run" name="dry_run" value="1" checked>
                            <?php esc_html_e('Μόνο έλεγχος (dry run) — δεν γράφει δεδομένα', 'spek-theme'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Το πραγματικό import ξεκλειδώνει μόνο αφού ολοκληρωθεί dry-run του ίδιου CSV και του ίδιου τύπου συγχρονισμού.', 'spek-theme'); ?></p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Εκτέλεση εισαγωγής', 'spek-theme'), 'primary', 'spek_product_import_submit', false); ?>
        </form>

        <div id="spek-import-progress" style="display:none;max-width:760px;margin-top:24px;">
            <h2 style="margin-bottom:8px;"><?php esc_html_e('Πρόοδος εισαγωγής', 'spek-theme'); ?></h2>
            <div style="height:18px;background:#dcdcde;border-radius:3px;overflow:hidden;">
                <div id="spek-import-progress-bar" style="width:0;height:100%;background:#2271b1;transition:width .2s ease;"></div>
            </div>
            <p id="spek-import-progress-text" style="margin-top:8px;"></p>
        </div>

        <div id="spek-import-result" style="max-width:760px;margin-top:18px;"></div>
    </div>

    <script>
    (function () {
        'use strict';

        const form = document.getElementById('spek-product-import-form');
        const submitButton = document.getElementById('spek_product_import_submit');
        const dryRunInput = document.getElementById('spek_product_import_dry_run');
        const modeInput = document.getElementById('spek_product_import_mode');
        const progressWrap = document.getElementById('spek-import-progress');
        const progressBar = document.getElementById('spek-import-progress-bar');
        const progressText = document.getElementById('spek-import-progress-text');
        const resultWrap = document.getElementById('spek-import-result');
        const ajaxNonce = <?php echo wp_json_encode($ajax_nonce); ?>;

        if (!form) {
            return;
        }

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = String(value ?? '');
            return div.innerHTML;
        }

        function showError(message) {
            resultWrap.innerHTML = '<div class="notice notice-error inline"><p><strong>' + escapeHtml(<?php echo wp_json_encode(__('Σφάλμα:', 'spek-theme')); ?>) + '</strong> ' + escapeHtml(message) + '</p></div>';
            submitButton.disabled = false;
            submitButton.value = <?php echo wp_json_encode(__('Εκτέλεση εισαγωγής', 'spek-theme')); ?>;
        }

        function renderStats(stats, done) {
            const prefix = stats.dry_run
                ? <?php echo wp_json_encode(__('Dry run', 'spek-theme')); ?>
                : <?php echo wp_json_encode(__('Εισαγωγή', 'spek-theme')); ?>;
            const modeLabel = stats.mode === 'full'
                ? <?php echo wp_json_encode(__('Πλήρης συγχρονισμός', 'spek-theme')); ?>
                : <?php echo wp_json_encode(__('Μόνο EN', 'spek-theme')); ?>;

            progressText.textContent = prefix + ' · ' + modeLabel + ': ' +
                <?php echo wp_json_encode(__('Γραμμές ', 'spek-theme')); ?> + stats.rows +
                <?php echo wp_json_encode(__(' · Matched ', 'spek-theme')); ?> + stats.matched +
                <?php echo wp_json_encode(__(' · EN updates ', 'spek-theme')); ?> + stats.translated +
                <?php echo wp_json_encode(__(' · Νέα ', 'spek-theme')); ?> + stats.created +
                <?php echo wp_json_encode(__(' · Missing SKU ', 'spek-theme')); ?> + stats.missing_product +
                <?php echo wp_json_encode(__(' · Ambiguous ', 'spek-theme')); ?> + stats.ambiguous +
                (stats.error_count ? <?php echo wp_json_encode(__(' · Σφάλματα ', 'spek-theme')); ?> + stats.error_count : '');

            if (!done) { return; }

            let html = '<div class="notice ' + (stats.error_count ? 'notice-warning' : 'notice-success') + ' inline"><p><strong>' +
                (stats.dry_run ? <?php echo wp_json_encode(__('Ο έλεγχος ολοκληρώθηκε.', 'spek-theme')); ?> : <?php echo wp_json_encode(__('Η εισαγωγή ολοκληρώθηκε.', 'spek-theme')); ?>) +
                '</strong><br>' + escapeHtml(modeLabel) +
                '<br>' + escapeHtml(<?php echo wp_json_encode(__('Γραμμές: ', 'spek-theme')); ?>) + stats.rows +
                <?php echo wp_json_encode(__(' · Matched: ', 'spek-theme')); ?> + stats.matched +
                <?php echo wp_json_encode(__(' · EN updates: ', 'spek-theme')); ?> + stats.translated +
                <?php echo wp_json_encode(__(' · Ήδη ίδια: ', 'spek-theme')); ?> + stats.unchanged +
                <?php echo wp_json_encode(__(' · Νέα προϊόντα: ', 'spek-theme')); ?> + stats.created +
                <?php echo wp_json_encode(__(' · Missing products: ', 'spek-theme')); ?> + stats.missing_product +
                <?php echo wp_json_encode(__(' · Ambiguous: ', 'spek-theme')); ?> + stats.ambiguous +
                <?php echo wp_json_encode(__(' · Taxonomy EN updates: ', 'spek-theme')); ?> + stats.taxonomy_updates +
                <?php echo wp_json_encode(__(' · Not ready/skip: ', 'spek-theme')); ?> + stats.not_ready +
                <?php echo wp_json_encode(__(' · Missing English: ', 'spek-theme')); ?> + stats.missing_english +
                <?php echo wp_json_encode(__(' · Σφάλματα: ', 'spek-theme')); ?> + stats.error_count + '</p>';

            if (stats.dry_run && stats.approved) {
                html += '<p><strong>' + escapeHtml(<?php echo wp_json_encode(__('Το dry-run εγκρίθηκε για αυτό το ακριβές CSV. Αποεπιλέξτε το “dry run” και ξανατρέξτε το ίδιο αρχείο όταν είστε έτοιμοι.', 'spek-theme')); ?>) + '</strong></p>';
            }

            if (stats.missing_skus && stats.missing_skus.length) {
                html += '<details><summary>' + escapeHtml(<?php echo wp_json_encode(__('SKU που δεν υπάρχουν στο WordPress', 'spek-theme')); ?>) + ' (' + stats.missing_product + ')</summary><p><code>' + escapeHtml(stats.missing_skus.join(', ')) + '</code></p></details>';
            }

            if (stats.errors && stats.errors.length) {
                html += '<details><summary>' + escapeHtml(<?php echo wp_json_encode(__('Προβολή σφαλμάτων', 'spek-theme')); ?>) + '</summary><ul style="list-style:disc;padding-left:20px;">';
                stats.errors.forEach(function (error) { html += '<li>' + escapeHtml(error) + '</li>'; });
                html += '</ul></details>';
            }

            if (stats.warning_count) {
                html += '<details><summary>' + escapeHtml(<?php echo wp_json_encode(__('Προειδοποιήσεις', 'spek-theme')); ?>) + ' (' + stats.warning_count + ')</summary><ul style="list-style:disc;padding-left:20px;">';
                (stats.warnings || []).forEach(function (warning) { html += '<li>' + escapeHtml(warning) + '</li>'; });
                html += '</ul></details>';
            }
            html += '</div>';
            resultWrap.innerHTML = html;
        }

        async function parseJsonResponse(response) {
            const text = await response.text();

            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error(<?php echo wp_json_encode(__('Ο server επέστρεψε μη αναμενόμενη απάντηση (HTTP ', 'spek-theme')); ?> + response.status + ').');
            }
        }

        async function processBatch(token, dryRun, attempt = 0) {
            const body = new URLSearchParams();
            body.set('action', 'spek_product_import_batch');
            body.set('nonce', ajaxNonce);
            body.set('token', token);
            body.set('dry_run', dryRun ? '1' : '0');

            try {
                const response = await fetch(window.ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                    body: body.toString()
                });

                const json = await parseJsonResponse(response);

                if (!json.success) {
                    const message = json.data && json.data.message
                        ? json.data.message
                        : <?php echo wp_json_encode(__('Αποτυχία επεξεργασίας παρτίδας.', 'spek-theme')); ?>;

                    // A 5xx response can be temporary (PHP-FPM/nginx hiccup). Retry a few times.
                    if (response.status >= 500 && attempt < 3) {
                        progressText.textContent += <?php echo wp_json_encode(__(' · προσωρινό σφάλμα, επανάληψη...', 'spek-theme')); ?>;
                        await new Promise(function (resolve) {
                            window.setTimeout(resolve, 800 * (attempt + 1));
                        });
                        return processBatch(token, dryRun, attempt + 1);
                    }

                    throw new Error(message);
                }

                const data = json.data;
                progressBar.style.width = Math.max(0, Math.min(100, data.percent || 0)) + '%';
                renderStats(data.stats, data.done);

                if (data.done) {
                    progressBar.style.width = '100%';
                    submitButton.disabled = false;
                    submitButton.value = <?php echo wp_json_encode(__('Εκτέλεση εισαγωγής', 'spek-theme')); ?>;
                    return;
                }

                window.setTimeout(function () {
                    processBatch(token, dryRun, 0).catch(function (error) {
                        showError(error.message);
                    });
                }, 120);
            } catch (error) {
                // fetch/network errors and non-JSON 502/503/504 pages are also worth retrying.
                if (attempt < 3 && !String(error.message || '').includes('συνεδρία εισαγωγής')) {
                    await new Promise(function (resolve) {
                        window.setTimeout(resolve, 800 * (attempt + 1));
                    });
                    return processBatch(token, dryRun, attempt + 1);
                }

                throw error;
            }
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const fileInput = document.getElementById('spek_product_csv');
            if (!fileInput.files || !fileInput.files.length) {
                showError(<?php echo wp_json_encode(__('Επίλεξε πρώτα CSV αρχείο.', 'spek-theme')); ?>);
                return;
            }

            submitButton.disabled = true;
            submitButton.value = <?php echo wp_json_encode(__('Προετοιμασία...', 'spek-theme')); ?>;
            progressWrap.style.display = 'block';
            progressBar.style.width = '0%';
            progressText.textContent = <?php echo wp_json_encode(__('Ανέβασμα και έλεγχος CSV...', 'spek-theme')); ?>;
            resultWrap.innerHTML = '';

            const dryRun = dryRunInput.checked;
            const formData = new FormData();
            formData.append('action', 'spek_product_import_init');
            formData.append('nonce', ajaxNonce);
            formData.append('spek_product_csv', fileInput.files[0]);
            formData.append('dry_run', dryRun ? '1' : '0');
            formData.append('import_mode', modeInput ? modeInput.value : 'translations');

            try {
                const response = await fetch(window.ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                });

                const json = await parseJsonResponse(response);
                if (!json.success) {
                    throw new Error(json.data && json.data.message ? json.data.message : <?php echo wp_json_encode(__('Αποτυχία προετοιμασίας εισαγωγής.', 'spek-theme')); ?>);
                }

                submitButton.value = <?php echo wp_json_encode(__('Εισαγωγή σε εξέλιξη...', 'spek-theme')); ?>;
                await processBatch(json.data.token, dryRun);
            } catch (error) {
                showError(error.message);
            }
        });
    }());
    </script>
    <?php
}

/**
 * Initialize an AJAX import session and persist the uploaded CSV temporarily.
 */
function spek_ajax_product_import_init(): void
{
    spek_product_importer_ajax_guard();

    $dry_run = !empty($_POST['dry_run']);
    $import_mode = isset($_POST['import_mode']) ? sanitize_key(wp_unslash($_POST['import_mode'])) : 'translations';
    $import_mode = function_exists('spek_import_mode') ? spek_import_mode($import_mode) : ($import_mode === 'full' ? 'full' : 'translations');

    if (empty($_FILES['spek_product_csv']['tmp_name'])) {
        wp_send_json_error(['message' => __('Δεν επιλέχθηκε CSV αρχείο.', 'spek-theme')], 400);
    }

    $filename = isset($_FILES['spek_product_csv']['name'])
        ? sanitize_file_name(wp_unslash($_FILES['spek_product_csv']['name']))
        : '';

    if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'csv') {
        wp_send_json_error([
            'message' => __('Το αρχείο πρέπει να είναι CSV. Κάντε export/save as CSV UTF-8 από το .xls.', 'spek-theme'),
        ], 400);
    }

    $tmp_name = (string) $_FILES['spek_product_csv']['tmp_name'];
    if (!is_uploaded_file($tmp_name) || !is_readable($tmp_name)) {
        wp_send_json_error(['message' => __('Το uploaded CSV δεν μπορεί να διαβαστεί.', 'spek-theme')], 400);
    }

    $upload_dir = wp_upload_dir();
    if (!empty($upload_dir['error'])) {
        wp_send_json_error(['message' => $upload_dir['error']], 500);
    }

    $import_dir = trailingslashit($upload_dir['basedir']) . 'spek-imports';
    if (!wp_mkdir_p($import_dir)) {
        wp_send_json_error(['message' => __('Δεν ήταν δυνατή η δημιουργία προσωρινού φακέλου εισαγωγής.', 'spek-theme')], 500);
    }

    // Protect temporary CSV files from direct web access on Apache-compatible servers.
    $htaccess = trailingslashit($import_dir) . '.htaccess';
    if (!file_exists($htaccess)) {
        @file_put_contents($htaccess, "Deny from all\n");
    }

    $index_file = trailingslashit($import_dir) . 'index.php';
    if (!file_exists($index_file)) {
        @file_put_contents($index_file, "<?php\n// Silence is golden.\n");
    }

    $token = wp_generate_password(32, false, false);
    $safe_path = trailingslashit($import_dir) . 'import-' . hash('sha256', $token) . '.csv';

    if (!@move_uploaded_file($tmp_name, $safe_path)) {
        wp_send_json_error(['message' => __('Αποτυχία αποθήκευσης του προσωρινού CSV.', 'spek-theme')], 500);
    }

    $file_hash = @hash_file('sha256', $safe_path);
    if (!is_string($file_hash) || $file_hash === '') {
        @unlink($safe_path);
        wp_send_json_error(['message' => __('Δεν ήταν δυνατός ο έλεγχος ακεραιότητας του CSV.', 'spek-theme')], 500);
    }

    // A write run is allowed only after a dry-run of the exact same file + mode.
    if (!$dry_run) {
        $approval = get_user_meta(get_current_user_id(), '_spek_product_import_approval', true);
        $approved = is_array($approval)
            && hash_equals((string) ($approval['hash'] ?? ''), $file_hash)
            && (string) ($approval['mode'] ?? '') === $import_mode
            && (time() - (int) ($approval['time'] ?? 0)) <= DAY_IN_SECONDS;
        if (!$approved) {
            @unlink($safe_path);
            wp_send_json_error([
                'message' => __('Πριν από πραγματική εγγραφή εκτελέστε επιτυχημένο dry-run του ίδιου ακριβώς CSV και της ίδιας λειτουργίας. Η έγκριση ισχύει για 24 ώρες.', 'spek-theme'),
            ], 409);
        }
    }

    $handle = @fopen($safe_path, 'rb');
    if (!$handle) {
        @unlink($safe_path);
        wp_send_json_error(['message' => __('Αποτυχία ανοίγματος του CSV.', 'spek-theme')], 400);
    }

    $sample = (string) fgets($handle);
    rewind($handle);
    $delimiter = spek_detect_csv_delimiter($sample);
    if (fread($handle, 3) !== "\xEF\xBB\xBF") { rewind($handle); }
    $header = fgetcsv($handle, 0, $delimiter);

    if (!$header) {
        fclose($handle);
        @unlink($safe_path);
        wp_send_json_error(['message' => __('Το CSV είναι κενό ή δεν έχει επικεφαλίδες.', 'spek-theme')], 400);
    }

    $header = array_map('spek_normalize_import_header', $header);
    $column_map = spek_build_import_column_map($header);

    foreach (['title', 'sku'] as $required) {
        if (!isset($column_map[$required])) {
            fclose($handle);
            @unlink($safe_path);
            wp_send_json_error([
                'message' => sprintf(__('Λείπει η απαραίτητη στήλη: %s', 'spek-theme'), $required),
            ], 400);
        }
    }

    $offset = (int) ftell($handle);
    fclose($handle);

    $file_size = (int) @filesize($safe_path);
    if ($file_size < 1) {
        @unlink($safe_path);
        wp_send_json_error(['message' => __('Το CSV είναι κενό.', 'spek-theme')], 400);
    }

    $state = [
        'user_id' => get_current_user_id(),
        'path' => $safe_path,
        'delimiter' => $delimiter,
        'column_map' => $column_map,
        'offset' => $offset,
        'file_size' => $file_size,
        'row_number' => 1,
        'file_hash' => $file_hash,
        'stats' => [
            'dry_run' => $dry_run,
            'mode' => $import_mode,
            'rows' => 0,
            'matched' => 0,
            'created' => 0,
            'updated' => 0,
            'translated' => 0,
            'unchanged' => 0,
            'missing_product' => 0,
            'missing_english' => 0,
            'ambiguous' => 0,
            'taxonomy_updates' => 0,
            'not_ready' => 0,
            'skipped' => 0,
            'errors' => [],
            'warnings' => [],
            'missing_skus' => [],
            '_term_updates' => [],
        ],
    ];

    $state_path = trailingslashit($import_dir) . 'state-' . hash('sha256', $token) . '.json';

    if (!spek_product_import_write_state($state_path, $state)) {
        @unlink($safe_path);
        wp_send_json_error([
            'message' => __('Δεν ήταν δυνατή η αποθήκευση της κατάστασης εισαγωγής.', 'spek-theme'),
        ], 500);
    }

    wp_send_json_success([
        'token' => $token,
        'percent' => $file_size > 0 ? round(($offset / $file_size) * 100, 1) : 0,
    ]);
}
add_action('wp_ajax_spek_product_import_init', 'spek_ajax_product_import_init');

/**
 * Process one small AJAX batch.
 */
function spek_ajax_product_import_batch(): void
{
    spek_product_importer_ajax_guard();

    $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
    if ($token === '') {
        wp_send_json_error(['message' => __('Λείπει το import token.', 'spek-theme')], 400);
    }

    if (!preg_match('/^[A-Za-z0-9]{20,64}$/', $token)) {
        wp_send_json_error(['message' => __('Μη έγκυρο import token.', 'spek-theme')], 400);
    }

    $paths = spek_product_import_session_paths($token);
    if (is_wp_error($paths)) {
        wp_send_json_error(['message' => $paths->get_error_message()], 500);
    }

    // Serialize all batches, including overlapping retries and separate import sessions.
    $lock = @fopen(dirname($paths['state']) . '/batch.lock', 'c');
    if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
        if (is_resource($lock)) { fclose($lock); }
        wp_send_json_error(['message' => __('Μια παρτίδα εκτελείται ήδη. Δοκιμάστε ξανά.', 'spek-theme')], 503);
    }
    register_shutdown_function(static function () use ($lock) { flock($lock, LOCK_UN); fclose($lock); });
    $state = spek_product_import_read_state($paths['state']);

    if (!is_array($state)) {
        wp_send_json_error([
            'message' => __('Η συνεδρία εισαγωγής δεν βρέθηκε. Ξεκινήστε ξανά την εισαγωγή.', 'spek-theme'),
        ], 410);
    }

    if ((int) ($state['user_id'] ?? 0) !== get_current_user_id()) {
        wp_send_json_error(['message' => __('Μη έγκυρη συνεδρία εισαγωγής.', 'spek-theme')], 403);
    }

    if (!empty($state['done'])) {
        wp_send_json_success(['done' => true, 'percent' => 100, 'stats' => spek_product_import_public_stats($state['stats'])]);
    }
    $dry_run = !empty($state['stats']['dry_run']);

    $handle = @fopen($paths['csv'], 'rb');
    if (!$handle) {
        @unlink($paths['state']);
        wp_send_json_error(['message' => __('Το προσωρινό CSV δεν μπορεί να ανοιχτεί.', 'spek-theme')], 500);
    }

    if (fseek($handle, (int) $state['offset']) !== 0) {
        fclose($handle);
        wp_send_json_error(['message' => __('Δεν ήταν δυνατή η συνέχιση ανάγνωσης του CSV.', 'spek-theme')], 500);
    }

    $mode = function_exists('spek_import_mode') ? spek_import_mode((string) ($state['stats']['mode'] ?? 'translations')) : 'translations';

    // Translation-only writes do not download media and can safely run in larger
    // batches. Full sync remains conservative because it may create media/terms.
    $batch_size = $dry_run ? 50 : ($mode === 'translations' ? 20 : 1);
    $processed_in_batch = 0;
    $done = false;

    if (function_exists('wp_raise_memory_limit')) {
        wp_raise_memory_limit('admin');
    }

    while ($processed_in_batch < $batch_size) {
        $row = fgetcsv($handle, 0, (string) $state['delimiter']);

        if ($row === false) {
            $done = true;
            break;
        }

        $state['row_number']++;
        $state['offset'] = (int) ftell($handle);

        if (count($row) === 1 && trim((string) $row[0]) === '') {
            continue;
        }

        $processed_in_batch++;
        $state['stats']['rows']++;

        spek_process_product_import_row(
            $row,
            (array) $state['column_map'],
            (int) $state['row_number'],
            $dry_run,
            $state['stats']
        );
    }

    if (!$done && feof($handle)) {
        $done = true;
    }

    $state['offset'] = max((int) $state['offset'], (int) ftell($handle));
    fclose($handle);

    $percent = $state['file_size'] > 0
        ? round(min(100, ($state['offset'] / $state['file_size']) * 100), 1)
        : 0;

    if ($done) {
        @unlink($paths['csv']);
        $state['done'] = true;

        if ($dry_run && empty($state['stats']['error_count'])) {
            update_user_meta(get_current_user_id(), '_spek_product_import_approval', [
                'hash' => (string) ($state['file_hash'] ?? ''),
                'mode' => $mode,
                'time' => time(),
            ]);
            $state['stats']['approved'] = true;
        } elseif (!$dry_run) {
            delete_user_meta(get_current_user_id(), '_spek_product_import_approval');
        }

        spek_product_import_write_state($paths['state'], $state);
        $percent = 100;
    } else {
        if (!spek_product_import_write_state($paths['state'], $state)) {
            wp_send_json_error([
                'message' => __('Δεν ήταν δυνατή η αποθήκευση της προόδου εισαγωγής.', 'spek-theme'),
            ], 500);
        }
    }

    wp_send_json_success([
        'done' => $done,
        'percent' => $percent,
        'stats' => spek_product_import_public_stats($state['stats']),
    ]);
}
add_action('wp_ajax_spek_product_import_batch', 'spek_ajax_product_import_batch');

/**
 * Return deterministic CSV/state paths for an import token.
 *
 * @return array|WP_Error
 */
function spek_product_import_session_paths(string $token)
{
    $upload_dir = wp_upload_dir();
    if (!empty($upload_dir['error'])) {
        return new WP_Error('upload_dir_error', (string) $upload_dir['error']);
    }

    $import_dir = trailingslashit($upload_dir['basedir']) . 'spek-imports';
    $hash = hash('sha256', $token);

    return [
        'csv' => trailingslashit($import_dir) . 'import-' . $hash . '.csv',
        'state' => trailingslashit($import_dir) . 'state-' . $hash . '.json',
    ];
}

/**
 * Persist import progress atomically to disk.
 */
function spek_product_import_write_state(string $state_path, array $state): bool
{
    $json = wp_json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json) || $json === '') {
        return false;
    }

    $tmp_path = $state_path . '.tmp-' . wp_generate_password(8, false, false);
    $written = @file_put_contents($tmp_path, $json, LOCK_EX);

    if ($written === false) {
        @unlink($tmp_path);
        return false;
    }

    if (!@rename($tmp_path, $state_path)) {
        @unlink($tmp_path);
        return false;
    }

    return true;
}

/**
 * Load import progress from disk.
 */
function spek_product_import_read_state(string $state_path): ?array
{
    if (!is_file($state_path) || !is_readable($state_path)) {
        return null;
    }

    $json = @file_get_contents($state_path);
    if (!is_string($json) || $json === '') {
        return null;
    }

    $state = json_decode($json, true);

    return is_array($state) ? $state : null;
}

/**
 * Shared AJAX security checks.
 */
function spek_product_importer_ajax_guard(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Δεν έχετε δικαίωμα εκτέλεσης εισαγωγής.', 'spek-theme')], 403);
    }

    check_ajax_referer('spek_product_import_ajax', 'nonce');
}

/**
 * Process one CSV product row.
 */
function spek_process_product_import_row(
    array $row,
    array $column_map,
    int $row_number,
    bool $dry_run,
    array &$stats
): void {
    spek_import_multilingual_row(spek_map_product_import_row($row, $column_map), $row_number, $dry_run, $stats);
}

/**
 * Keep a bounded error list so large imports do not bloat AJAX responses/state files.
 */
function spek_product_import_add_error(array &$stats, string $message): void
{
    if (!isset($stats['errors']) || !is_array($stats['errors'])) {
        $stats['errors'] = [];
    }

    if (count($stats['errors']) < 100) {
        $stats['errors'][] = $message;
    }

    $stats['error_count'] = (int) ($stats['error_count'] ?? 0) + 1;
}

/**
 * Return only the stats needed by the browser.
 */
function spek_product_import_public_stats(array $stats): array
{
    return [
        'dry_run' => !empty($stats['dry_run']),
        'mode' => (string) ($stats['mode'] ?? 'translations'),
        'rows' => (int) ($stats['rows'] ?? 0),
        'matched' => (int) ($stats['matched'] ?? 0),
        'created' => (int) ($stats['created'] ?? 0),
        'updated' => (int) ($stats['updated'] ?? 0),
        'translated' => (int) ($stats['translated'] ?? 0),
        'unchanged' => (int) ($stats['unchanged'] ?? 0),
        'missing_product' => (int) ($stats['missing_product'] ?? 0),
        'missing_english' => (int) ($stats['missing_english'] ?? 0),
        'ambiguous' => (int) ($stats['ambiguous'] ?? 0),
        'taxonomy_updates' => (int) ($stats['taxonomy_updates'] ?? 0),
        'not_ready' => (int) ($stats['not_ready'] ?? 0),
        'skipped' => (int) ($stats['skipped'] ?? 0),
        'error_count' => (int) ($stats['error_count'] ?? count($stats['errors'] ?? [])),
        'errors' => array_values(array_slice((array) ($stats['errors'] ?? []), 0, 100)),
        'warning_count' => (int) ($stats['warning_count'] ?? 0),
        'warnings' => array_values(array_slice((array) ($stats['warnings'] ?? []), 0, 100)),
        'missing_skus' => array_values(array_slice((array) ($stats['missing_skus'] ?? []), 0, 100)),
        'approved' => !empty($stats['approved']),
    ];
}

/** Detect CSV delimiter from the header line. */
function spek_detect_csv_delimiter(string $sample): string
{
    $delimiters = [',' => 0, ';' => 0, "\t" => 0];

    foreach ($delimiters as $delimiter => $unused) {
        $delimiters[$delimiter] = substr_count($sample, $delimiter);
    }

    arsort($delimiters);
    $detected = (string) key($delimiters);

    return $delimiters[$detected] > 0 ? $detected : ',';
}

/** Normalize a CSV header for forgiving matching. */
function spek_normalize_import_header($value): string
{
    $value = (string) $value;
    $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
    $value = trim($value, " \t\n\r\0\x0B\"");
    $value = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    $value = preg_replace('/[\s_\-]+/', ' ', $value);

    return trim((string) $value);
}

/** Map known legacy headers to internal importer keys. */
function spek_build_import_column_map(array $headers): array
{
    $aliases = [
        'id' => ['id'],
        'title' => ['title', 'name', 'product name'],
        'sku' => ['sku', 'product sku'],
        'product_type' => ['product type', 'type'],
        'content' => ['content', 'description', 'product description'],
        'image_url' => ['image url', 'image', 'images'],
        'image_alt' => ['image alt text', 'image alt', 'alt text'],
        'categories' => ['product categories', 'product category', 'categories', 'category'],
    ];

    // Enriched SPEK CSV columns; previous legacy aliases remain supported.
    $extended_keys = [
        'product_dimensions',
        'product_material',
        'product_color',
        'product_application_text',
        'product_compatibility',
        'product_installation_type',
        'product_short_description',
        'product_packaging',
        'product_weight',
        'product_capacity',
        'product_features',
        'title_en',
        'content_en',
        'product_short_description_en',
        'catalogue_pages',
        'source_excel_rows',
        'review_status',
        'review_notes',
        'product_datasheet',
        'product_catalogue_pdf',
        'product_installation_video',
        'product_application_terms',
        'product_material_terms',
        'product_series_terms',
        'import_action',
    ];
    foreach ($extended_keys as $key) {
        $aliases[$key] = [spek_normalize_import_header($key)];
    }

    $aliases['title'] = array_merge($aliases['title'], ['title el', 'title gr', 'greek description', 'description el', 'description gr', 'περιγραφή', 'ελληνική περιγραφή']);
    $aliases['sku'] = array_merge($aliases['sku'], ['product code', 'κωδικός', 'κωδικός προϊόντος']);
    $aliases['title_en'] = array_merge($aliases['title_en'], ['english description', 'description en', 'αγγλική περιγραφή']);
    foreach (spek_product_translated_meta_keys() as $key) {
        $aliases[$key] = [spek_normalize_import_header($key)];
        $aliases[$key . '_en'] = [spek_normalize_import_header($key . '_en')];
    }
    foreach (['categories', 'product_application_terms', 'product_material_terms', 'product_series_terms'] as $key) {
        $aliases[$key . '_en'] = [spek_normalize_import_header($key . '_en')];
    }
    foreach ($aliases as $key => &$names) {
        if (substr($key, -3) !== '_en' && !in_array($key, ['sku', 'id', 'import_action', 'review_status', 'review_notes'], true)) {
            $names[] = spek_normalize_import_header($key . '_el');
            $names[] = spek_normalize_import_header($key . '_gr');
        }
    }
    unset($names);
    $map = [];

    foreach ($aliases as $key => $names) {
        foreach ($headers as $index => $header) {
            if (in_array($header, $names, true)) {
                $map[$key] = $index;
                break;
            }
        }
    }

    // A three-column description CSV provides both the title and description.
    foreach (['title' => 'content', 'title_en' => 'content_en'] as $title_key => $content_key) {
        if (!isset($map[$content_key]) && isset($map[$title_key])) {
            $heading = $headers[$map[$title_key]];
            if (strpos($heading, 'description') !== false || strpos($heading, 'περιγραφή') !== false) {
                $map[$content_key] = $map[$title_key];
            }
        }
    }
    return $map;
}

/** Convert raw CSV row to named values. */
function spek_map_product_import_row(array $row, array $column_map): array
{
    $data = [];

    foreach ($column_map as $key => $index) {
        $value = isset($row[$index]) ? (string) $row[$index] : '';
        $data[$key] = spek_import_to_utf8($value);
    }

    return $data;
}

/** Best-effort UTF-8 normalization for legacy Greek CSV exports. */
function spek_import_to_utf8(string $value): string
{
    if ($value === '' || !function_exists('mb_check_encoding') || mb_check_encoding($value, 'UTF-8')) {
        return $value;
    }

    $encodings = ['Windows-1253', 'ISO-8859-7', 'Windows-1252', 'ISO-8859-1'];
    $detected = function_exists('mb_detect_encoding')
        ? mb_detect_encoding($value, $encodings, true)
        : false;

    if ($detected && function_exists('mb_convert_encoding')) {
        return mb_convert_encoding($value, 'UTF-8', $detected);
    }

    return $value;
}

/** Download/reuse remote image, set featured image and attachment alt. */
function spek_import_product_image(int $post_id, string $url, string $alt = '')
{
    $url = esc_url_raw(trim($url));

    if (!$url || !wp_http_validate_url($url)) {
        return new WP_Error('invalid_image_url', __('Μη έγκυρο Image URL.', 'spek-theme'));
    }

    $existing = get_posts([
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'no_found_rows' => true,
        'suppress_filters' => true,
        'meta_query' => [[
            'key' => '_spek_import_source_url',
            'value' => $url,
            'compare' => '=',
        ]],
    ]);

    if ($existing) {
        $attachment_id = (int) $existing[0];

        if ($alt !== '') {
            update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
        }

        set_post_thumbnail($post_id, $attachment_id);
        return $attachment_id;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Keep a single AJAX request comfortably below common nginx/PHP timeouts.
    $tmp = download_url($url, 10);
    if (is_wp_error($tmp)) {
        return $tmp;
    }

    $path = (string) wp_parse_url($url, PHP_URL_PATH);
    $filename = sanitize_file_name(wp_basename($path));

    if ($filename === '') {
        $filename = 'product-' . $post_id . '.jpg';
    }

    $file_array = [
        'name' => $filename,
        'tmp_name' => $tmp,
    ];

    $attachment_id = media_handle_sideload($file_array, $post_id, get_the_title($post_id));

    if (is_wp_error($attachment_id)) {
        @unlink($tmp);
        return $attachment_id;
    }

    update_post_meta($attachment_id, '_spek_import_source_url', $url);

    if ($alt !== '') {
        update_post_meta($attachment_id, '_wp_attachment_image_alt', $alt);
    }

    set_post_thumbnail($post_id, $attachment_id);

    return (int) $attachment_id;
}

/** Backward-compatible lookup: never choose arbitrarily from multiple translations. */
function spek_find_product_by_sku(string $sku): int {
    $id = spek_import_product_identity($sku, spek_language_for_locale('el'));
    return is_wp_error($id) ? 0 : $id;
}
/** Backward-compatible Greek category hierarchy importer. */
function spek_import_product_categories(string $raw, array &$stats, int $row_number): array {
    $ids = [];
    foreach (spek_import_term_paths($raw, 'product_category') as $levels) {
        $parent = 0;
        foreach ($levels as $name) {
            $id = spek_import_term_identity($name, 'product_category', $parent, spek_language_for_locale('el'), false);
            if (is_wp_error($id)) { spek_product_import_add_error($stats, $id->get_error_message()); $parent = 0; break; }
            $parent = (int) $id;
        }
        if ($parent) { $ids[] = $parent; }
    }
    return array_values(array_unique($ids));
}
