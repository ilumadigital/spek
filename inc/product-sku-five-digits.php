<?php
/**
 * Safe, standalone 5-digit SKU corrections for existing SPEK products.
 * Does not run the ordinary SKU-keyed product importer.
 *
 * @package SpekTheme
 */
if (!defined('ABSPATH')) { exit; }

add_action('admin_menu', 'spek_sku5_menu');
add_action('wp_ajax_spek_sku5_apply', 'spek_sku5_apply_ajax');

function spek_sku5_menu(): void
{
    add_submenu_page('edit.php?post_type=spek_product', 'Μετατροπή κωδικών σε 5 ψηφία',
        'Μετατροπή κωδικών σε 5 ψηφία', 'manage_options', 'spek-sku-five-digits', 'spek_sku5_page');
}

/** CSV values must remain strings throughout the entire workflow. */
function spek_sku5_read_csv(string $path): array
{
    $handle = @fopen($path, 'rb');
    if (!$handle) { throw new RuntimeException('Αδυναμία ανάγνωσης CSV.'); }
    try {
        $header = fgetcsv($handle, 0, ',', '"', '');
        if (!$header) { throw new RuntimeException('Το CSV είναι κενό.'); }
        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        $keys = ['id', 'old_sku', 'new_sku', 'reference_title'];
        if ($header !== $keys) { throw new RuntimeException('Απαιτούνται ακριβώς οι στήλες: ' . implode(', ', $keys)); }
        $rows = []; $ids = []; $old = []; $new = [];
        while (($values = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if ($values === [null]) { continue; }
            $line = count($rows) + 2;
            if (count($values) !== 4 || count($rows) >= 1000) {
                throw new RuntimeException('Λανθασμένες στήλες ή υπέρβαση 1000 γραμμών (γραμμή ' . $line . ').');
            }
            $row = array_combine($keys, array_map('trim', $values));
            if (!preg_match('/^[1-9][0-9]*$/D', $row['id']) || in_array((int) $row['id'], [27, 29], true)) {
                throw new RuntimeException('Μη επιτρεπτό WP ID στη γραμμή ' . $line . ' (τα ID 27/29 παραμένουν χωρίς SKU).');
            }
            if (!preg_match('/^[1-9][0-9]{0,3}$/D', $row['old_sku']) ||
                !preg_match('/^[0-9]{5}$/D', $row['new_sku']) ||
                str_pad($row['old_sku'], 5, '0', STR_PAD_LEFT) !== $row['new_sku']) {
                throw new RuntimeException('Μη έγκυρη μετατροπή old_sku → new_sku στη γραμμή ' . $line . '.');
            }
            if (isset($ids[$row['id']]) || isset($old[$row['old_sku']]) || isset($new[$row['new_sku']])) {
                throw new RuntimeException('Διπλό WP ID, old_sku ή new_sku στο CSV (γραμμή ' . $line . ').');
            }
            $ids[$row['id']] = true; $old[$row['old_sku']] = true; $new[$row['new_sku']] = true;
            $rows[] = $row;
        }
        if (!$rows) { throw new RuntimeException('Το CSV δεν περιλαμβάνει εγγραφές.'); }
        foreach ($rows as $row) {
            if (isset($old[$row['new_sku']]) || isset($new[$row['old_sku']])) {
                throw new RuntimeException('Σύγκρουση παλιού/νέου SKU μέσα στο CSV: ' . $row['old_sku']);
            }
        }
        return $rows;
    } finally { fclose($handle); }
}

/** Include trashed products and legacy SKU aliases when checking collisions. */
function spek_sku5_conflicting_ids(string $sku, int $expected_id): array
{
    global $wpdb;
    $sql = $wpdb->prepare(
        "SELECT DISTINCT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID
         WHERE p.post_type = %s AND m.meta_key IN ('product_code','_product_code','sku','_sku','product_sku')
         AND BINARY m.meta_value = BINARY %s AND p.ID <> %d LIMIT 10",
        'spek_product', $sku, $expected_id
    );
    $ids = $wpdb->get_col($sql);
    if (!is_array($ids)) { throw new RuntimeException('Δεν ήταν δυνατός ο έλεγχος μοναδικότητας στη βάση.'); }
    return array_map('intval', $ids);
}

/** Live WP check: ID + EXACT canonical code + both codes free across ALL other products. */
function spek_sku5_check(array $row): string
{
    $id = (int) $row['id'];
    if (get_post_type($id) !== 'spek_product' || get_post_status($id) === 'trash') {
        throw new RuntimeException('Το WP ID δεν ανήκει σε ενεργό spek_product.');
    }
    $meta = get_post_meta($id, 'product_code', false);
    if (!is_array($meta) || count($meta) !== 1 || !is_string($meta[0])) {
        throw new RuntimeException('Απαιτείται ακριβώς μία αποθηκευμένη τιμή product_code (string).');
    }
    $current = $meta[0];
    if ($current !== $row['old_sku'] && $current !== $row['new_sku']) {
        throw new RuntimeException('Το πραγματικό SKU «' . $current . '» διαφέρει από old_sku «' . $row['old_sku'] . '» και new_sku «' . $row['new_sku'] . '».');
    }
    foreach (['old_sku', 'new_sku'] as $key) {
        $conflicts = spek_sku5_conflicting_ids($row[$key], $id);
        if ($conflicts) {
            throw new RuntimeException('Το ' . $key . ' «' . $row[$key] . '» ανήκει και σε άλλο προϊόν (WP ID: ' . implode(', ', $conflicts) . ').');
        }
    }
    return $current === $row['new_sku'] ? 'applied' : 'pending';
}

/** Isolated session file, owned by one admin user; no product DB writes in preview. */
function spek_sku5_session_path(string $token): string
{
    $upload = wp_upload_dir();
    if (!empty($upload['error'])) { throw new RuntimeException((string) $upload['error']); }
    $dir = trailingslashit($upload['basedir']) . 'spek-imports';
    if (!wp_mkdir_p($dir)) { throw new RuntimeException('Αδυναμία δημιουργίας προσωρινού φακέλου.'); }
    if (!file_exists($dir . '/.htaccess')) { @file_put_contents($dir . '/.htaccess', "Deny from all\n"); }
    if (!file_exists($dir . '/index.php')) { @file_put_contents($dir . '/index.php', "<?php\n// Silence is golden.\n"); }
    return $dir . '/sku5-' . hash('sha256', $token) . '.json';
}

function spek_sku5_page(): void
{
    if (!current_user_can('manage_options')) { wp_die('Δεν έχετε δικαίωμα πρόσβασης.'); }
    echo '<div class="wrap"><h1>Μετατροπή κωδικών σε 5 ψηφία</h1>';
    echo '<p>Ξεχωριστή λειτουργία του importer: ενημερώνει <strong>μόνο</strong> το product_code υπαρχόντων προϊόντων μέσω WP ID. Δεν δημιουργεί προϊόντα, δεν χρησιμοποιεί τον κανονικό CSV importer και δεν τροποποιεί άλλο πεδίο.</p>';
    echo '<form method="post" enctype="multipart/form-data">';
    wp_nonce_field('spek_sku5_preview');
    echo '<input type="file" name="spek_sku5_csv" accept=".csv,text/csv" required> ';
    submit_button('Έλεγχος και προεπισκόπηση', 'secondary', 'spek_sku5_preview', false);
    echo '</form><p class="description">Ανεβάστε απευθείας το αρχικό UTF-8 SPEK_SKU_5digits_corrections.csv χωρίς επαναποθήκευση από το Excel.</p>';
    if (!isset($_POST['spek_sku5_preview'])) { echo '</div>'; return; }
    check_admin_referer('spek_sku5_preview');
    try {
        $file = $_FILES['spek_sku5_csv'] ?? [];
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
            strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION)) !== 'csv' ||
            (int) ($file['size'] ?? 0) < 1 || (int) ($file['size'] ?? 0) > 1024 * 1024 ||
            !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) {
            throw new RuntimeException('Ανεβάστε έγκυρο CSV έως 1 MB.');
        }
        $rows = spek_sku5_read_csv($file['tmp_name']);
        $errors = []; $pending = 0; $applied = 0; $table = '';
        foreach ($rows as $row) {
            try {
                $state = spek_sku5_check($row);
                if ($state === 'pending') { ++$pending; $status = 'Έτοιμο'; }
                else { ++$applied; $status = 'Ήδη εφαρμοσμένο'; }
            } catch (Throwable $e) {
                $status = 'ΣΦΑΛΜΑ: ' . $e->getMessage();
                $errors[] = 'WP ID ' . $row['id'] . ' / SKU ' . $row['old_sku'] . ': ' . $e->getMessage();
            }
            $table .= '<tr><td>' . esc_html($row['id']) . '</td><td><code>' . esc_html($row['old_sku']) .
                '</code></td><td><code>' . esc_html($row['new_sku']) . '</code></td><td>' .
                esc_html($row['reference_title']) . '</td><td>' . esc_html($status) . '</td></tr>';
        }
        echo '<h2>Προεπισκόπηση — ' . count($rows) . ' προϊόντα</h2>';
        echo '<p><strong>Για αλλαγή: ' . (int) $pending . ' · Ήδη ενημερωμένα: ' . (int) $applied . ' · Σφάλματα: ' . count($errors) . '</strong></p>';
        if ($errors) {
            echo '<div class="notice notice-error inline" style="padding:12px"><p><strong>Η εφαρμογή αποκλείστηκε. Καμία αλλαγή δεν έγινε στα προϊόντα.</strong></p><ul style="list-style:disc;padding-left:24px">';
            foreach ($errors as $error) { echo '<li>' . esc_html($error) . '</li>'; }
            echo '</ul></div>';
        }
        echo '<div style="overflow-x:auto"><table class="widefat striped"><thead><tr><th>WP ID</th><th>Τρέχον / old_sku</th><th>Νέο / new_sku</th><th>Τίτλος αναφοράς</th><th>Αποτέλεσμα</th></tr></thead><tbody>' . $table . '</tbody></table></div>';
        if (!$errors && $pending === 0) {
            echo '<div class="notice notice-success inline"><p>Όλοι οι κωδικοί έχουν ήδη ενημερωθεί.</p></div>';
        } elseif (!$errors) {
            $token = wp_generate_password(32, false, false);
            $state = ['uid' => get_current_user_id(), 'expires' => time() + HOUR_IN_SECONDS,
                'rows' => $rows, 'cursor' => 0, 'updated' => 0, 'unchanged' => 0, 'preflight' => false];
            if (!spek_product_import_write_state(spek_sku5_session_path($token), $state)) {
                throw new RuntimeException('Αδυναμία αποθήκευσης συνεδρίας.');
            }
            echo '<p><label><input id="spek-sku5-confirm" type="checkbox"> Επιβεβαιώνω ότι έλεγξα τις ' . (int) $pending . ' αλλαγές και θέλω να ενημερωθεί αποκλειστικά το product_code.</label></p>';
            echo '<p><button type="button" class="button button-primary" id="spek-sku5-apply" disabled>Εφαρμογή αλλαγών SKU</button> <span id="spek-sku5-progress" role="status"></span></p>';
            ?>
            <script>
            (() => {
                const checkbox = document.getElementById('spek-sku5-confirm');
                const button = document.getElementById('spek-sku5-apply');
                const progress = document.getElementById('spek-sku5-progress');
                checkbox.addEventListener('change', () => { button.disabled = !checkbox.checked; });
                button.addEventListener('click', async () => {
                    button.disabled = true;
                    checkbox.disabled = true;
                    try {
                        let done = false;
                        while (!done) {
                            const body = new URLSearchParams({action: 'spek_sku5_apply', confirmed: '1',
                                token: <?php echo wp_json_encode($token); ?>,
                                nonce: <?php echo wp_json_encode(wp_create_nonce('spek_sku5_apply')); ?>});
                            const response = await fetch(window.ajaxurl, {method: 'POST', credentials: 'same-origin', body});
                            const json = await response.json();
                            if (!json.success) { throw new Error(json.data?.message || 'Αποτυχία επεξεργασίας.'); }
                            done = !!json.data.done;
                            progress.textContent = json.data.cursor + '/' + json.data.total +
                                ' · Ενημερώθηκαν: ' + json.data.updated + ' · Ήδη σωστά: ' + json.data.unchanged +
                                (done ? ' · Ολοκληρώθηκε.' : ' · Επεξεργασία…');
                        }
                    } catch (e) {
                        progress.textContent = 'Διακοπή: ' + e.message + ' Επανεκτελέστε την προεπισκόπηση για ασφαλή συνέχεια.';
                    }
                });
            })();
            </script>
            <?php
        }
    } catch (Throwable $e) {
        echo '<div class="notice notice-error inline"><p>' . esc_html($e->getMessage()) . '</p></div>';
    }
    echo '</div>';
}

