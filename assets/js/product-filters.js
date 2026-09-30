(function () {
    'use strict';

    const panel = document.querySelector('[data-product-filters-panel]');

    if (!panel || typeof window.matchMedia !== 'function') {
        return;
    }

    const mobileQuery = window.matchMedia('(max-width: 780px)');

    function syncPanel(event) {
        const isMobile = typeof event.matches === 'boolean' ? event.matches : mobileQuery.matches;
        panel.open = !isMobile;
    }

    syncPanel(mobileQuery);

    if (typeof mobileQuery.addEventListener === 'function') {
        mobileQuery.addEventListener('change', syncPanel);
    } else if (typeof mobileQuery.addListener === 'function') {
        mobileQuery.addListener(syncPanel);
    }

    panel.querySelectorAll('[data-filter-more]').forEach((button) => {
        const group = button.closest('[data-filter-group]');
        const label = button.querySelector('[data-filter-more-label]');

        if (!group || !label) {
            return;
        }

        const initialLabel = label.textContent.trim();
        const lessLabel = document.documentElement.lang && document.documentElement.lang.toLowerCase().startsWith('en')
            ? 'Less'
            : 'Λιγότερα';

        button.addEventListener('click', () => {
            const expanded = group.classList.toggle('is-expanded');
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            label.textContent = expanded ? lessLabel : initialLabel;
        });
    });
})();
