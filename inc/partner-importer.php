<?php
/**
 * SPEK Partner / Store Locator XLSX & CSV Importer.
 *
 * Imports partner/store rows, matches a Google Maps place when possible,
 * geocodes locator coordinates with TomTom, and updates the spek_partner CPT.
 *
 * Google Places data is used only for place identity + Google Maps URL. The
 * public locator remains TomTom/Leaflet, so its coordinates are sourced from
 * the user-provided Maps URL (when it contains coordinates) or TomTom.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Add importer below the Store Locations CPT. */
function spek_partner_importer_menu(): void
{
    add_submenu_page(
        'edit.php?post_type=spek_partner',
        __('Εισαγωγή συνεργατών', 'spek-theme'),
        __('Εισαγωγή Excel', 'spek-theme'),
        'manage_options',
        'spek-partner-importer',
        'spek_render_partner_importer_page'
    );
}
add_action('admin_menu', 'spek_partner_importer_menu');

/** Importer screen. */
function spek_render_partner_importer_page(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Δεν έχετε δικαίωμα πρόσβασης σε αυτή τη σελίδα.', 'spek-theme'));
    }

    $nonce = wp_create_nonce('spek_partner_import_ajax');
    $has_google = defined('SPEK_GOOGLE_MAPS_API_KEY') && trim((string) SPEK_GOOGLE_MAPS_API_KEY) !== '';
    $has_tomtom = defined('SPEK_TOMTOM_API_KEY') && trim((string) SPEK_TOMTOM_API_KEY) !== '';
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Εισαγωγή συνεργατών / σημείων πώλησης', 'spek-theme'); ?></h1>

        <p style="max-width:920px;">
            <?php esc_html_e('Ανεβάστε Excel (.xlsx) ή CSV. Ο importer αντιστοιχίζει τις στήλες του αρχείου με τα πεδία συνεργατών, δημιουργεί/ενημερώνει τα σημεία πώλησης, αντιστοιχίζει τον Νομό στην taxonomy «Περιοχές» και προσπαθεί να βρει το σωστό κατάστημα στο Google Maps.', 'spek-theme'); ?>
        </p>

        <div style="max-width:920px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:16px 18px;margin:18px 0;">
            <strong><?php esc_html_e('Κατάσταση υπηρεσιών', 'spek-theme'); ?></strong>
            <ul style="margin-bottom:0;">
                <li>
                    Google Places:
                    <strong style="color:<?php echo $has_google ? '#008a20' : '#b32d2e'; ?>">
                        <?php echo $has_google ? esc_html__('ενεργό', 'spek-theme') : esc_html__('δεν έχει οριστεί API key', 'spek-theme'); ?>
                    </strong>
                    <?php if (!$has_google) : ?>
                        — <code>define('SPEK_GOOGLE_MAPS_API_KEY', '...');</code>
                    <?php endif; ?>
                </li>
                <li>
                    TomTom locator geocoding:
                    <strong style="color:<?php echo $has_tomtom ? '#008a20' : '#b32d2e'; ?>">
                        <?php echo $has_tomtom ? esc_html__('ενεργό', 'spek-theme') : esc_html__('δεν έχει οριστεί API key', 'spek-theme'); ?>
                    </strong>
                    <?php if (!$has_tomtom) : ?>
                        — <code>define('SPEK_TOMTOM_API_KEY', '...');</code>
                    <?php endif; ?>
                </li>
            </ul>
        </div>

        <form id="spek-partner-import-form" enctype="multipart/form-data" style="max-width:920px;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:18px;">
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="spek_partner_file"><?php esc_html_e('Αρχείο', 'spek-theme'); ?></label></th>
                        <td>
                            <input type="file" id="spek_partner_file" name="partner_file" accept=".xlsx,.csv" required>
                            <p class="description"><?php esc_html_e('Αναγνωρίζονται: Όνομα, Οδός, Πόλη/Περιοχή, TK, Τηλέφωνο 1, Νομός, EMAIL, Χάρτης. Επιπλέον συνώνυμα headers γίνονται δεκτά.', 'spek-theme'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="spek_partner_match_threshold"><?php esc_html_e('Ελάχιστη βεβαιότητα Google match', 'spek-theme'); ?></label></th>
                        <td>
                            <input type="number" id="spek_partner_match_threshold" name="match_threshold" value="68" min="50" max="95" step="1" class="small-text"> %
                            <p class="description"><?php esc_html_e('Κάτω από αυτό το όριο το σημείο εισάγεται χωρίς αυτόματο Google Maps link και επισημαίνεται για έλεγχο.', 'spek-theme'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Λειτουργία', 'spek-theme'); ?></th>
                        <td>
                            <label><input type="checkbox" id="spek_partner_import_dry_run" name="dry_run" value="1" checked> <?php esc_html_e('Δοκιμαστική εκτέλεση — χωρίς αλλαγές στη βάση (εκτελούνται κανονικά εξωτερικά API αιτήματα)', 'spek-theme'); ?></label>
                            <br>
                            <label><input type="checkbox" id="spek_partner_import_update_existing" name="update_existing" value="1" checked> <?php esc_html_e('Ενημέρωση υπαρχόντων σημείων όταν βρεθεί ίδια επωνυμία + διεύθυνση/ΤΚ', 'spek-theme'); ?></label>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(__('Εκτέλεση εισαγωγής', 'spek-theme'), 'primary', 'spek_partner_import_submit', false); ?>
        </form>

        <div id="spek-partner-import-progress" style="display:none;max-width:920px;margin-top:24px;">
            <div style="height:12px;background:#dcdcde;border-radius:999px;overflow:hidden;">
                <div id="spek-partner-import-progress-bar" style="height:100%;width:0;background:#2271b1;transition:width .2s ease;"></div>
            </div>
            <p id="spek-partner-import-progress-text"></p>
        </div>

        <div id="spek-partner-import-result" style="max-width:1100px;margin-top:18px;"></div>
    </div>

    <script>
    (function () {
        const form = document.getElementById('spek-partner-import-form');
        const fileInput = document.getElementById('spek_partner_file');
        const submitButton = document.getElementById('spek_partner_import_submit');
        const progressWrap = document.getElementById('spek-partner-import-progress');
        const progressBar = document.getElementById('spek-partner-import-progress-bar');
        const progressText = document.getElementById('spek-partner-import-progress-text');
        const resultWrap = document.getElementById('spek-partner-import-result');
        const ajaxUrl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
        const nonce = <?php echo wp_json_encode($nonce); ?>;

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value == null ? '' : String(value);
            return div.innerHTML;
        }

        function showError(message) {
            resultWrap.innerHTML = '<div class="notice notice-error inline"><p>' + escapeHtml(message) + '</p></div>';
        }

        let completeReview = [];

        function csvCell(value) {
            let cell = String(value == null ? '' : value);
            // Avoid CSV formula injection when opened in Excel/LibreOffice.
            if (/^\s*[=+@-]/.test(cell)) {
                cell = "'" + cell;
            }
            return '"' + cell.replace(/"/g, '""') + '"';
        }

        function downloadReview() {
            if (!completeReview.length) { return; }
            const columns = [
                ['row', 'Γραμμή'], ['name', 'Επωνυμία'], ['action', 'Ενέργεια'],
                ['google_status', 'Κατάσταση Google'], ['score', 'Βεβαιότητα'],
                ['google_name', 'Επωνυμία Google'], ['google_address', 'Διεύθυνση Google'],
                ['coords_source', 'Πηγή συντεταγμένων'], ['note', 'Σημειώσεις']
            ];
            const lines = [columns.map((col) => csvCell(col[1])).join(';')];
            completeReview.forEach((item) => {
                lines.push(columns.map((col) => csvCell(item[col[0]])).join(';'));
            });
            const blob = new Blob(['\uFEFF' + lines.join('\r\n')], {type: 'text/csv;charset=utf-8'});
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'spek-partner-import-report.csv';
            document.body.appendChild(link);
            link.click();
            link.remove();
            setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
        }

        resultWrap.addEventListener('click', function (event) {
            if (event.target && event.target.id === 'spek-partner-export-report') {
                downloadReview();
            }
        });

        function renderResult(data) {
            const stats = data.stats || {};
            const rows = Array.isArray(data.review) ? data.review : [];
            completeReview = rows;
            const summary = [
                ['Σύνολο', stats.total || 0],
                ['Δημιουργία', stats.created || 0],
                ['Ενημέρωση', stats.updated || 0],
                ['Χωρίς αλλαγές', stats.unchanged || 0],
                ['Google matches', stats.google_matched || 0],
                ['Για έλεγχο', stats.needs_review || 0],
                ['Με συντεταγμένες', stats.geocoded || 0],
                ['Παραλείφθηκαν', stats.skipped || 0],
                ['Σφάλματα', stats.errors_count || 0],
            ];

            let html = '<div class="notice notice-success inline"><p><strong>' + (data.dry_run ? 'Η δοκιμαστική εκτέλεση ολοκληρώθηκε.' : 'Η εισαγωγή ολοκληρώθηκε.') + '</strong></p></div>';
            html += '<table class="widefat striped" style="max-width:760px;margin:14px 0 22px"><tbody>';
            summary.forEach(function (item) {
                html += '<tr><th style="width:260px">' + escapeHtml(item[0]) + '</th><td>' + escapeHtml(item[1]) + '</td></tr>';
            });
            html += '</tbody></table>';

            if (rows.length) {
                html += '<h2>Αναφορά αντιστοίχισης</h2><p><button type="button" class="button button-secondary" id="spek-partner-export-report">Εξαγωγή πλήρους αναφοράς CSV (' + rows.length + ' γραμμές)</button></p><div style="overflow:auto;max-height:640px"><table class="widefat striped"><thead><tr>' +
                    '<th>Γραμμή</th><th>Επωνυμία</th><th>Ενέργεια</th><th>Google</th><th>Score</th><th>Google αποτέλεσμα</th><th>Locator</th><th>Σημείωση</th>' +
                    '</tr></thead><tbody>';
                rows.forEach(function (row) {
                    const google = row.google_status || '—';
                    html += '<tr>' +
                        '<td>' + escapeHtml(row.row) + '</td>' +
                        '<td><strong>' + escapeHtml(row.name) + '</strong></td>' +
                        '<td>' + escapeHtml(row.action) + '</td>' +
                        '<td>' + escapeHtml(google) + '</td>' +
                        '<td>' + escapeHtml(row.score == null ? '—' : row.score + '%') + '</td>' +
                        '<td>' + escapeHtml([row.google_name, row.google_address].filter(Boolean).join(' — ')) + '</td>' +
                        '<td>' + escapeHtml(row.coords_source || '—') + '</td>' +
                        '<td>' + escapeHtml(row.note || '') + '</td>' +
                    '</tr>';
                });
                html += '</tbody></table></div>';
            }

            if (Array.isArray(stats.errors) && stats.errors.length) {
                html += '<h2>Σφάλματα / προειδοποιήσεις</h2><ul style="list-style:disc;padding-left:22px">';
                stats.errors.forEach(function (error) { html += '<li>' + escapeHtml(error) + '</li>'; });
                html += '</ul>';
            }

            resultWrap.innerHTML = html;
        }

        async function request(body) {
            const response = await fetch(ajaxUrl, { method: 'POST', body, credentials: 'same-origin' });
            const payload = await response.json();
            if (!payload || !payload.success) {
                const message = payload && payload.data && payload.data.message ? payload.data.message : 'Άγνωστο σφάλμα importer.';
                throw new Error(message);
            }
            return payload.data;
        }

        async function processBatch(token) {
            const body = new URLSearchParams();
            body.set('action', 'spek_partner_import_batch');
            body.set('nonce', nonce);
            body.set('token', token);

            const data = await request(body);
            const percent = data.total > 0 ? Math.round((data.processed / data.total) * 100) : 100;
            progressBar.style.width = percent + '%';
            progressText.textContent = 'Επεξεργασία ' + data.processed + ' / ' + data.total + '…';

            if (data.done) {
                renderResult(data);
                submitButton.disabled = false;
                return;
            }

            await processBatch(token);
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (!fileInput.files.length) {
                showError('Επίλεξε πρώτα Excel ή CSV αρχείο.');
                return;
            }

            submitButton.disabled = true;
            resultWrap.innerHTML = '';
            progressWrap.style.display = 'block';
            progressBar.style.width = '0%';
            progressText.textContent = 'Ανέβασμα και έλεγχος αρχείου…';

            const body = new FormData(form);
            body.append('action', 'spek_partner_import_init');
            body.append('nonce', nonce);

            try {
                const data = await request(body);
                progressText.textContent = 'Βρέθηκαν ' + data.total + ' εγγραφές. Ξεκινά η αντιστοίχιση…';
                await processBatch(data.token);
            } catch (error) {
                showError(error.message || String(error));
                submitButton.disabled = false;
            }
        });
    }());
    </script>
    <?php
}

