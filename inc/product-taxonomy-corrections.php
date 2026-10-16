<?php
/**
 * Reviewed, taxonomy-only corrections for existing SPEK products.
 * CSV upload -> read-only preview -> explicit, revalidated AJAX application.
 * The regular product CSV importer is unaffected.
 *
 * @package SpekTheme
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'spek_tc_register_menu');
add_action('wp_ajax_spek_tc_apply', 'spek_tc_ajax_apply');

function spek_tc_register_menu(): void
{
    add_submenu_page(
        'edit.php?post_type=spek_product',
        'Διορθώσεις σειρών / κατηγοριών',
        'Διορθώσεις σειρών / κατηγοριών',
        'manage_options',
        'spek-taxonomy-corrections',
        'spek_tc_render_page'
    );
}

/** Only assigned term IDs matter; compare sets, not order. */
function spek_tc_sorted_ids(array $ids): array
{
    $ids = array_values(array_unique(array_map('intval', $ids)));
    sort($ids, SORT_NUMERIC);
    return $ids;
}

/** Never create terms, and reject missing or ambiguous term names. */
function spek_tc_resolve_terms(string $names, string $taxonomy): array
{
    if (!taxonomy_exists($taxonomy)) {
        throw new RuntimeException('Η ταξινομία δεν υπάρχει: ' . $taxonomy);
    }
    $ids = [];
    foreach (explode('|', $names) as $name) {
        $name = trim($name);
        if ($name === '') {
            throw new RuntimeException('Κενό όνομα όρου στο ' . $taxonomy);
        }
        $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false, 'name' => $name]);
        if (is_wp_error($terms)) {
            throw new RuntimeException($terms->get_error_message());
        }
        $exact = array_values(array_filter($terms, static function ($term) use ($name) {
            return $term->name === $name;
        }));
        if (count($exact) !== 1) {
            throw new RuntimeException('Απουσιάζει ή δεν είναι μοναδικός ο όρος «' . $name . '» στο ' . $taxonomy);
        }
        $ids[] = (int) $exact[0]->term_id;
    }
    if (count($ids) !== count(array_unique($ids))) {
        throw new RuntimeException('Ο ίδιος όρος υπάρχει δύο φορές στο ' . $taxonomy);
    }
    return spek_tc_sorted_ids($ids);
}

function spek_tc_current_terms(int $id, string $taxonomy): array
{
    $ids = wp_get_object_terms($id, $taxonomy, ['fields' => 'ids']);
    if (is_wp_error($ids)) {
        throw new RuntimeException($ids->get_error_message());
    }
    return spek_tc_sorted_ids($ids);
}

function spek_tc_term_names(array $ids, string $taxonomy): string
{
    $names = [];
    foreach ($ids as $id) {
        $term = get_term((int) $id, $taxonomy);
        if (!$term || is_wp_error($term)) {
            throw new RuntimeException('Δεν είναι δυνατή η ανάγνωση όρου ' . $id);
        }
        $names[] = $term->name;
    }
    return $names ? implode(' | ', $names) : '—';
}

/** Read the actual WP record for diagnostics, even when preconditions reject it. */
function spek_tc_live_snapshot(int $id): array
{
    $snapshot = ['sku' => '—', 'series' => 'Μη διαθέσιμο', 'categories' => 'Μη διαθέσιμο'];
    if (get_post_type($id) !== 'spek_product') {
        return $snapshot;
    }
    $snapshot['sku'] = (string) get_post_meta($id, 'product_code', true);
    foreach (['series' => 'product_series', 'categories' => 'product_category'] as $field => $taxonomy) {
        try {
            $snapshot[$field] = spek_tc_term_names(spek_tc_current_terms($id, $taxonomy), $taxonomy);
        } catch (Throwable $e) {
            $snapshot[$field] = 'Αδυναμία ανάγνωσης: ' . $e->getMessage();
        }
    }
    return $snapshot;
}

