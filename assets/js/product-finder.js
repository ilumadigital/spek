(function () {
    'use strict';

    function spekText(text) {
        return (window.spekI18n && window.spekI18n[text]) || text;
    }

    const finder = document.querySelector('#spek-product-finder .spek-finder');

    if (!finder || typeof spekFinder === 'undefined') {
        return;
    }

    const panels = Array.from(finder.querySelectorAll('.spek-finder-panel'));
    const progressItems = Array.from(finder.querySelectorAll('[data-progress-step]'));
    const resultsWrap = finder.querySelector('.spek-finder-results');
    const resultsCount = finder.querySelector('.spek-finder-results-count');
    const loading = finder.querySelector('.spek-finder-loading');
    const workspace = finder.querySelector('.spek-finder-workspace');
    const mobileStepLabel = finder.querySelector('[data-mobile-step-label]');
    const mobileStepCount = finder.querySelector('[data-mobile-step-count]');
    const mobileProgressBar = finder.querySelector('[data-mobile-progress-bar]');

    const stepOrder = ['category', 'application', 'specs', 'results'];
    const stepLabels = {
        start: spekText('Έναρξη'),
        category: spekText('Κατηγορία'),
        application: spekText('Εφαρμογή'),
        specs: spekText('Χαρακτηριστικά'),
        results: spekText('Αποτελέσματα')
    };

    function currentStepIndex(step) {
        return stepOrder.indexOf(step);
    }

    function updateProgress(step) {
        const index = currentStepIndex(step);

        progressItems.forEach((item) => {
            const itemStep = item.getAttribute('data-progress-step');
            const itemIndex = currentStepIndex(itemStep);
            const isCurrent = itemStep === step;
            const isComplete = index > itemIndex || step === 'results';

            item.classList.toggle('is-current', isCurrent);
            item.classList.toggle('is-complete', isComplete && !isCurrent);

            if (isCurrent) {
                item.setAttribute('aria-current', 'step');
            } else {
                item.removeAttribute('aria-current');
            }
        });

        if (mobileStepLabel) {
            mobileStepLabel.textContent = stepLabels[step] || stepLabels.start;
        }

        if (mobileStepCount) {
            mobileStepCount.textContent = index >= 0 ? (index + 1) + ' / 4' : '0 / 4';
        }

        if (mobileProgressBar) {
            const value = index >= 0 ? ((index + 1) / 4) * 100 : 0;
            mobileProgressBar.style.width = value + '%';
        }
    }

    function showStep(step, options) {
        options = options || {};

        panels.forEach((panel) => {
            const active = panel.dataset.step === step;
            panel.classList.toggle('is-active', active);
            panel.setAttribute('aria-hidden', active ? 'false' : 'true');
        });

        finder.dataset.currentStep = step;
        updateProgress(step);
        updateSummary();
        updateRequiredButtons();

        if (options.scroll !== false) {
            const target = window.innerWidth <= 760 ? finder : workspace || finder;
            window.setTimeout(() => {
                target.scrollIntoView({
                    behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth',
                    block: 'start'
                });
            }, 30);
        }
    }

    function getValue(selector) {
        const field = finder.querySelector(selector);
        return field ? field.value.trim() : '';
    }

    function getLabel(name) {
        const checked = finder.querySelector('input[name="' + name + '"]:checked');
        return checked ? (checked.dataset.label || checked.value) : '';
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

    function selectedSpecsLabel() {
        const labels = [];
        const material = finder.querySelector('select[name="product_material"]');
        const series = finder.querySelector('select[name="product_series"]');
        const installation = getValue('input[name="product_installation_type"]');
        const search = getValue('input[name="product_search"]');

        if (material && material.value) {
            labels.push(material.options[material.selectedIndex].text);
        }
        if (series && series.value) {
            labels.push(series.options[series.selectedIndex].text);
        }
        if (installation) {
            labels.push(installation);
        }
        if (search) {
            labels.push(search);
        }

        return labels.length ? labels.join(' · ') : spekText('Χωρίς επιπλέον φίλτρα');
    }

    function updateSummary() {
        const category = getLabel('product_category');
        const application = getLabel('product_application');
        const specs = selectedSpecsLabel();

        const categorySummary = finder.querySelector('[data-summary-category]');
        const applicationSummary = finder.querySelector('[data-summary-application]');
        const specsSummary = finder.querySelector('[data-summary-specs]');

        if (categorySummary) {
            categorySummary.textContent = category || spekText('Δεν έχει επιλεγεί');
        }
        if (applicationSummary) {
            applicationSummary.textContent = application || spekText('Δεν έχει επιλεγεί');
        }
        if (specsSummary) {
            specsSummary.textContent = specs;
        }

        const resultCategory = finder.querySelector('[data-result-summary-category]');
        const resultApplication = finder.querySelector('[data-result-summary-application]');
        const resultSpecs = finder.querySelector('[data-result-summary-specs]');

        if (resultCategory) {
            resultCategory.textContent = category ? spekText('Κατηγορία') + ': ' + category : '';
            resultCategory.hidden = !category;
        }
        if (resultApplication) {
            resultApplication.textContent = application ? spekText('Εφαρμογή') + ': ' + application : '';
            resultApplication.hidden = !application;
        }
        if (resultSpecs) {
            resultSpecs.textContent = specs;
            resultSpecs.hidden = false;
        }
    }

    function updateRequiredButtons() {
        finder.querySelectorAll('[data-requires-selection]').forEach((button) => {
            const name = button.getAttribute('data-requires-selection');
            button.disabled = !finder.querySelector('input[name="' + name + '"]:checked');
        });
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
        updateSummary();
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
            const response = await fetch(
                spekFinder.ajaxUrl + (spekFinder.ajaxUrl.includes('?') ? '&' : '?') + 'lang=' + encodeURIComponent(spekFinder.lang),
                {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                }
            );

            const payload = await response.json();

            if (!payload.success) {
                throw new Error(payload.data && payload.data.message ? payload.data.message : 'Request failed');
            }

            if (resultsWrap) {
                resultsWrap.innerHTML = payload.data.html;
                Array.from(resultsWrap.children).forEach((item, index) => {
                    item.style.setProperty('--finder-result-delay', (index * 55) + 'ms');
                    item.classList.add('is-finder-result');
                });
            }

            if (resultsCount) {
                resultsCount.textContent = payload.data.countText;
            }
        } catch (error) {
            if (resultsWrap) {
                resultsWrap.innerHTML = '<div class="empty-state spek-finder-no-results"><h2></h2><p></p></div>';
                resultsWrap.querySelector('h2').textContent = spekText('Κάτι πήγε στραβά');
                resultsWrap.querySelector('p').textContent = spekText('Δοκιμάστε ξανά ή αλλάξτε τα κριτήρια αναζήτησης.');
            }
        } finally {
            if (loading) {
                loading.hidden = true;
            }
        }
    }

    finder.addEventListener('change', (event) => {
        if (
            event.target.matches('input[name="product_category"]') ||
            event.target.matches('input[name="product_application"]') ||
            event.target.matches('select[name="product_material"]') ||
            event.target.matches('select[name="product_series"]')
        ) {
            updateSummary();
            updateRequiredButtons();
        }
    });

    finder.addEventListener('input', (event) => {
        if (
            event.target.matches('input[name="product_installation_type"]') ||
            event.target.matches('input[name="product_search"]')
        ) {
            updateSummary();
        }
    });

    finder.addEventListener('click', (event) => {
        const nextButton = event.target.closest('.spek-finder-next');
        const prevButton = event.target.closest('.spek-finder-prev');
        const submitButton = event.target.closest('.spek-finder-submit');
        const restartButton = event.target.closest('.spek-finder-restart');

        if (nextButton && !nextButton.disabled) {
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
            updateSummary();
            updateRequiredButtons();
            showStep('category');
        }
    });

    updateSummary();
    updateRequiredButtons();
    updateProgress('start');
})();