/** Guard importer AJAX endpoints. */
function spek_partner_importer_ajax_guard(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => __('Δεν έχετε δικαίωμα εισαγωγής συνεργατών.', 'spek-theme')], 403);
    }
    check_ajax_referer('spek_partner_import_ajax', 'nonce');
}

/** Start an import session. */
function spek_ajax_partner_import_init(): void
{
    spek_partner_importer_ajax_guard();

    if (empty($_FILES['partner_file']['tmp_name']) || !is_uploaded_file($_FILES['partner_file']['tmp_name'])) {
        wp_send_json_error(['message' => __('Δεν επιλέχθηκε έγκυρο αρχείο.', 'spek-theme')], 400);
    }

    $filename = isset($_FILES['partner_file']['name']) ? sanitize_file_name(wp_unslash($_FILES['partner_file']['name'])) : '';
    $ext = strtolower((string) pathinfo($filename, PATHINFO_EXTENSION));

    if (!in_array($ext, ['xlsx', 'csv'], true)) {
        wp_send_json_error(['message' => __('Υποστηρίζονται μόνο .xlsx και .csv αρχεία.', 'spek-theme')], 400);
    }

    $tmp = (string) $_FILES['partner_file']['tmp_name'];
    $parsed = $ext === 'xlsx' ? spek_partner_import_read_xlsx($tmp) : spek_partner_import_read_csv($tmp);

    if (is_wp_error($parsed)) {
        wp_send_json_error(['message' => $parsed->get_error_message()], 400);
    }

    if (count($parsed) < 2) {
        wp_send_json_error(['message' => __('Το αρχείο είναι κενό ή δεν περιέχει γραμμές δεδομένων.', 'spek-theme')], 400);
    }

    $headers = array_map('spek_partner_import_normalize_header', array_shift($parsed));
    $column_map = spek_partner_import_column_map($headers);

    if (!isset($column_map['name'])) {
        wp_send_json_error(['message' => __('Δεν βρέθηκε στήλη «Όνομα».', 'spek-theme')], 400);
    }

    $rows = [];
    $seen_rows = [];
    foreach ($parsed as $index => $raw_row) {
        $row = spek_partner_import_map_row($raw_row, $column_map);
        // Keep incomplete but nonempty rows so the review inbox can repair
        // missing names instead of silently dropping those spreadsheet records.
        $nonempty = array_filter($row, static function ($value): bool {
            return trim((string) $value) !== '';
        });
        if (!$nonempty) {
            continue;
        }
        $row['_source_row'] = $index + 2;
        // Exact duplicates in one upload should not create the same shop twice.
        // A name without an address/postcode is NOT sufficient to deduplicate.
        if ($row['address'] !== '' || $row['postcode'] !== '') {
            $identity = spek_partner_import_key($row);
            if (isset($seen_rows[$identity])) {
                $row['_duplicate_source_row'] = $seen_rows[$identity];
            } else {
                $seen_rows[$identity] = $row['_source_row'];
            }
        }
        $rows[] = $row;
    }

    if (!$rows) {
        wp_send_json_error(['message' => __('Δεν βρέθηκαν έγκυρες εγγραφές με επωνυμία.', 'spek-theme')], 400);
    }

    $threshold = isset($_POST['match_threshold']) ? (int) $_POST['match_threshold'] : 68;
    $threshold = max(50, min(95, $threshold));

    $state = [
        'rows' => $rows,
        'index' => 0,
        'dry_run' => !empty($_POST['dry_run']),
        'update_existing' => !empty($_POST['update_existing']),
        'threshold' => $threshold,
        'stats' => [
            'total' => count($rows),
            'created' => 0,
            'updated' => 0,
            'unchanged' => 0,
            'google_matched' => 0,
            'needs_review' => 0,
            'geocoded' => 0,
            'skipped' => 0,
            'errors_count' => 0,
            'errors' => [],
        ],
        'review' => [],
    ];

    $token = wp_generate_password(32, false, false);
    $path = spek_partner_import_state_path($token, true);
    if (!$path || !spek_partner_import_write_state($path, $state)) {
        wp_send_json_error(['message' => __('Δεν ήταν δυνατή η δημιουργία προσωρινού import session.', 'spek-theme')], 500);
    }

    wp_send_json_success([
        'token' => $token,
        'total' => count($rows),
    ]);
}
add_action('wp_ajax_spek_partner_import_init', 'spek_ajax_partner_import_init');

