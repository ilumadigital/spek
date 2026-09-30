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
})();
