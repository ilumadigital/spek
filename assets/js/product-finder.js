(function () {
    function spekText(text) { return (window.spekI18n && window.spekI18n[text]) || text; }
    const finder = document.querySelector('#spek-product-finder .spek-finder');

    if (!finder || typeof spekFinder === 'undefined') {
        return;
    }

    const panels = finder.querySelectorAll('.spek-finder-panel');
    const progressItems = finder.querySelectorAll('.spek-finder__progress span');
    const resultsWrap = finder.querySelector('.spek-finder-results');
    const resultsCount = finder.querySelector('.spek-finder-results-count');
    const loading = finder.querySelector('.spek-finder-loading');

    const stepIndexes = {
        start: 0,
        category: 1,
        application: 2,
        specs: 3,
        results: 4
    };

    function showStep(step) {
        panels.forEach((panel) => {
            panel.classList.toggle('is-active', panel.dataset.step === step);
        });

        finder.dataset.currentStep = step;

        progressItems.forEach((item, index) => {
            item.classList.toggle('is-active', index <= (stepIndexes[step] || 0));
        });

        finder.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function getValue(selector) {
        const field = finder.querySelector(selector);
        return field ? field.value.trim() : '';
    }

    function getFinderData() {
        const checkedCategory = finder.querySelector('input[name="product_category"]:checked');
        const checkedApplication = finder.querySelector('input[name="product_application"]:checked');

        return {
            action: 'spek_product_finder',
            nonce: spekFinder.nonce,
            lang: spekFinder.lang,
            product_category: checkedCategory ? checkedCategory.value : '',
            product_application: checkedApplication ? checkedApplication.value : '',
            product_material: getValue('select[name="product_material"]'),
            product_series: getValue('select[name="product_series"]'),
            product_installation_type: getValue('input[name="product_installation_type"]'),
            product_search: getValue('input[name="product_search"]')
        };
    }

    function clearResults() {
        if (resultsWrap) {
            resultsWrap.innerHTML = '';
        }

        if (resultsCount) {
            resultsCount.textContent = '';
        }
    }

    async function fetchResults() {
        clearResults();
        showStep('results');

        if (loading) {
            loading.hidden = false;
        }

        const formData = new FormData();
        const data = getFinderData();

        Object.keys(data).forEach((key) => {
            formData.append(key, data[key]);
        });

        try {
            const response = await fetch(spekFinder.ajaxUrl + (spekFinder.ajaxUrl.includes('?') ? '&' : '?') + 'lang=' + encodeURIComponent(spekFinder.lang), {
                method: 'POST',
                credentials: 'same-origin',
                body: formData
            });

            const payload = await response.json();

            if (!payload.success) {
                throw new Error(payload.data && payload.data.message ? payload.data.message : 'Request failed');
            }

            if (resultsWrap) {
                resultsWrap.innerHTML = payload.data.html;
            }

            if (resultsCount) {
                resultsCount.textContent = payload.data.countText;
            }
        } catch (error) {
            if (resultsWrap) {
                resultsWrap.innerHTML = '<div class="empty-state"><h2></h2><p></p></div>';
                resultsWrap.querySelector('h2').textContent = spekText('Κάτι πήγε στραβά');
                resultsWrap.querySelector('p').textContent = spekText('Δοκιμάστε ξανά ή αλλάξτε τα κριτήρια αναζήτησης.');
            }
        } finally {
            if (loading) {
                loading.hidden = true;
            }
        }
    }

    finder.addEventListener('click', (event) => {
        const nextButton = event.target.closest('.spek-finder-next');
        const prevButton = event.target.closest('.spek-finder-prev');
        const submitButton = event.target.closest('.spek-finder-submit');
        const restartButton = event.target.closest('.spek-finder-restart');

        if (nextButton) {
            showStep(nextButton.dataset.nextStep);
        }

        if (prevButton) {
            showStep(prevButton.dataset.prevStep);
        }

        if (submitButton) {
            fetchResults();
        }

        if (restartButton) {
            finder.querySelectorAll('input[type="radio"]').forEach((input) => {
                input.checked = false;
            });
            finder.querySelectorAll('input[type="text"], input[type="search"]').forEach((input) => {
                input.value = '';
            });
            finder.querySelectorAll('select').forEach((select) => {
                select.selectedIndex = 0;
            });
            clearResults();
            showStep('category');
        }
    });
})();