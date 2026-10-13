/**
 * SPEK partner review: choose outstanding issues, recheck Google Places,
 * and separately approve/reject genuine map URL proposals.
 * The checkboxes are associated with the independent bulk form.
 */
(function () {
    'use strict';

    const form = document.getElementById('spek-partner-bulk-form');
    if (!form) { return; }

    const selectAll = form.querySelector('[data-spek-bulk-select-all]');
    const counter = form.querySelector('[data-spek-bulk-counter]');
    const actionButtons = Array.from(form.querySelectorAll('[data-spek-bulk-action]'));
    const recheckButton = form.querySelector('[data-spek-bulk-recheck]');
    const items = Array.from(document.querySelectorAll('[data-spek-bulk-item]'));
    const config = window.spekPartnerReviewConfig || {};
    let busy = false;

    function selected() {
        return items.filter(function (item) { return item.checked; });
    }

    function proposalsChosen() {
        return selected().filter(function (item) {
            return item.dataset.spekBulkProposal === '1';
        });
    }

    function approvableChosen() {
        return selected().filter(function (item) {
            return item.dataset.spekBulkApprovable === '1';
        });
    }

    function refresh() {
        const chosen = selected();
        const proposals = proposalsChosen();
        const approvable = approvableChosen();
        if (counter) {
            counter.textContent = chosen.length + ' από ' + items.length
                + ' επιλεγμένες · ' + proposals.length + ' προτάσεις'
                + ' · ' + approvable.length + ' προς μαζική έγκριση';
        }
        actionButtons.forEach(function (button) {
            button.disabled = busy || (button.value === 'approve'
                ? approvable.length === 0
                : proposals.length === 0);
        });
        if (recheckButton) {
            recheckButton.disabled = busy || chosen.length === 0 || !config.ajaxUrl || !config.nonce;
        }
        if (selectAll) {
            selectAll.checked = items.length > 0 && chosen.length === items.length;
            selectAll.indeterminate = chosen.length > 0 && chosen.length < items.length;
            selectAll.disabled = busy || items.length === 0;
        }
        items.forEach(function (item) { item.disabled = busy; });
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            items.forEach(function (item) { item.checked = selectAll.checked; });
            refresh();
        });
    }
    items.forEach(function (item) { item.addEventListener('change', refresh); });

    form.addEventListener('submit', function (event) {
        const submitter = event.submitter;
        const action = submitter && submitter.value === 'reject' ? 'reject' : 'approve';
        const applicable = action === 'approve' ? approvableChosen() : proposalsChosen();
        if (!applicable.length || busy) {
            event.preventDefault();
            return;
        }
        const count = applicable.length;
        const ignored = selected().length - count;
        const note = ignored ? '\nΟι ' + ignored + ' εγγραφές χωρίς έγκυρη/αρκετά αξιόπιστη πρόταση δεν θα αλλάξουν.' : '';
        const message = action === 'reject'
            ? 'Να απορριφθούν ' + count + ' επιλεγμένες προτάσεις Google Maps; Οι συνεργάτες παραμένουν καταχωρισμένοι.' + note
            : 'Να εγκριθούν ' + count + ' επιλεγμένες προτάσεις Google Maps; Τα pins και οι διευθύνσεις στο WordPress δεν αλλάζουν αυτόματα.' + note;
        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });

    async function recheckOne(id) {
        const body = new URLSearchParams({
            action: 'spek_partner_review_recheck',
            partner_id: String(id),
            nonce: config.nonce
        });
        const response = await fetch(config.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'},
            body: body.toString()
        });
        const result = await response.json();
        if (!response.ok || !result.success) {
            const message = result.data && result.data.message
                ? result.data.message
                : 'Απέτυχε το αίτημα Google Places.';
            const error = new Error(message);
            error.isFatal = response.status === 401 || response.status === 403 || response.status === 503;
            throw error;
        }
        return result.data || {};
    }

    if (recheckButton) {
        recheckButton.addEventListener('click', async function () {
            const chosen = selected();
            if (!chosen.length || busy || !config.ajaxUrl || !config.nonce) { return; }
            const count = chosen.length;
            if (!window.confirm('Να γίνει επανέλεγχος Google Places για ' + count
                + ' επιλεγμένους συνεργάτες; Θα γίνουν έως ' + count
                + ' χρεώσιμα API requests, ανάλογα με τα δωρεάν όρια. Δεν θα αλλαχθούν διευθύνσεις ή pins.')) {
                return;
            }

            busy = true;
            refresh();
            const counts = {proposed: 0, linked: 0, none: 0, handled: 0, errors: 0};
            let firstError = '';
            try {
                for (let i = 0; i < chosen.length; i++) {
                    if (counter) {
                        counter.textContent = 'Επανέλεγχος Google: ' + (i + 1) + ' / ' + count;
                    }
                    try {
                        const result = await recheckOne(chosen[i].value);
                        if (Object.prototype.hasOwnProperty.call(counts, result.result)) {
                            counts[result.result]++;
                        } else {
                            counts.none++;
                        }
                    } catch (error) {
                        counts.errors++;
                        if (!firstError) { firstError = error.message || String(error); }
                        if (error.isFatal) { break; }
                    }
                }
            } finally {
                busy = false;
                refresh();
            }
            window.alert(
                'Ο επανέλεγχος ολοκληρώθηκε.\n'
                + 'Νέες προτάσεις: ' + counts.proposed + '\n'
                + 'Ήδη συνδεδεμένα: ' + counts.linked + '\n'
                + 'Χωρίς νέα πρόταση: ' + (counts.none + counts.handled) + '\n'
                + 'Σφάλματα: ' + counts.errors
                + (firstError ? '\n' + firstError : '')
                + '\nΟι προτάσεις απαιτούν έγκριση και οι συντεταγμένες παραμένουν ανεξάρτητη εκκρεμότητα.'
            );
            if (counts.proposed > 0 || counts.linked > 0) {
                window.location.reload();
            }
        });
    }
    refresh();
}());
