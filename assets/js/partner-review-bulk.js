/**
 * SPEK partner review: page-scoped bulk selection and explicit confirmation.
 * Checkboxes use form="spek-partner-bulk-form" because each review card
 * already contains its own independent inline edit form.
 */
(function () {
    'use strict';

    const form = document.getElementById('spek-partner-bulk-form');
    if (!form) {
        return;
    }

    const selectAll = form.querySelector('[data-spek-bulk-select-all]');
    const counter = form.querySelector('[data-spek-bulk-counter]');
    const actions = Array.from(form.querySelectorAll('[data-spek-bulk-action]'));
    const items = Array.from(document.querySelectorAll('[data-spek-bulk-item]'));

    function selectedCount() {
        return items.filter(function (item) { return item.checked; }).length;
    }

    function refresh() {
        const selected = selectedCount();
        if (counter) {
            counter.textContent = selected + ' από ' + items.length + ' επιλεγμένες';
        }
        actions.forEach(function (button) { button.disabled = selected < 1; });
        if (selectAll) {
            selectAll.checked = items.length > 0 && selected === items.length;
            selectAll.indeterminate = selected > 0 && selected < items.length;
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            items.forEach(function (item) { item.checked = selectAll.checked; });
            refresh();
        });
    }

    items.forEach(function (item) {
        item.addEventListener('change', refresh);
    });

    form.addEventListener('submit', function (event) {
        const count = selectedCount();
        if (!count) {
            event.preventDefault();
            return;
        }
        const submitter = event.submitter;
        const action = submitter && submitter.value === 'reject' ? 'reject' : 'approve';
        const message = action === 'reject'
            ? 'Να απορριφθούν οι ' + count + ' επιλεγμένες προτάσεις Google Maps; Οι συνεργάτες θα διατηρηθούν.'
            : 'Να εγκριθούν οι ' + count + ' επιλεγμένες προτάσεις Google Maps; Θα ενημερωθούν οι σύνδεσμοι Google Maps, όχι οι συντεταγμένες των pins.';
        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });

    refresh();
}());