/** Process exactly one row per request to keep API calls safe and observable. */
function spek_ajax_partner_import_batch(): void
{
    spek_partner_importer_ajax_guard();

    $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
    if ($token === '' || !preg_match('/^[A-Za-z0-9]+$/', $token)) {
        wp_send_json_error(['message' => __('Μη έγκυρο import token.', 'spek-theme')], 400);
    }

    $path = spek_partner_import_state_path($token, false);
    $state = $path ? spek_partner_import_read_state($path) : null;
    if (!$state) {
        wp_send_json_error(['message' => __('Το import session δεν βρέθηκε ή έχει λήξει.', 'spek-theme')], 404);
    }

    $total = count($state['rows']);
    $index = (int) $state['index'];

    if ($index < $total) {
        $row = $state['rows'][$index];
        spek_partner_import_process_row($row, (bool) $state['dry_run'], (bool) $state['update_existing'], (int) $state['threshold'], $state['stats'], $state['review']);
        $state['index'] = $index + 1;
    }

    $done = (int) $state['index'] >= $total;

    if ($done) {
        @unlink($path);
    } elseif (!spek_partner_import_write_state($path, $state)) {
        wp_send_json_error(['message' => __('Δεν ήταν δυνατή η αποθήκευση της προόδου.', 'spek-theme')], 500);
    }

    wp_send_json_success([
        'processed' => (int) $state['index'],
        'total' => $total,
        'done' => $done,
        'dry_run' => (bool) $state['dry_run'],
        'stats' => $state['stats'],
        'review' => $done ? $state['review'] : [],
    ]);
}
add_action('wp_ajax_spek_partner_import_batch', 'spek_ajax_partner_import_batch');

/** Resolve and optionally create the importer state directory. */
function spek_partner_import_state_path(string $token, bool $create_dir): string
{
    $upload = wp_upload_dir();
    if (!empty($upload['error'])) {
        return '';
    }

    $dir = trailingslashit($upload['basedir']) . 'spek-partner-imports';
    if ($create_dir && !is_dir($dir)) {
        if (!wp_mkdir_p($dir)) {
            return '';
        }
        @file_put_contents(trailingslashit($dir) . 'index.php', "<?php\n// Silence is golden.\n");
        @file_put_contents(trailingslashit($dir) . '.htaccess', "Deny from all\n");
    }

    if (!is_dir($dir)) {
        return '';
    }

    return trailingslashit($dir) . 'state-' . hash('sha256', $token) . '.json';
}

function spek_partner_import_write_state(string $path, array $state): bool
{
    $json = wp_json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (!is_string($json)) {
        return false;
    }

    $tmp = $path . '.tmp';
    if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
        return false;
    }

    return @rename($tmp, $path);
}

function spek_partner_import_read_state(string $path): ?array
{
    if (!is_readable($path)) {
        return null;
    }
    $decoded = json_decode((string) file_get_contents($path), true);
    return is_array($decoded) ? $decoded : null;
}

/** Read one file from an XLSX ZIP container. */
function spek_partner_import_xlsx_file(string $path, string $internal)
{
    if (class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return false;
        }
        $content = $zip->getFromName($internal);
        $zip->close();
        return $content;
    }

    // WordPress bundles PclZip, so XLSX imports do not depend on the PHP zip extension.
    if (!class_exists('PclZip')) {
        $pclzip = ABSPATH . 'wp-admin/includes/class-pclzip.php';
        if (is_readable($pclzip)) {
            require_once $pclzip;
        }
    }

    if (class_exists('PclZip')) {
        $archive = new PclZip($path);
        $files = $archive->extract(PCLZIP_OPT_BY_NAME, $internal, PCLZIP_OPT_EXTRACT_AS_STRING);
        if (is_array($files) && !empty($files[0]['content'])) {
            return $files[0]['content'];
        }
    }

    return false;
}