/** Strict CSV parsing: UTF-8 BOM, exact schema, distinct ID/SKU, max 1000 rows. */
function spek_tc_read_csv(string $path): array
{
    $fh = @fopen($path, 'rb');
    if (!$fh) {
        throw new RuntimeException('Το CSV δεν μπορεί να διαβαστεί.');
    }
    try {
        $headers = fgetcsv($fh, 0, ',', '"', '');
        if (!$headers) {
            throw new RuntimeException('Το CSV είναι κενό.');
        }
        $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        $required = ['id', 'sku', 'reference_title', 'expected_series', 'series', 'expected_categories', 'categories'];
        if ($headers !== $required) {
            throw new RuntimeException('Λανθασμένες στήλες CSV. Απαιτούνται: ' . implode(', ', $required));
        }
        $rows = [];
        $ids = [];
        $skus = [];
        while (($values = fgetcsv($fh, 0, ',', '"', '')) !== false) {
            if ($values === [null]) {
                continue;
            }
            if (count($values) !== 7 || count($rows) >= 1000) {
                throw new RuntimeException('Μη έγκυρη γραμμή CSV ή υπέρβαση των 1000 προϊόντων.');
            }
            $row = array_combine($required, array_map('trim', $values));
            if (!ctype_digit($row['id']) || (int) $row['id'] < 1 || $row['sku'] === '') {
                throw new RuntimeException('Μη έγκυρο ID/SKU στη γραμμή ' . (count($rows) + 2));
            }
            if (isset($ids[(int) $row['id']]) || isset($skus[$row['sku']])) {
                throw new RuntimeException('Διπλό ID/SKU μέσα στο CSV: ' . $row['id'] . ' / ' . $row['sku']);
            }
            $ids[(int) $row['id']] = true;
            $skus[$row['sku']] = true;
            $rows[] = $row;
        }
        if (!$rows) {
            throw new RuntimeException('Δεν βρέθηκαν προϊόντα στο CSV.');
        }
        return $rows;
    } finally {
        fclose($fh);
    }
}

/** Exact, case-sensitive SKU across all spek_product statuses, including trash. */
function spek_tc_assert_product(array $row): int
{
    global $wpdb;
    $id = (int) $row['id'];
    if (get_post_type($id) !== 'spek_product' || get_post_status($id) === 'trash' ||
        (string) get_post_meta($id, 'product_code', true) !== $row['sku']) {
        throw new RuntimeException('Το WP ID δεν ανήκει σε ενεργό προϊόν με ακριβώς αυτό το SKU.');
    }
    $sql = $wpdb->prepare(
        "SELECT DISTINCT p.ID FROM {$wpdb->posts} p INNER JOIN {$wpdb->postmeta} m ON m.post_id = p.ID WHERE p.post_type = %s AND m.meta_key = %s AND BINARY m.meta_value = BINARY %s LIMIT 2",
        'spek_product', 'product_code', $row['sku']
    );
    $matches = $wpdb->get_col($sql);
    if (!is_array($matches) || count($matches) !== 1 || (int) $matches[0] !== $id) {
        throw new RuntimeException('Το SKU δεν είναι μοναδικό ή δεν αντιστοιχεί στο WP ID.');
    }
    return $id;
}

/** Build a plan using LIVE taxonomy values; nothing here writes to WP. */
function spek_tc_plan(array $row): array
{
    $id = spek_tc_assert_product($row);
    $plan = [];
    foreach (['series' => 'product_series', 'categories' => 'product_category'] as $field => $taxonomy) {
        $target = $row[$field];
        // A blank destination always means 'do not touch', even if expected_* is provided.
        if ($target === '') {
            continue;
        }
        $expected = $row['expected_' . $field];
        if ($expected === '' || ($field === 'series' && strpos($target, '|') !== false)) {
            throw new RuntimeException('Απαιτείται expected_' . $field . ' και μία μοναδική νέα σειρά.');
        }
        $old = spek_tc_resolve_terms($expected, $taxonomy);
        $new = spek_tc_resolve_terms($target, $taxonomy);
        $current = spek_tc_current_terms($id, $taxonomy);
        if ($current !== $old && $current !== $new) {
            throw new RuntimeException('Ασυμφωνία ' . $taxonomy . ': πραγματικό «' .
                spek_tc_term_names($current, $taxonomy) . '», αναμενόμενο «' . $expected . '», νέο «' . $target . '».');
        }
        $plan[$taxonomy] = ['current' => $current, 'target' => $new];
    }
    if (!$plan) {
        throw new RuntimeException('Η γραμμή δεν ζητά αλλαγή σε καμία ταξινομία.');
    }
    return $plan;
}

function spek_tc_has_changes(array $plan): bool
{
    foreach ($plan as $part) {
        if ($part['current'] !== $part['target']) {
            return true;
        }
    }
    return false;
}

