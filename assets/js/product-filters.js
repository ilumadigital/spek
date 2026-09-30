(function () {
    'use strict';

    const panel = document.querySelector('[data-product-filters-panel]');
    const form = document.getElementById('product-filters-form');
    const search = document.getElementById('product_search');
    const scrollKey = 'spekProductFiltersScrollY';
    const panelKey = 'spekProductFiltersPanelOpen';
    let searchTimer = null;
    let isSubmitting = false;

    if (panel && typeof window.matchMedia === 'function') {
        const mobileQuery = window.matchMedia('(max-width: 780px)');

        function syncPanel(event) {
            const isMobile = typeof event.matches === 'boolean' ? event.matches : mobileQuery.matches;

            if (!isMobile) {
                panel.open = true;
                return;
            }

            let shouldOpen = false;
            try {
                shouldOpen = sessionStorage.getItem(panelKey) === '1';
            } catch (error) {
                shouldOpen = false;
            }

            panel.open = shouldOpen;
        }

        syncPanel(mobileQuery);

        if (typeof mobileQuery.addEventListener === 'function') {
            mobileQuery.addEventListener('change', syncPanel);
        } else if (typeof mobileQuery.addListener === 'function') {
            mobileQuery.addListener(syncPanel);
        }


        panel.addEventListener('toggle', () => {
            if (!mobileQuery.matches) {
                return;
            }

            try {
                sessionStorage.setItem(panelKey, panel.open ? '1' : '0');
            } catch (error) {
                // Ignore storage failures.
            }
        });
    }

    try {
        const savedScroll = sessionStorage.getItem(scrollKey);
        if (savedScroll !== null) {
            sessionStorage.removeItem(scrollKey);
            const y = Number(savedScroll);
            if (Number.isFinite(y)) {
                window.requestAnimationFrame(() => window.scrollTo(0, y));
            }
        }
    } catch (error) {
        // sessionStorage can be unavailable in strict privacy modes.
    }

    function rememberScroll() {
        try {
            sessionStorage.setItem(scrollKey, String(window.scrollY || 0));
        } catch (error) {
            // Ignore storage failures.
        }
    }

    function submitFilters() {
        if (!form || isSubmitting) {
            return;
        }

        isSubmitting = true;
        rememberScroll();
        form.classList.add('is-submitting');

        if (typeof form.requestSubmit === 'function') {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }

    if (form) {
        form.querySelectorAll('input[type="checkbox"]').forEach((input) => {
            input.addEventListener('change', submitFilters);
        });

        form.addEventListener('submit', rememberScroll);

        const clearLink = form.querySelector('.product-filters__clear');
        if (clearLink) {
            clearLink.addEventListener('click', () => {
                try {
                    sessionStorage.removeItem(panelKey);
                } catch (error) {
                    // Ignore storage failures.
                }
            });
        }
    }

    if (search) {
        search.addEventListener('input', () => {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(submitFilters, 450);
        });

        search.addEventListener('search', () => {
            window.clearTimeout(searchTimer);
            submitFilters();
        });

        search.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            window.clearTimeout(searchTimer);
            submitFilters();
        });
    }

    document.querySelectorAll('[data-product-sort], [data-product-per-page]').forEach((select) => {
        select.addEventListener('change', submitFilters);
    });
})();