/** Read a simple tabular XLSX without requiring PhpSpreadsheet. */
function spek_partner_import_read_xlsx(string $path)
{
    $shared = [];
    $shared_xml = spek_partner_import_xlsx_file($path, 'xl/sharedStrings.xml');
    if (is_string($shared_xml) && $shared_xml !== '') {
        $doc = new DOMDocument();
        if (@$doc->loadXML($shared_xml, LIBXML_NONET | LIBXML_COMPACT)) {
            $xpath = new DOMXPath($doc);
            $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            foreach ($xpath->query('//a:si') as $node) {
                $parts = [];
                foreach ($xpath->query('.//a:t', $node) as $text_node) {
                    $parts[] = $text_node->textContent;
                }
                $shared[] = implode('', $parts);
            }
        }
    }

    $sheet_xml = spek_partner_import_xlsx_file($path, 'xl/worksheets/sheet1.xml');
    if (!is_string($sheet_xml) || $sheet_xml === '') {
        return new WP_Error('spek_partner_no_sheet', __('Δεν βρέθηκε το πρώτο φύλλο στο Excel ή ο server δεν μπορεί να διαβάσει ZIP/XLSX.', 'spek-theme'));
    }

    $doc = new DOMDocument();
    if (!@$doc->loadXML($sheet_xml, LIBXML_NONET | LIBXML_COMPACT)) {
        return new WP_Error('spek_partner_bad_sheet', __('Το πρώτο φύλλο του Excel δεν είναι έγκυρο.', 'spek-theme'));
    }

    $xpath = new DOMXPath($doc);
    $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
    $rows = [];

    foreach ($xpath->query('//a:sheetData/a:row') as $row_node) {
        $row = [];
        foreach ($xpath->query('./a:c', $row_node) as $cell) {
            $ref = $cell->attributes->getNamedItem('r');
            $cell_ref = $ref ? $ref->nodeValue : '';
            if (!preg_match('/^([A-Z]+)\d+$/', $cell_ref, $matches)) {
                continue;
            }
            $column = spek_partner_import_excel_column_index($matches[1]);
            $type_node = $cell->attributes->getNamedItem('t');
            $type = $type_node ? $type_node->nodeValue : '';
            $value = '';

            if ($type === 'inlineStr') {
                $parts = [];
                foreach ($xpath->query('.//a:is//a:t', $cell) as $text_node) {
                    $parts[] = $text_node->textContent;
                }
                $value = implode('', $parts);
            } else {
                $value_node = $xpath->query('./a:v', $cell)->item(0);
                $raw = $value_node ? $value_node->textContent : '';
                if ($type === 's' && $raw !== '' && isset($shared[(int) $raw])) {
                    $value = $shared[(int) $raw];
                } else {
                    $value = $raw;
                }
            }

            $row[$column] = trim((string) $value);
        }

        if ($row) {
            $max = max(array_keys($row));
            $dense = array_fill(0, $max + 1, '');
            foreach ($row as $column => $value) {
                $dense[$column] = $value;
            }
            $rows[] = $dense;
        }
    }

    return $rows;
}

function spek_partner_import_excel_column_index(string $letters): int
{
    $index = 0;
    foreach (str_split($letters) as $letter) {
        $index = ($index * 26) + (ord($letter) - 64);
    }
    return max(0, $index - 1);
}

/** Read CSV with delimiter autodetection. */
function spek_partner_import_read_csv(string $path)
{
    $handle = @fopen($path, 'rb');
    if (!$handle) {
        return new WP_Error('spek_partner_bad_csv', __('Το CSV δεν μπόρεσε να διαβαστεί.', 'spek-theme'));
    }

    $sample = (string) fgets($handle);
    rewind($handle);
    $delimiters = [',' => substr_count($sample, ','), ';' => substr_count($sample, ';'), "\t" => substr_count($sample, "\t")];
    arsort($delimiters);
    $delimiter = (string) array_key_first($delimiters);
    $rows = [];

    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        $rows[] = array_map(static function ($value): string {
            $value = (string) $value;
            if (substr($value, 0, 3) === "\xEF\xBB\xBF") {
                $value = substr($value, 3);
            }
            return trim($value);
        }, $row);
    }
    fclose($handle);

    return $rows;
}

/**
 * Fold Greek diacritics before comparisons.
 *
 * WordPress remove_accents() does not reliably strip Greek tonos/dialytika on
 * all installations, so importer matching must normalize them explicitly.
 */
function spek_partner_import_fold_accents($value): string
{
    $value = mb_strtolower(trim((string) $value), 'UTF-8');
    $value = strtr($value, [
        'ά' => 'α',
        'έ' => 'ε',
        'ή' => 'η',
        'ί' => 'ι',
        'ϊ' => 'ι',
        'ΐ' => 'ι',
        'ό' => 'ο',
        'ύ' => 'υ',
        'ϋ' => 'υ',
        'ΰ' => 'υ',
        'ώ' => 'ω',
        'ς' => 'σ',
    ]);

    return remove_accents($value);
}

function spek_partner_import_normalize_header($value): string
{
    $value = spek_partner_import_fold_accents($value);
    $value = str_replace(['/', '\\'], ' ', $value);
    $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
    return trim((string) preg_replace('/\s+/u', ' ', (string) $value));
}

/** Map flexible Excel headers to importer fields. */
function spek_partner_import_column_map(array $headers): array
{
    $aliases = [
        'name' => ['ονομα', 'επωνυμια', 'name', 'partner', 'καταστημα', 'συνεργατης'],
        'address' => ['οδος', 'διευθυνση', 'address', 'street'],
        'area' => ['πολη περιοχη', 'πολη', 'περιοχη', 'city area', 'city', 'area'],
        'postcode' => ['tk', 'τκ', 'ταχυδρομικος κωδικας', 'postal code', 'postcode', 'zip'],
        'phone' => ['τηλεφωνο 1', 'τηλεφωνο', 'phone 1', 'phone', 'telephone'],
        'region' => ['νομος', 'περιφερεια', 'region', 'prefecture', 'county'],
        'email' => ['email', 'e mail', 'ηλεκτρονικο ταχυδρομειο'],
        'maps_url' => ['χαρτης', 'google maps', 'google maps url', 'maps', 'map'],
        'website' => ['website', 'site', 'ιστοσελιδα'],
        'contact_person' => ['υπευθυνος', 'υπευθυνος επικοινωνιας', 'contact person'],
    ];

    $map = [];
    foreach ($headers as $index => $header) {
        foreach ($aliases as $key => $values) {
            if (in_array($header, array_map('spek_partner_import_normalize_header', $values), true)) {
                $map[$key] = $index;
                break;
            }
        }
    }
    return $map;
}

function spek_partner_import_map_row(array $row, array $map): array
{
    $data = [];
    foreach ($map as $key => $index) {
        $data[$key] = isset($row[$index]) ? trim((string) $row[$index]) : '';
    }

    foreach (['name', 'address', 'area', 'postcode', 'phone', 'region', 'email', 'maps_url', 'website', 'contact_person'] as $key) {
        if (!isset($data[$key])) {
            $data[$key] = '';
        }
    }

    // Accept older Google URLs missing https:// and misplaced in the street column.
    $street_maps_url = spek_partner_import_normalize_maps_url($data['address']);
    $data['maps_url'] = spek_partner_import_normalize_maps_url($data['maps_url']);
    if ($street_maps_url !== '') {
        if ($data['maps_url'] === '') {
            $data['maps_url'] = $street_maps_url;
        }
        $data['address'] = '';
    }

    $data['postcode'] = trim(preg_replace('/\s+/u', '', $data['postcode']));
    $data['email'] = sanitize_email($data['email']);

    return $data;
}