/** Reuse the existing importer's protected directory, but not its import sessions. */
function spek_tc_session_path(string $token): string
{
    $upload = wp_upload_dir();
    if (!empty($upload['error'])) {
        throw new RuntimeException((string) $upload['error']);
    }
    $dir = trailingslashit($upload['basedir']) . 'spek-imports';
    if (!wp_mkdir_p($dir)) {
        throw new RuntimeException('Δεν δημιουργείται ο φάκελος εισαγωγής.');
    }
    // WordPress/PHP web server should also block direct access to this directory.
    if (!file_exists($dir . '/.htaccess')) {
        @file_put_contents($dir . '/.htaccess', "Deny from all\n");
    }
    if (!file_exists($dir . '/index.php')) {
        @file_put_contents($dir . '/index.php', "<?php\n// Silence is golden.\n");
    }
    return $dir . '/taxonomy-' . hash('sha256', $token) . '.json';
}

/** Previewing does not write to any WordPress tables (only a private JSON session). */
function spek_tc_render_page(): void
{
    if (!current_user_can('manage_options')) {
        wp_die('Δεν έχετε δικαίωμα.');
    }
    echo '<div class="wrap"><h1>Διορθώσεις σειρών / κατηγοριών</h1>';
    echo '<p>Ξεχωριστή λειτουργία του SPEK importer για υπάρχοντα προϊόντα. Αλλάζει <strong>μόνο</strong> product_series και product_category. Δεν αλλάζει κείμενα, κωδικούς, εικόνες ή άλλα δεδομένα.</p>';
    echo '<form method="post" enctype="multipart/form-data">';
    wp_nonce_field('spek_tc_preview');
    echo '<input type="file" name="spek_tc_csv" accept=".csv,text/csv" required> ';
    submit_button('Έλεγχος και προεπισκόπηση', 'secondary', 'spek_tc_preview', false);
    echo '</form>';
    if (!isset($_POST['spek_tc_preview'])) {
        echo '</div>';
        return;
    }
    check_admin_referer('spek_tc_preview');
    try {
        $file = $_FILES['spek_tc_csv'] ?? [];
        $name = isset($file['name']) ? (string) $file['name'] : '';
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK ||
            strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'csv' ||
            (int) ($file['size'] ?? 0) < 1 || (int) ($file['size'] ?? 0) > 5 * 1024 * 1024 ||
            !is_uploaded_file($file['tmp_name'] ?? '')) {
            throw new RuntimeException('Ανεβάστε έγκυρο αρχείο .csv έως 5 MB.');
        }
        $rows = spek_tc_read_csv($file['tmp_name']);
        $errors = 0;
        $pending = 0;
        $blockers = [];
        echo '<h2>Προεπισκόπηση — ' . count($rows) . ' προϊόντα</h2>';
        // Buffer the table so validation blockers can be shown ABOVE 189+ preview rows.
        ob_start();
        echo '<div style="overflow-x:auto"><table class="widefat striped" style="min-width:1080px"><thead><tr><th>WP ID / SKU</th><th>Τίτλος αναφοράς</th><th>Σειρά (τώρα → μετά)</th><th>Κατηγορίες (τώρα → μετά)</th><th>Αποτέλεσμα</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            $display = [];
            try {
                $plan = spek_tc_plan($row);
                $needs_change = spek_tc_has_changes($plan);
                $pending += (int) $needs_change;
                $status = $needs_change ? 'Έτοιμο' : 'Ήδη διορθωμένο';
                foreach (['series' => 'product_series', 'categories' => 'product_category'] as $field => $tax) {
                    $current = isset($plan[$tax]) ? spek_tc_term_names($plan[$tax]['current'], $tax) : spek_tc_term_names(spek_tc_current_terms((int) $row['id'], $tax), $tax);
                    $display[$field] = $current . ($row[$field] === '' ? ' (χωρίς αλλαγή)' : ' → ' . str_replace('|', ' | ', $row[$field]));
                }
            } catch (Throwable $e) {
                ++$errors;
                $snapshot = spek_tc_live_snapshot((int) $row['id']);
                $blockers[] = ['row' => $row, 'reason' => $e->getMessage(), 'actual' => $snapshot];
                $status = 'ΣΦΑΛΜΑ: ' . $e->getMessage();
                foreach (['series', 'categories'] as $field) {
                    $display[$field] = 'WP: ' . $snapshot[$field] . ' · CSV αναμένει: ' .
                        ($row['expected_' . $field] === '' ? '—' : $row['expected_' . $field]) .
                        ($row[$field] === '' ? ' (χωρίς αλλαγή)' : ' → ' . $row[$field]);
                }
            }
            $row_has_error = isset($snapshot);
            echo '<tr id="spek-tc-row-' . esc_attr($row['id']) . '"' . ($row_has_error ? ' style="background:#fff0ef"' : '') . '><td>' .
                esc_html($row['id'] . ' / ' . $row['sku']) . '</td><td>' . esc_html($row['reference_title']) .
                '</td><td>' . esc_html($display['series']) . '</td><td>' . esc_html($display['categories']) .
                '</td><td style="overflow-wrap:anywhere;min-width:180px">' . esc_html($status) . '</td></tr>';
            unset($snapshot);
        }
        echo '</tbody></table></div>';
        $preview_table = ob_get_clean();
        if ($errors) {
            echo '<div class="notice notice-error inline" style="padding:10px 16px;max-width:1100px" id="spek-tc-errors">';
            echo '<p><strong>Η εφαρμογή αποκλείστηκε: ' . (int) $errors . ' σφάλμα(τα).</strong> Κανένα προϊόν δεν ενημερώθηκε.</p>';
            echo '<p>Παρακάτω εμφανίζονται οι πραγματικές τιμές WordPress, οι αναμενόμενες τιμές CSV και ο λόγος απόρριψης. Δεν γίνεται αυτόματη παράκαμψη των ελέγχων.</p><ul style="list-style:disc;padding-left:22px">';
            foreach ($blockers as $problem) {
                $bad = $problem['row'];
                $live = $problem['actual'];
                echo '<li style="margin-bottom:12px"><strong><a href="#spek-tc-row-' . esc_attr($bad['id']) . '">WP ID ' . esc_html($bad['id']) . ' / SKU ' . esc_html($bad['sku']) . '</a></strong> — ' . esc_html($problem['reason']);
                echo '<div>SKU στη βάση: <code>' . esc_html($live['sku']) . '</code></div>';
                foreach (['series' => 'Σειρά', 'categories' => 'Κατηγορίες'] as $field => $label) {
                    echo '<div><strong>' . esc_html($label) . ':</strong> τώρα <code>' . esc_html($live[$field]) . '</code> · CSV αναμένει <code>' . esc_html($bad['expected_' . $field] === '' ? '—' : $bad['expected_' . $field]) . '</code> · CSV ζητά <code>' . esc_html($bad[$field] === '' ? 'χωρίς αλλαγή' : $bad[$field]) . '</code></div>';
                }
                echo '</li>';
            }
            echo '</ul><p>Επιβεβαιώστε την ασυμφωνία στο WordPress πριν διορθωθεί η εγγραφή. Μην αλλάξετε το εγκεκριμένο CSV ή τις ταξινομίες μόνο για να παρακαμφθεί ο έλεγχος.</p></div>';
        }
        echo $preview_table; // Escaped table cells, generated entirely above.
        if ($errors) {
        } elseif ($pending === 0) {
            echo '<div class="notice notice-success inline"><p>Όλες οι διορθώσεις έχουν ήδη εφαρμοστεί.</p></div>';
        } else {
            $token = wp_generate_password(32, false, false);
            $path = spek_tc_session_path($token);
            $state = ['user_id' => get_current_user_id(), 'expires' => time() + HOUR_IN_SECONDS,
                'rows' => $rows, 'cursor' => 0, 'updated' => 0, 'unchanged' => 0, 'preflight' => false];
            if (!spek_product_import_write_state($path, $state)) {
                throw new RuntimeException('Αποτυχία αποθήκευσης της προεπισκόπησης.');
            }
            echo '<p><strong>Έτοιμα για αλλαγή: ' . (int) $pending . '. Ήδη διορθωμένα: ' . (count($rows) - $pending) . '.</strong></p>';
            echo '<p>Η εφαρμογή γίνεται με ξεχωριστά αιτήματα και ελέγχει ξανά τα δεδομένα πριν από κάθε αλλαγή.</p>';
            echo '<button type="button" class="button button-primary" id="spek-tc-apply">Εφαρμογή εγκεκριμένων διορθώσεων</button> <span id="spek-tc-progress" role="status"></span>';
            ?>
            <script>
            (() => {
                const button = document.getElementById('spek-tc-apply');
                const progress = document.getElementById('spek-tc-progress');
                button.addEventListener('click', async () => {
                    button.disabled = true;
                    progress.textContent = 'Επαλήθευση και εφαρμογή...';
                    try {
                        let done = false;
                        while (!done) {
                            const body = new URLSearchParams({action: 'spek_tc_apply',
                                nonce: <?php echo wp_json_encode(wp_create_nonce('spek_tc_apply')); ?>,
                                token: <?php echo wp_json_encode($token); ?>});
                            const response = await fetch(window.ajaxurl, {method: 'POST', credentials: 'same-origin', body});
                            const result = await response.json();
                            if (!result.success) throw new Error(result.data?.message || 'Αποτυχία εφαρμογής.');
                            done = result.data.done;
                            progress.textContent = result.data.cursor + '/' + result.data.total + ' · Αλλαγές: ' + result.data.updated + ' · Ήδη σωστά: ' + result.data.unchanged + (done ? ' · Ολοκληρώθηκε.' : '');
                        }
                    } catch (error) {
                        progress.textContent = 'Διακοπή: ' + error.message + ' Μπορείτε να ξανακάνετε προεπισκόπηση του ίδιου CSV και να συνεχίσετε με ασφάλεια.';
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

/** Replace all assigned terms for only the declared taxonomies; rollback this row on failure. */
function spek_tc_apply_row(array $row, array $plan): bool
{
    if (!spek_tc_has_changes($plan)) {
        return false;
    }
    $id = (int) $row['id'];
    $touched = [];
    try {
        foreach ($plan as $taxonomy => $change) {
            if ($change['current'] === $change['target']) {
                continue;
            }
            $touched[$taxonomy] = $change['current'];
            $result = wp_set_object_terms($id, $change['target'], $taxonomy, false);
            if (is_wp_error($result) || spek_tc_current_terms($id, $taxonomy) !== $change['target']) {
                throw new RuntimeException('Δεν αποθηκεύτηκε/επαληθεύτηκε η ταξινομία ' . $taxonomy);
            }
        }
        return true;
    } catch (Throwable $e) {
        $rollback_failed = false;
        foreach ($touched as $taxonomy => $original) {
            $restored = wp_set_object_terms($id, $original, $taxonomy, false);
            if (is_wp_error($restored)) {
                $rollback_failed = true;
                continue;
            }
            try {
                if (spek_tc_current_terms($id, $taxonomy) !== $original) {
                    $rollback_failed = true;
                }
            } catch (Throwable $ignored) {
                $rollback_failed = true;
            }
        }
        throw new RuntimeException('SKU ' . $row['sku'] . ': ' . $e->getMessage() .
            ($rollback_failed ? ' · ΠΡΟΣΟΧΗ: απέτυχε η επαναφορά της γραμμής.' : ' · Η γραμμή επανήλθε.'));
    }
}

/** Exactly one product per request, serialized with the regular importer. */
function spek_tc_ajax_apply(): void
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Δεν έχετε δικαίωμα.'], 403);
    }
    check_ajax_referer('spek_tc_apply', 'nonce');
    $token = isset($_POST['token']) ? sanitize_text_field(wp_unslash($_POST['token'])) : '';
    if (!preg_match('/^[A-Za-z0-9]{32}$/D', $token)) {
        wp_send_json_error(['message' => 'Μη έγκυρη συνεδρία.'], 400);
    }
    try {
        $path = spek_tc_session_path($token);
        // Share the regular importer's lock to avoid simultaneous writes.
        $lock = @fopen(dirname($path) . '/batch.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            if ($lock) fclose($lock);
            throw new RuntimeException('Άλλη εισαγωγή εκτελείται. Δοκιμάστε ξανά.');
        }
        try {
            $state = spek_product_import_read_state($path);
            if (!$state || (int) ($state['user_id'] ?? 0) !== get_current_user_id() ||
                time() > (int) ($state['expires'] ?? 0) || !isset($state['rows'], $state['cursor'])) {
                throw new RuntimeException('Η προεπισκόπηση έληξε ή δεν είναι δική σας. Ανεβάστε ξανά το CSV.');
            }
            $total = count($state['rows']);
            if (!$state['preflight']) {
                // Abort without product writes if any approved row changed after preview.
                foreach ($state['rows'] as $row) {
                    spek_tc_plan($row);
                }
                $state['preflight'] = true;
            }
            if ($state['cursor'] < $total) {
                $row = $state['rows'][$state['cursor']];
                // Check current data and expected_* AGAIN immediately before this write.
                $plan = spek_tc_plan($row);
                if (spek_tc_apply_row($row, $plan)) {
                    ++$state['updated'];
                } else {
                    ++$state['unchanged'];
                }
                ++$state['cursor'];
            }
            if (!spek_product_import_write_state($path, $state)) {
                throw new RuntimeException('Αποτυχία αποθήκευσης προόδου. Επανελέγξτε πριν συνεχίσετε.');
            }
            $result = ['cursor' => $state['cursor'], 'total' => $total,
                'updated' => $state['updated'], 'unchanged' => $state['unchanged'],
                'done' => $state['cursor'] >= $total];
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
        wp_send_json_success($result);
    } catch (Throwable $e) {
        wp_send_json_error(['message' => $e->getMessage()], 409);
    }
}