/** One existing product per AJAX request, protected by the normal importer's lock. */
function spek_sku5_apply_ajax(): void
{
    if (!current_user_can('manage_options')) { wp_send_json_error(['message' => 'Δεν έχετε δικαίωμα.'], 403); }
    check_ajax_referer('spek_sku5_apply', 'nonce');
    $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
    if (!preg_match('/^[A-Za-z0-9]{32}$/D', $token) || ($_POST['confirmed'] ?? '') !== '1') {
        wp_send_json_error(['message' => 'Μη έγκυρη ή μη επιβεβαιωμένη συνεδρία.'], 400);
    }
    try {
        $path = spek_sku5_session_path($token);
        $lock = @fopen(dirname($path) . '/batch.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            if ($lock) { fclose($lock); }
            throw new RuntimeException('Άλλη εισαγωγή εκτελείται. Δοκιμάστε ξανά.');
        }
        try {
            $state = spek_product_import_read_state($path);
            if (!is_array($state) || (int) ($state['uid'] ?? 0) !== get_current_user_id() ||
                time() > (int) ($state['expires'] ?? 0) || !isset($state['rows'], $state['cursor']) ||
                !is_array($state['rows'])) {
                throw new RuntimeException('Η συνεδρία έληξε. Ανεβάστε ξανά το CSV.');
            }
            $total = count($state['rows']);
            if (empty($state['preflight'])) {
                // First request: reject the whole file before changing a single product.
                foreach ($state['rows'] as $row) { spek_sku5_check($row); }
                $state['preflight'] = true;
            }
            if ((int) $state['cursor'] < $total) {
                $row = $state['rows'][(int) $state['cursor']];
                $status = spek_sku5_check($row); // Recheck immediately before write.
                if ($status === 'pending') {
                    $id = (int) $row['id'];
                    $updated = update_post_meta($id, 'product_code', (string) $row['new_sku']);
                    if ($updated === false || spek_sku5_check($row) !== 'applied') {
                        throw new RuntimeException('SKU ' . $row['old_sku'] . ': αποτυχία αποθήκευσης/επαλήθευσης.');
                    }
                    ++$state['updated'];
                } else { ++$state['unchanged']; }
                ++$state['cursor'];
            }
            if (!spek_product_import_write_state($path, $state)) {
                throw new RuntimeException('Αποτυχία αποθήκευσης προόδου. Επανεκτελέστε προεπισκόπηση.');
            }
            $result = ['total' => $total, 'cursor' => (int) $state['cursor'],
                'updated' => (int) $state['updated'], 'unchanged' => (int) $state['unchanged'],
                'done' => (int) $state['cursor'] >= $total];
        } finally {
            flock($lock, LOCK_UN); fclose($lock);
        }
        wp_send_json_success($result);
    } catch (Throwable $e) {
        wp_send_json_error(['message' => $e->getMessage()], 409);
    }
}