/** Accept only verified Google Maps URLs; normalize scheme-less Excel links. */
function spek_partner_import_normalize_maps_url(string $raw): string
{
    $raw = trim(html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    if ($raw === '') {
        return '';
    }
    if (preg_match('~^(?:(?:www\.)?google\.com/maps|maps\.google\.com/|maps\.app\.goo\.gl/)~i', $raw)) {
        $raw = 'https://' . $raw;
    }
    return spek_partner_import_is_google_maps_url($raw) ? esc_url_raw($raw) : '';
}

/** Explicit /maps/place/ URLs can point to a different business. */
function spek_partner_import_google_url_warning(string $url, string $business_name): string
{
    $path = (string) wp_parse_url($url, PHP_URL_PATH);
    if (!preg_match('~/(?:maps/)?place/([^/@?]+)~u', $path, $matches)) {
        return '';
    }
    $place_name = str_replace('+', ' ', rawurldecode($matches[1]));
    if (spek_partner_import_similarity($business_name, $place_name) < 45) {
        return sprintf(
            'Google Maps URL προς «%s» — διαφορετική επωνυμία από το Excel. Χρειάζεται επιβεβαίωση.',
            sanitize_text_field($place_name)
        );
    }
    return '';
}

function spek_partner_import_is_google_maps_url(string $value): bool
{
    if (!wp_http_validate_url($value)) {
        return false;
    }
    $host = strtolower((string) wp_parse_url($value, PHP_URL_HOST));
    return in_array($host, ['maps.app.goo.gl', 'maps.google.com', 'www.google.com', 'google.com'], true)
        && (strpos($value, '/maps') !== false || $host === 'maps.app.goo.gl');
}

/** Normalize comparison text. */
function spek_partner_import_normalize_text(string $value): string
{
    $value = spek_partner_import_fold_accents(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $value = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);
    return trim((string) preg_replace('/\s+/u', ' ', (string) $value));
}

function spek_partner_import_similarity(string $left, string $right): float
{
    $left = spek_partner_import_normalize_text($left);
    $right = spek_partner_import_normalize_text($right);
    if ($left === '' || $right === '') {
        return 0.0;
    }
    if ($left === $right) {
        return 100.0;
    }

    similar_text($left, $right, $char_score);
    $left_tokens = array_values(array_unique(array_filter(explode(' ', $left))));
    $right_tokens = array_values(array_unique(array_filter(explode(' ', $right))));
    $intersection = array_intersect($left_tokens, $right_tokens);
    $token_score = count($left_tokens) ? (count($intersection) / count($left_tokens)) * 100 : 0;

    return max((float) $char_score, (float) $token_score);
}

/** Build deterministic import identity for same branch/location. */
function spek_partner_import_key(array $row): string
{
    return hash('sha256', implode('|', [
        spek_partner_import_normalize_text((string) $row['name']),
        spek_partner_import_normalize_text((string) $row['address']),
        spek_partner_import_normalize_text((string) $row['area']),
        preg_replace('/\D+/', '', (string) $row['postcode']),
    ]));
}

/**
 * Match the complete shop identity, never just same name + postal code.
 * -1 means ambiguous existing duplicates; the importer must not guess.
 */
function spek_partner_import_find_existing(array $row): int
{
    $key = spek_partner_import_key($row);
    $ids = get_posts([
        'post_type' => 'spek_partner',
        'post_status' => 'any',
        'posts_per_page' => 3,
        'fields' => 'ids',
        'meta_key' => '_spek_partner_import_key',
        'meta_value' => $key,
        'no_found_rows' => true,
    ]);
    if (count($ids) > 1) {
        return -1;
    }
    if (count($ids) === 1) {
        return (int) $ids[0];
    }

    $candidates = get_posts([
        'post_type' => 'spek_partner',
        'post_status' => 'any',
        'posts_per_page' => 100,
        's' => (string) $row['name'],
        'no_found_rows' => true,
    ]);

    $target = [
        spek_partner_import_normalize_text((string) $row['name']),
        spek_partner_import_normalize_text((string) $row['address']),
        spek_partner_import_normalize_text((string) $row['area']),
        preg_replace('/\D+/', '', (string) $row['postcode']),
    ];
    $matches = [];
    foreach ($candidates as $candidate) {
        $existing = [
            spek_partner_import_normalize_text((string) $candidate->post_title),
            spek_partner_import_normalize_text((string) get_post_meta($candidate->ID, 'partner_address', true)),
            spek_partner_import_normalize_text((string) get_post_meta($candidate->ID, 'partner_area', true)),
            preg_replace('/\D+/', '', (string) get_post_meta($candidate->ID, 'partner_postcode', true)),
        ];
        if ($target === $existing) {
            $matches[] = (int) $candidate->ID;
        }
    }
    return count($matches) > 1 ? -1 : ($matches[0] ?? 0);
}

/** Parse phones, extracting one mobile while preserving all fixed-line numbers. */
function spek_partner_import_phones(string $raw): array
{
    $parts = preg_split('/\s*(?:\/|\||,|;|\n)\s*/u', trim($raw));
    $parts = array_values(array_filter(array_map('trim', is_array($parts) ? $parts : [])));
    $landlines = [];
    $mobile = '';

    foreach ($parts as $part) {
        $digits = preg_replace('/\D+/', '', $part);
        $local = preg_replace('/^(?:0030|30)/', '', $digits);
        if ($mobile === '' && preg_match('/^69\d{8}$/', $local)) {
            $mobile = $part;
        } else {
            $landlines[] = $part;
        }
    }

    return [
        'phone' => implode(' / ', $landlines),
        'mobile' => $mobile,
    ];
}

/** Google Places Text Search: identity only, never coordinates for the TomTom map. */
function spek_partner_import_google_match(array $row, int $threshold): array
{
    if (!defined('SPEK_GOOGLE_MAPS_API_KEY') || trim((string) SPEK_GOOGLE_MAPS_API_KEY) === '') {
        return ['status' => 'no_key', 'score' => null];
    }

    $query = trim(implode(', ', array_filter([
        $row['name'],
        $row['address'],
        $row['postcode'],
        $row['area'],
        $row['region'],
    ])));

    if ($query === '') {
        return ['status' => 'review', 'score' => 0];
    }

    $response = wp_remote_post('https://places.googleapis.com/v1/places:searchText', [
        'timeout' => 15,
        'headers' => [
            'Content-Type' => 'application/json',
            'X-Goog-Api-Key' => (string) SPEK_GOOGLE_MAPS_API_KEY,
            'X-Goog-FieldMask' => 'places.id,places.displayName.text,places.formattedAddress',
        ],
        'body' => wp_json_encode([
            'textQuery' => $query,
            'maxResultCount' => 5,
        ]),
    ]);

    if (is_wp_error($response)) {
        return ['status' => 'error', 'score' => null, 'note' => $response->get_error_message()];
    }

    $code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode((string) wp_remote_retrieve_body($response), true);
    if ($code < 200 || $code >= 300) {
        $message = isset($body['error']['message']) ? sanitize_text_field((string) $body['error']['message']) : 'Google Places HTTP ' . $code;
        return ['status' => 'error', 'score' => null, 'note' => $message];
    }

    $places = isset($body['places']) && is_array($body['places']) ? $body['places'] : [];
    if (!$places) {
        return ['status' => 'review', 'score' => 0, 'note' => __('Δεν βρέθηκε Google Places αποτέλεσμα.', 'spek-theme')];
    }

    $best = null;
    $best_score = -1;
    $target_postcode = preg_replace('/\D+/', '', (string) $row['postcode']);
    $target_area = spek_partner_import_normalize_text((string) $row['area']);

    foreach ($places as $rank => $place) {
        $place_id = isset($place['id']) ? (string) $place['id'] : '';
        $name = isset($place['displayName']['text']) ? (string) $place['displayName']['text'] : '';
        $address = isset($place['formattedAddress']) ? (string) $place['formattedAddress'] : '';
        if ($place_id === '') {
            continue;
        }

        $name_score = spek_partner_import_similarity((string) $row['name'], $name);
        $address_query = implode(' ', array_filter([$row['address'], $row['area'], $row['region']]));
        $address_score = spek_partner_import_similarity($address_query, $address);
        $score = ($name_score * 0.52) + ($address_score * 0.28);

        if ($target_postcode !== '' && strpos(preg_replace('/\D+/', '', $address), $target_postcode) !== false) {
            $score += 15;
        }
        if ($target_area !== '' && strpos(spek_partner_import_normalize_text($address), $target_area) !== false) {
            $score += 5;
        }
        if ($rank === 0) {
            $score += 2;
        }

        $score = (int) round(min(100, $score));
        if ($score > $best_score) {
            $best_score = $score;
            $best = [
                'place_id' => $place_id,
                'name' => $name,
                'address' => $address,
                'score' => $score,
                'name_score' => $name_score,
            ];
        }
    }

    if (!$best) {
        return ['status' => 'review', 'score' => 0];
    }

    // A high address score alone must not accept the wrong business.
    $best['status'] = $best['score'] >= $threshold && $best['name_score'] >= 55
        ? 'matched'
        : 'review';
    if ($best['status'] === 'review') {
        $best['note'] = 'Η επωνυμία/διεύθυνση Google δεν επιβεβαιώθηκε επαρκώς.';
    }

    // If both postcodes are present and conflict, hold for human review.
    $google_postcode = '';
    if (preg_match('/\\b[0-9]{5}\\b/', (string) $best['address'], $postal_matches)) {
        $google_postcode = $postal_matches[0];
    }
    if ($target_postcode !== '' && $google_postcode !== '' && $target_postcode !== $google_postcode) {
        $best['status'] = 'review';
        $best['note'] = 'Διαφορετικός ΤΚ μεταξύ Excel και Google Places.';
    }

    if ($best['status'] === 'matched') {
        $best['maps_url'] = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($query) . '&query_place_id=' . rawurlencode($best['place_id']);
    }

    return $best;
}

/** Follow a short Google Maps URL just enough to extract coordinates, if present. */
function spek_partner_import_resolve_google_maps_url(string $url): string
{
    if (!spek_partner_import_is_google_maps_url($url)) {
        return $url;
    }

    $current = $url;
    for ($i = 0; $i < 5; $i++) {
        $response = wp_remote_get($current, [
            'timeout' => 8,
            'redirection' => 0,
            'user-agent' => 'Mozilla/5.0 SPEK Partner Importer',
        ]);
        if (is_wp_error($response)) {
            break;
        }
        $code = (int) wp_remote_retrieve_response_code($response);
        $location = wp_remote_retrieve_header($response, 'location');
        if ($code >= 300 && $code < 400 && is_string($location) && $location !== '') {
            $next = html_entity_decode($location, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $host = strtolower((string) wp_parse_url($next, PHP_URL_HOST));
            $allowed = $host === 'maps.app.goo.gl' || $host === 'maps.google.com' || $host === 'www.google.com' || $host === 'google.com' || substr($host, -11) === '.google.com';
            if (!$allowed) {
                break;
            }
            $current = $next;
            continue;
        }
        break;
    }
    return $current;
}

/**
 * Greece has five-digit postal codes. An overseas result for such a shop is
 * almost certainly an ambiguous geocoding hit, NOT a valid store location.
 * Do not modify legitimately foreign partners with other postal formats.
 */
function spek_partner_import_coords_plausible(array $row, array $coords): bool
{
    $postcode = preg_replace('/\\D+/', '', (string) ($row['postcode'] ?? ''));
    if (!preg_match('/^[0-9]{5}$/', $postcode)) {
        return true;
    }
    if (!isset($coords['latitude'], $coords['longitude'])
        || !is_numeric($coords['latitude']) || !is_numeric($coords['longitude'])) {
        return false;
    }
    $lat = (float) $coords['latitude'];
    $lng = (float) $coords['longitude'];
    return $lat >= 34.2 && $lat <= 42.4 && $lng >= 19.0 && $lng <= 30.5;
}

/** Locator coordinates from supplied URL or TomTom. */
function spek_partner_import_locator_coords(array $row): array
{
    $maps_url = trim((string) $row['maps_url']);
    if ($maps_url !== '' && function_exists('spek_extract_lat_lng_from_maps_url')) {
        $resolved = spek_partner_import_resolve_google_maps_url($maps_url);
        $coords = spek_extract_lat_lng_from_maps_url($resolved);
        if (!empty($coords['latitude']) && !empty($coords['longitude'])) {
            if (!spek_partner_import_coords_plausible($row, $coords)) {
                return [
                    'error' => 'Ο Google Maps σύνδεσμος επέστρεψε συντεταγμένες εκτός Ελλάδας για ελληνικό ΤΚ.',
                    'source' => 'Google Maps URL – needs review',
                ];
            }
            return [
                'latitude' => (string) $coords['latitude'],
                'longitude' => (string) $coords['longitude'],
                'source' => 'Google Maps URL',
            ];
        }
    }

    if (!defined('SPEK_TOMTOM_API_KEY') || trim((string) SPEK_TOMTOM_API_KEY) === '') {
        return [];
    }

    $query = trim(implode(', ', array_filter([
        $row['address'],
        $row['postcode'],
        $row['area'],
        $row['region'],
        preg_match('/^[0-9]{5}$/', preg_replace('/\\D+/', '', (string) ($row['postcode'] ?? ''))) ? 'Ελλάδα' : '',
    ])));
    if ($query === '') {
        return [];
    }

    $endpoint = 'https://api.tomtom.com/maps/orbis/places/geocode?' . http_build_query([
        'query' => $query,
        'maxResults' => 1,
        'geopoliticalView' => 'Unified',
    ]);

    $response = wp_remote_get($endpoint, [
        'timeout' => 12,
        'headers' => [
            'TomTom-Api-Key' => (string) SPEK_TOMTOM_API_KEY,
            'TomTom-Api-Version' => '2',
            'Attributes' => 'results.position',
            'Accept' => 'application/json',
            'Accept-Language' => 'el-GR,el;q=0.9,en;q=0.8',
            'Referer' => home_url('/'),
            'Origin' => home_url('/'),
        ],
    ]);

    if (is_wp_error($response)) {
        return ['error' => $response->get_error_message(), 'source' => 'TomTom failed'];
    }

    $response_code = (int) wp_remote_retrieve_response_code($response);
    $body = json_decode((string) wp_remote_retrieve_body($response), true);

    if ($response_code !== 200) {
        $api_message = is_array($body)
            ? (string) ($body['detailedError']['message'] ?? $body['error']['message'] ?? $body['message'] ?? '')
            : '';
        return [
            'error' => trim('TomTom HTTP ' . $response_code . ($api_message !== '' ? ': ' . sanitize_text_field($api_message) : '')),
            'source' => 'TomTom failed',
        ];
    }

    $coords = $body['results'][0]['position']['coordinates'] ?? null;
    if (!is_array($coords) || count($coords) < 2) {
        return ['error' => 'TomTom returned no coordinates for ' . $query, 'source' => 'TomTom failed'];
    }

    $lng = (float) $coords[0];
    $lat = (float) $coords[1];
    if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
        return [];
    }
    if (!spek_partner_import_coords_plausible($row, [
        'latitude' => $lat, 'longitude' => $lng,
    ])) {
        return [
            'error' => 'Απορρίφθηκε geocoding εκτός Ελλάδας για ελληνικό ΤΚ. Διορθώστε τις συντεταγμένες από τον Έλεγχο Συνεργατών.',
            'source' => 'TomTom – needs review',
        ];
    }

    return [
        'latitude' => (string) $lat,
        'longitude' => (string) $lng,
        'source' => 'TomTom',
    ];
}

/** Import one row and record review metadata. */
function spek_partner_import_process_row(array $row, bool $dry_run, bool $update_existing, int $threshold, array &$stats, array &$review): void
{
    $source_row = (int) ($row['_source_row'] ?? 0);
    $name = sanitize_text_field((string) $row['name']);
    if ($name === '') {
        if (!$dry_run && function_exists('spek_partner_review_queue_issue')) {
            spek_partner_review_queue_issue($row, 'missing_name', 'Λείπει η επωνυμία του συνεργάτη.');
        }
        $stats['skipped']++;
        $stats['needs_review']++;
        $review[] = [
            'row' => $source_row, 'name' => 'Χωρίς επωνυμία',
            'action' => 'skip missing name', 'google_status' => 'review',
            'score' => null, 'note' => 'Λείπει η επωνυμία. Η εγγραφή στάλθηκε για έλεγχο.',
        ];
        spek_partner_import_error($stats, sprintf(__('Γραμμή %d: λείπει η επωνυμία.', 'spek-theme'), $source_row));
        return;
    }

    if (!empty($row['_duplicate_source_row'])) {
        if (!$dry_run && function_exists('spek_partner_review_queue_issue')) {
            spek_partner_review_queue_issue(
                $row,
                'duplicate',
                'Πιθανή διπλοεγγραφή: πρώτη εμφάνιση στη γραμμή ' . (int) $row['_duplicate_source_row']
            );
        }
        $stats['skipped']++;
        $stats['needs_review']++;
        $review[] = [
            'row' => $source_row, 'name' => $name, 'action' => 'skip duplicate',
            'google_status' => 'review', 'score' => null,
            'note' => 'Διπλή εγγραφή στο Excel (πρώτη εμφάνιση: γραμμή ' . (int) $row['_duplicate_source_row'] . ').',
        ];
        return;
    }

    $post_id = spek_partner_import_find_existing($row);
    if ($post_id === -1) {
        if (!$dry_run && function_exists('spek_partner_review_queue_issue')) {
            spek_partner_review_queue_issue(
                $row,
                'ambiguous',
                'Βρέθηκαν περισσότερες από μία υπάρχουσες εγγραφές με τα ίδια στοιχεία.'
            );
        }
        $stats['skipped']++;
        $stats['needs_review']++;
        $review[] = [
            'row' => $source_row, 'name' => $name, 'action' => 'skip ambiguous',
            'google_status' => 'review', 'score' => null,
            'note' => 'Πολλαπλές υπάρχουσες εγγραφές έχουν ίδια πλήρη ταυτότητα. Χρειάζεται χειροκίνητος έλεγχος.',
        ];
        return;
    }
    if ($post_id && !$update_existing) {
        $stats['skipped']++;
        $review[] = ['row' => $source_row, 'name' => $name, 'action' => 'skip existing', 'google_status' => '—', 'score' => null, 'note' => 'Υπάρχει ήδη και η ενημέρωση υπαρχόντων είναι απενεργοποιημένη.'];
        return;
    }

    $google = [];
    $maps_url = trim((string) $row['maps_url']);
    $suggested_maps_url = '';
    $existing_maps_url = $post_id ? (string) get_post_meta($post_id, 'partner_maps_url', true) : '';
    $existing_place_id = $post_id ? (string) get_post_meta($post_id, '_spek_partner_google_place_id', true) : '';

    if ($maps_url !== '') {
        // Never silently replace an already published Google link with a new
        // spreadsheet value. Show the new link in the review inbox first.
        if ($post_id && $existing_maps_url !== '' && $maps_url !== $existing_maps_url) {
            $suggested_maps_url = $maps_url;
            $maps_url = $existing_maps_url;
            $google = [
                'status' => 'review', 'score' => null, 'place_id' => '',
                'note' => 'Το νέο Google Maps URL διαφέρει από την υπάρχουσα τοποθεσία. Απαιτείται έγκριση.',
            ];
            $stats['needs_review']++;
        } else {
        // A WordPress admin may already have explicitly approved this URL.
        $url_warning = $post_id && $maps_url === $existing_maps_url
            ? ''
            : spek_partner_import_google_url_warning($maps_url, $name);
        if ($url_warning !== '') {
            // Do not overwrite an already verified link with a mismatched place.
            $suggested_maps_url = $maps_url;
            $maps_url = $existing_maps_url;
            $google = ['status' => 'review', 'score' => null, 'place_id' => '', 'note' => $url_warning];
            $stats['needs_review']++;
        } else {
            $google = ['status' => 'provided', 'score' => 100, 'maps_url' => $maps_url, 'name' => '', 'address' => '', 'place_id' => ''];
            $stats['google_matched']++;
        }
        }
    } elseif ($post_id && $existing_maps_url !== '') {
        // Reimports must keep a manually confirmed / previously saved link.
        $maps_url = $existing_maps_url;
        $google = [
            'status' => 'existing', 'score' => null, 'place_id' => $existing_place_id,
            'note' => 'Διατηρήθηκε το Google Maps link από το WordPress.',
        ];
        $stats['google_matched']++;
    } else {
        $google = spek_partner_import_google_match($row, $threshold);
        if (($google['status'] ?? '') === 'matched') {
            $maps_url = (string) $google['maps_url'];
            $stats['google_matched']++;
        } else {
            $stats['needs_review']++;
            // Save only a proposed Google link for human review, not a
            // confirmed place ID or location. Place IDs may be retained.
            if (!empty($google['place_id'])) {
                $suggested_query = (string) ($google['name'] ?? $name);
                $suggested_maps_url = 'https://www.google.com/maps/search/?api=1&query='
                    . rawurlencode($suggested_query)
                    . '&query_place_id=' . rawurlencode((string) $google['place_id']);
            }
        }
    }

    // Never replace previously maintained coordinates automatically.
    $existing_lat = $post_id ? trim((string) get_post_meta($post_id, 'partner_latitude', true)) : '';
    $existing_lng = $post_id ? trim((string) get_post_meta($post_id, 'partner_longitude', true)) : '';
    if ($existing_lat !== '' && $existing_lng !== '') {
        $coords = [
            'latitude' => $existing_lat, 'longitude' => $existing_lng,
            'source' => 'Υπάρχουσες συντεταγμένες (διατηρήθηκαν)',
        ];
    } else {
        // Google Places ID is saved independently. While the public map still
        // uses TomTom, never persist Google API coordinates into this layer.
        $coords_row = $row;
        $coords_row['maps_url'] = $maps_url;
        $coords = spek_partner_import_locator_coords($coords_row);
    }
    $bad_coords = !empty($coords['latitude']) && !empty($coords['longitude'])
        && !spek_partner_import_coords_plausible($row, $coords);
    if ($bad_coords) {
        // Existing coordinates are never erased by automatic reimport, but
        // are flagged for manual correction and excluded from the public map.
        $google['note'] = trim((string) ($google['note'] ?? '') . ' Συντεταγμένες εκτός Ελλάδας για ελληνικό ΤΚ.');
    }
    if ((!empty($coords['error']) || $bad_coords) && function_exists('spek_partner_import_error')) {
        $stats['needs_review']++;
        spek_partner_import_error($stats, 'Γραμμή ' . $source_row . ': ' .
            ($bad_coords ? 'Υπάρχον μη έγκυρο pin.' : (string) $coords['error']));
    }
    if (!empty($coords['latitude']) && !empty($coords['longitude']) && !$bad_coords) {
        $stats['geocoded']++;
    }

    // An identical suggestion already approved/rejected by an admin must
    // never be reopened on the next Excel import.
    if ($post_id && $suggested_maps_url !== '') {
        $previously_handled = [
            (string) get_post_meta($post_id, '_spek_partner_review_accepted_url', true),
            (string) get_post_meta($post_id, '_spek_partner_review_rejected_url', true),
        ];
        if (in_array($suggested_maps_url, $previously_handled, true)) {
            $suggested_maps_url = '';
        }
    }

    $phones = spek_partner_import_phones((string) $row['phone']);
    $meta = [
        'partner_address' => sanitize_text_field((string) $row['address']),
        'partner_area' => sanitize_text_field((string) $row['area']),
        'partner_postcode' => sanitize_text_field((string) $row['postcode']),
        'partner_phone' => sanitize_text_field((string) $phones['phone']),
        'partner_mobile' => sanitize_text_field((string) $phones['mobile']),
        'partner_email' => sanitize_email((string) $row['email']),
        'partner_website' => esc_url_raw((string) $row['website']),
        'partner_contact_person' => sanitize_text_field((string) $row['contact_person']),
        'partner_maps_url' => esc_url_raw($maps_url),
        'partner_latitude' => isset($coords['latitude']) ? sanitize_text_field((string) $coords['latitude']) : '',
        'partner_longitude' => isset($coords['longitude']) ? sanitize_text_field((string) $coords['longitude']) : '',
        '_spek_partner_import_key' => spek_partner_import_key($row),
        // A suggested/unverified Google result must never become a confirmed ID.
        '_spek_partner_google_place_id' => in_array((string) ($google['status'] ?? ''), ['matched', 'existing'], true)
            ? sanitize_text_field((string) ($google['place_id'] ?? ''))
            : '',
        '_spek_partner_suggested_maps_url' => esc_url_raw($suggested_maps_url),
        '_spek_partner_google_match_status' => sanitize_key((string) ($google['status'] ?? '')),
        '_spek_partner_google_match_score' => isset($google['score']) && $google['score'] !== null ? (string) (int) $google['score'] : '',
        '_spek_partner_google_candidate_name' => sanitize_text_field((string) ($google['name'] ?? '')),
        '_spek_partner_google_candidate_address' => sanitize_text_field((string) ($google['address'] ?? '')),
        '_spek_partner_google_match_note' => sanitize_text_field((string) ($google['note'] ?? '')),
        '_spek_partner_pin_needs_review' => ($bad_coords || !empty($coords['error'])) ? '1' : '',
        '_spek_partner_import_source_row' => (string) $source_row,
    ];

    $action = $post_id ? 'update' : 'create';
    $changed = !$post_id;

    if ($post_id) {
        if (get_the_title($post_id) !== $name) {
            $changed = true;
        }
        foreach ($meta as $key => $value) {
            if ($value !== '' && (string) get_post_meta($post_id, $key, true) !== (string) $value) {
                $changed = true;
                break;
            }
        }
    }

    if ($dry_run) {
        $stats[$post_id ? ($changed ? 'updated' : 'unchanged') : 'created']++;
    } else {
        $postarr = [
            'post_type' => 'spek_partner',
            'post_title' => $name,
            'post_status' => 'publish',
        ];
        if ($post_id) {
            $postarr['ID'] = $post_id;
        }

        $result = $post_id ? wp_update_post(wp_slash($postarr), true) : wp_insert_post(wp_slash($postarr), true);
        if (is_wp_error($result)) {
            $stats['skipped']++;
            spek_partner_import_error($stats, sprintf('Γραμμή %d (%s): %s', $source_row, $name, $result->get_error_message()));
            return;
        }
        $post_id = (int) $result;

        // Blank input cannot delete confirmed data. Coordinates are immutable
        // under reimport; changes to a pin must be made explicitly in WordPress.
        foreach ($meta as $key => $value) {
            if ($value === '') {
                continue;
            }
            if ($post_id && in_array($key, ['partner_latitude', 'partner_longitude'], true)) {
                $current = (string) get_post_meta($post_id, $key, true);
                if ($current !== '') {
                    continue;
                }
            }
            update_post_meta($post_id, $key, wp_slash($value));
        }
        update_post_meta($post_id, '_spek_partner_imported_at', current_time('mysql'));
        // A genuinely new uncertain recommendation must re-enter the review
        // inbox. Do not undo a human decision for an unchanged suggestion.
        $review_state = (string) get_post_meta($post_id, '_spek_partner_review_resolution', true);
        $import_status = (string) ($google['status'] ?? '');
        if ($suggested_maps_url !== '' || $bad_coords || !empty($coords['error'])
            || in_array($import_status, ['review', 'error', 'no_key'], true)) {
            if ($review_state === '' || ($suggested_maps_url !== ''
                && $suggested_maps_url !== $existing_maps_url)) {
                update_post_meta($post_id, '_spek_partner_review_resolution', 'pending');
            }
        }

        $region = trim((string) $row['region']);
        if ($region !== '' && !in_array(mb_strtolower($region, 'UTF-8'), ['unknown', 'n/a', '-', 'null'], true)) {
            $term = term_exists($region, 'partner_region');
            if (!$term) {
                $term = wp_insert_term(sanitize_text_field($region), 'partner_region');
            }
            if (!is_wp_error($term)) {
                $term_id = is_array($term) ? (int) $term['term_id'] : (int) $term;
                wp_set_object_terms($post_id, [$term_id], 'partner_region', false);
            }
        }

        $stats[$action === 'create' ? 'created' : ($changed ? 'updated' : 'unchanged')]++;
    }

    // Complete audit trail for uploads with 1,500+ partners.
    $review[] = [
            'row' => $source_row,
            'name' => $name,
            'action' => $action,
            'google_status' => (string) ($google['status'] ?? '—'),
            'score' => $google['score'] ?? null,
            'google_name' => (string) ($google['name'] ?? ''),
            'google_address' => (string) ($google['address'] ?? ''),
            'coords_source' => (string) ($coords['source'] ?? ''),
            'note' => trim(implode(' | ', array_filter([
                (string) ($google['note'] ?? ''),
                !empty($coords['error']) ? 'Locator: ' . (string) $coords['error'] : '',
            ]))),
        ];
}

function spek_partner_import_error(array &$stats, string $message): void
{
    $stats['errors_count'] = (int) ($stats['errors_count'] ?? 0) + 1;
    if (count($stats['errors'] ?? []) < 10000) {
        $stats['errors'][] = $message;
    }
}
