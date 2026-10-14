/**
 * SPEK partner review: automatically advance resumable pin repair in wp-admin.
 * The server also schedules WP-Cron batches for visits without an admin.
 */
(function () {
    'use strict';
    const panel = document.querySelector('[data-spek-pin-repair]');
    const config = window.spekPartnerPinRepair || {};
    if (!panel || panel.dataset.status === 'complete'
        || !config.endpoint || !config.nonce) {
        return;
    }

    const label = panel.querySelector('[data-spek-pin-repair-label]');
    let attempts = 0;
    let lastCursor = -1;

    async function next() {
        try {
            const payload = new URLSearchParams({
                action: 'spek_partner_coord_repair_batch',
                nonce: config.nonce
            });
            const response = await fetch(config.endpoint, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
                body: payload.toString()
            });
            const result = await response.json();
            if (!response.ok || !result.success || !result.data) {
                throw new Error(result.data && result.data.message
                    ? result.data.message : 'Unable to update coordinate repair progress');
            }
            const state = result.data;
            if (label) {
                label.textContent = 'Ελέγχθηκαν ' + state.cursor + ' / ' + state.total
                    + ' συνεργάτες · διορθώθηκαν ' + state.fixed
                    + ' · συμπληρώθηκαν ' + state.filled
                    + ' κενά pins · προστατεύτηκαν ' + state.protected
                    + ' διαφορετικές/χειροκίνητες θέσεις.';
            }
            panel.dataset.status = state.status;
            if (state.status === 'complete') {
                panel.classList.remove('notice-info');
                panel.classList.add('notice-success');
                if (label) {
                    label.textContent += ' Η εφάπαξ διόρθωση ολοκληρώθηκε. Ανανέωσε τη σελίδα για νέους μετρητές.';
                }
                return;
            }
            attempts = state.cursor === lastCursor ? attempts + 1 : 0;
            lastCursor = state.cursor;
            // If cron has claimed the lock, back off for a moment.
            window.setTimeout(next, attempts ? 850 : 150);
        } catch (error) {
            if (label) {
                label.textContent = 'Η αυτόματη διόρθωση έχει προγραμματιστεί στο WordPress. '
                    + 'Αν δεν προχωρά, έλεγξε το WP-Cron ή ανανέωσε τη σελίδα. '
                    + (error.message || '');
            }
            // Cron still has the same resumable state if browser calls fail.
            if (window.console && window.console.warn) {
                window.console.warn('SPEK coordinate repair:', error);
            }
        }
    }

    next();
}());
