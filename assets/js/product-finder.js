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
    const applicationOptions = finder.querySelector('[data-application-options]');
    const applicationLoading = finder.querySelector('[data-application-loading]');
    const applicationEmpty = finder.querySelector('[data-application-empty]');
    const applicationNext = finder.querySelector('[data-application-next]');
    const defaultApplicationMode = finder.querySelector('[data-application-mode="default"]');
    const seatCoverMode = finder.querySelector('[data-application-mode="seat-cover"]');
    const coverMeasurements = finder.querySelector('[data-cover-measurements]');
    const coverNoDimensionsNote = finder.querySelector('[data-cover-no-dimensions-note]');
    const specsLoading = finder.querySelector('[data-specs-loading]');
    const specsEmpty = finder.querySelector('[data-specs-empty]');
    const specsNote = finder.querySelector('[data-specs-note]');
    const specsGrid = finder.querySelector('[data-specs-grid]');
    const progressApplicationLabel = finder.querySelector('[data-progress-label-application]');
    const heroApplicationLabel = document.querySelector('[data-hero-label-application]');

    const stepOrder = ['category', 'application', 'specs', 'results'];
    const stepLabels = {
        start: spekText('Έναρξη'),
        category: spekText('Κατηγορία'),
        application: spekText('Εφαρμογή'),
        specs: spekText('Χαρακτηριστικά'),
        results: spekText('Αποτελέσματα')
    };

    let applicationOptionCount = 0;
    let facetsLoading = false;
    let facetRequestId = 0;
    let dimensionRefreshTimer = null;

    function currentStepIndex(step) {
        return stepOrder.indexOf(step);
    }

    function selectedCategoryInput() {
        return finder.querySelector('input[name="product_category"]:checked');
    }

    function isSeatCoverSelected() {
        const selected = selectedCategoryInput();
        return !!selected && (selected.dataset.seatCover === '1' || selected.value === 'kalymmata-lekanis');
    }

    function coverDimensionsKnown() {
        const checked = finder.querySelector('input[name="cover_dimensions_known"]:checked');
        return checked ? checked.value : '';
    }

    function getNumericValue(selector) {
        const field = finder.querySelector(selector);
        if (!field || field.value.trim() === '') {
            return '';
        }

        const value = Number.parseFloat(field.value.replace(',', '.'));
        return Number.isFinite(value) ? String(value) : '';
    }

    function hasValidCoverDimensions() {
        if (!isSeatCoverSelected()) {
            return true;
        }

        const known = coverDimensionsKnown();
        if (known === 'no') {
            return true;
        }
        if (known !== 'yes') {
            return false;
        }

        const length = Number.parseFloat(getNumericValue('input[name="cover_length"]'));
        const width = Number.parseFloat(getNumericValue('input[name="cover_width"]'));
        return Number.isFinite(length) && length > 0 && Number.isFinite(width) && width > 0;
    }

    function updateBranchLabels() {
        const seatCover = isSeatCoverSelected();
        const label = seatCover ? spekText('Διαστάσεις') : spekText('Εφαρμογή');

        stepLabels.application = label;

        if (progressApplicationLabel) {
            progressApplicationLabel.textContent = label;
        }
        if (heroApplicationLabel) {
            heroApplicationLabel.textContent = seatCover ? spekText('Διαστάσεις') : spekText('Εφαρμογή');
        }

        if (defaultApplicationMode) {
            defaultApplicationMode.hidden = seatCover;
        }
        if (seatCoverMode) {
            seatCoverMode.hidden = !seatCover;
        }

        updateCoverMeasurementsVisibility();
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

    function getSelectLabel(name) {
        const select = finder.querySelector('select[name="' + name + '"]');
        if (!select || !select.value || select.selectedIndex < 0) {
            return '';
        }
        return select.options[select.selectedIndex].text;
    }

    function getFinderData() {
        const checkedCategory = finder.querySelector('input[name="product_category"]:checked');
        const checkedApplication = finder.querySelector('input[name="product_application"]:checked');
        const knownDimensions = coverDimensionsKnown();

        return {
            action: 'spek_product_finder',
            nonce: spekFinder.nonce,
            lang: spekFinder.lang,
            product_category: checkedCategory ? checkedCategory.value : '',
            product_application: isSeatCoverSelected() ? '' : (checkedApplication ? checkedApplication.value : ''),
            product_material: getValue('select[name="product_material"]'),
            product_series: getValue('select[name="product_series"]'),
            product_installation_type: getValue('select[name="product_installation_type"]'),
            product_search: getValue('input[name="product_search"]'),
            cover_dimensions_known: isSeatCoverSelected() ? knownDimensions : '',
            cover_length: isSeatCoverSelected() && knownDimensions === 'yes' ? getNumericValue('input[name="cover_length"]') : '',
            cover_width: isSeatCoverSelected() && knownDimensions === 'yes' ? getNumericValue('input[name="cover_width"]') : ''
        };
    }

    function getFacetData() {
        const data = getFinderData();
        data.action = 'spek_product_finder_facets';
        delete data.product_search;
        return data;
    }

    function selectedSpecsLabel() {
        const labels = [];
        const material = getSelectLabel('product_material');
        const series = getSelectLabel('product_series');
        const installation = getSelectLabel('product_installation_type');
        const search = getValue('input[name="product_search"]');

        if (material) {
            labels.push(material);
        }
        if (series) {
            labels.push(series);
        }
        if (installation) {
            labels.push(installation);
        }
        if (search) {
            labels.push(search);
        }

        return labels.length ? labels.join(' · ') : spekText('Χωρίς επιπλέον φίλτρα');
    }

    function applicationSummaryLabel() {
        if (!isSeatCoverSelected()) {
            const selectedApplication = getLabel('product_application');
            if (selectedApplication) {
                return selectedApplication;
            }

            return applicationOptionCount === 0 ? spekText('Δεν απαιτείται') : '';
        }

        const known = coverDimensionsKnown();
        if (known === 'no') {
            return spekText('Χωρίς διαστάσεις');
        }
        if (known === 'yes') {
            const length = getNumericValue('input[name="cover_length"]');
            const width = getNumericValue('input[name="cover_width"]');
            if (length && width) {
                return length + ' × ' + width + ' cm';
            }
            return spekText('Αναμονή διαστάσεων');
        }

        return '';
    }

    function updateSummary() {
        const category = getLabel('product_category');
        const application = applicationSummaryLabel();
        const specs = selectedSpecsLabel();
        const seatCover = isSeatCoverSelected();

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
            const prefix = seatCover ? spekText('Διαστάσεις') : spekText('Εφαρμογή');
            resultApplication.textContent = application ? prefix + ': ' + application : '';
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
            button.disabled = facetsLoading || !finder.querySelector('input[name="' + name + '"]:checked');
        });

        finder.querySelectorAll('.spek-finder-submit').forEach((button) => {
            button.disabled = facetsLoading;
        });

        if (applicationNext) {
            if (isSeatCoverSelected()) {
                applicationNext.disabled = facetsLoading || !hasValidCoverDimensions();
            } else {
                const hasApplication = !!finder.querySelector('input[name="product_application"]:checked');
                applicationNext.disabled = facetsLoading || (applicationOptionCount > 0 && !hasApplication);
            }
        }
    }

    function updateCoverMeasurementsVisibility() {
        const known = coverDimensionsKnown();
        const seatCover = isSeatCoverSelected();

        if (coverMeasurements) {
            coverMeasurements.hidden = !seatCover || known !== 'yes';
        }
        if (coverNoDimensionsNote) {
            coverNoDimensionsNote.hidden = !seatCover || known !== 'no';
        }
    }

    function clearResults() {
        if (resultsWrap) {
            resultsWrap.innerHTML = '';
        }

        if (resultsCount) {
            resultsCount.textContent = '';
        }
    }

    function resetSelect(select) {
        if (!select) {
            return;
        }
        select.selectedIndex = 0;
    }

    function resetApplicationSelection() {
        finder.querySelectorAll('input[name="product_application"], input[name="cover_dimensions_known"]').forEach((input) => {
            input.checked = false;
        });
        finder.querySelectorAll('input[name="cover_length"], input[name="cover_width"]').forEach((input) => {
            input.value = '';
        });
        updateCoverMeasurementsVisibility();
    }

    function resetSpecsSelection() {
        resetSelect(finder.querySelector('select[name="product_material"]'));
        resetSelect(finder.querySelector('select[name="product_series"]'));
        resetSelect(finder.querySelector('select[name="product_installation_type"]'));
    }

    function renderApplications(options) {
        if (!applicationOptions) {
            return;
        }

        const checkedApplication = finder.querySelector('input[name="product_application"]:checked');
        const selected = checkedApplication ? checkedApplication.value : '';
        applicationOptions.innerHTML = '';
        applicationOptionCount = Array.isArray(options) ? options.length : 0;

        (options || []).forEach((option, index) => {
            const label = document.createElement('label');
            label.className = 'spek-finder-chip';

            const input = document.createElement('input');
            input.type = 'radio';
            input.name = 'product_application';
            input.value = option.value;
            input.dataset.label = option.label;
            input.checked = selected === option.value;

            const shell = document.createElement('span');
            const number = document.createElement('i');
            const title = document.createElement('strong');
            const check = document.createElement('b');

            number.textContent = String(index + 1).padStart(2, '0');
            title.textContent = option.label;
            check.textContent = '✓';
            check.setAttribute('aria-hidden', 'true');

            shell.append(number, title, check);
            label.append(input, shell);
            applicationOptions.append(label);
        });

        if (applicationEmpty) {
            applicationEmpty.hidden = applicationOptionCount !== 0 || isSeatCoverSelected();
        }
    }

    function renderSelectOptions(name, options, placeholder) {
        const select = finder.querySelector('select[name="' + name + '"]');
        if (!select) {
            return;
        }

        const current = select.value;
        select.innerHTML = '';

        const empty = document.createElement('option');
        empty.value = '';
        empty.textContent = placeholder;
        select.append(empty);

        let currentExists = false;
        (options || []).forEach((option) => {
            const item = document.createElement('option');
            item.value = option.value;
            item.textContent = option.label;
            if (option.value === current) {
                currentExists = true;
            }
            select.append(item);
        });

        select.value = currentExists ? current : '';

        const key = name === 'product_material'
            ? 'material'
            : (name === 'product_series' ? 'series' : 'installation');
        const field = finder.querySelector('[data-facet-field="' + key + '"]');
        if (field) {
            field.hidden = !options || options.length === 0;
        }
    }

    function renderSpecs(payload) {
        renderSelectOptions('product_material', payload.materials || [], spekText('Όλα τα διαθέσιμα υλικά'));
        renderSelectOptions('product_series', payload.series || [], spekText('Όλες οι διαθέσιμες σειρές'));
        renderSelectOptions('product_installation_type', payload.installationTypes || [], spekText('Όλοι οι διαθέσιμοι τύποι'));

        const visibleFacetFields = Array.from(finder.querySelectorAll('[data-facet-field]')).filter((field) => !field.hidden);
        const noFacets = visibleFacetFields.length === 0;

        if (specsEmpty) {
            specsEmpty.hidden = !noFacets;
        }
        if (specsNote) {
            specsNote.hidden = noFacets;
        }
        if (specsGrid) {
            specsGrid.classList.toggle('spek-finder-form-grid--search-only', noFacets);
        }
    }

    function setFacetsLoading(state) {
        facetsLoading = state;
        finder.classList.toggle('is-facets-loading', state);

        if (applicationLoading) {
            applicationLoading.hidden = !state || isSeatCoverSelected();
        }
        if (specsLoading) {
            specsLoading.hidden = !state;
        }

        updateRequiredButtons();
    }

    async function refreshFacets(options) {
        options = options || {};

        if (!selectedCategoryInput()) {
            return null;
        }

        if (options.resetApplication) {
            resetApplicationSelection();
            if (applicationOptions) {
                applicationOptions.innerHTML = '';
            }
            applicationOptionCount = 0;
        }
        if (options.resetSpecs) {
            resetSpecsSelection();
        }

        const requestId = ++facetRequestId;
        setFacetsLoading(true);

        const formData = new FormData();
        const data = getFacetData();
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

            if (requestId !== facetRequestId) {
                return null;
            }
            if (!payload.success) {
                throw new Error(payload.data && payload.data.message ? payload.data.message : 'Facet request failed');
            }

            renderApplications(payload.data.applications || []);
            renderSpecs(payload.data);
            updateBranchLabels();
            updateSummary();
            updateRequiredButtons();
            return payload.data;
        } catch (error) {
            if (requestId === facetRequestId) {
                renderApplications([]);
                renderSpecs({materials: [], series: [], installationTypes: []});
            }
            return null;
        } finally {
            if (requestId === facetRequestId) {
                setFacetsLoading(false);
                updateSummary();
            }
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
        if (event.target.matches('input[name="product_category"]')) {
            updateBranchLabels();
            updateSummary();
            refreshFacets({resetApplication: true, resetSpecs: true});
            return;
        }

        if (event.target.matches('input[name="product_application"]')) {
            updateSummary();
            updateRequiredButtons();
            refreshFacets({resetSpecs: true});
            return;
        }

        if (event.target.matches('input[name="cover_dimensions_known"]')) {
            updateCoverMeasurementsVisibility();
            updateSummary();
            updateRequiredButtons();
            refreshFacets({resetSpecs: true});
            return;
        }

        if (
            event.target.matches('select[name="product_material"]') ||
            event.target.matches('select[name="product_series"]') ||
            event.target.matches('select[name="product_installation_type"]')
        ) {
            updateSummary();
            updateRequiredButtons();
            refreshFacets();
        }
    });

    finder.addEventListener('input', (event) => {
        if (event.target.matches('input[name="cover_length"], input[name="cover_width"]')) {
            updateSummary();
            updateRequiredButtons();
            window.clearTimeout(dimensionRefreshTimer);
            if (hasValidCoverDimensions()) {
                dimensionRefreshTimer = window.setTimeout(() => {
                    refreshFacets({resetSpecs: true});
                }, 350);
            }
            return;
        }

        if (event.target.matches('input[name="product_search"]')) {
            updateSummary();
        }
    });

    finder.addEventListener('click', (event) => {
        const nextButton = event.target.closest('.spek-finder-next');
        const prevButton = event.target.closest('.spek-finder-prev');
        const submitButton = event.target.closest('.spek-finder-submit');
        const restartButton = event.target.closest('.spek-finder-restart');

        if (nextButton && !nextButton.disabled) {
            if (nextButton.hasAttribute('data-category-next')) {
                refreshFacets().finally(() => {
                    if (isSeatCoverSelected() || applicationOptionCount > 0) {
                        showStep('application');
                    } else {
                        showStep('specs');
                    }
                });
            } else if (nextButton.hasAttribute('data-application-next')) {
                refreshFacets().finally(() => {
                    showStep(nextButton.dataset.nextStep);
                });
            } else {
                showStep(nextButton.dataset.nextStep);
            }
        }

        if (prevButton) {
            if (
                prevButton.dataset.prevStep === 'application'
                && !isSeatCoverSelected()
                && applicationOptionCount === 0
            ) {
                showStep('category');
            } else {
                showStep(prevButton.dataset.prevStep);
            }
        }

        if (submitButton) {
            fetchResults();
        }

        if (restartButton) {
            facetRequestId += 1;
            finder.querySelectorAll('input[type="radio"]').forEach((input) => {
                input.checked = false;
            });
            finder.querySelectorAll('input[type="text"], input[type="search"], input[type="number"]').forEach((input) => {
                input.value = '';
            });
            finder.querySelectorAll('select').forEach((select) => {
                select.selectedIndex = 0;
            });
            if (applicationOptions) {
                applicationOptions.innerHTML = '';
            }
            applicationOptionCount = 0;
            renderSpecs({materials: [], series: [], installationTypes: []});
            clearResults();
            setFacetsLoading(false);
            updateBranchLabels();
            updateSummary();
            updateRequiredButtons();
            showStep('category');
        }
    });

    updateBranchLabels();
    updateSummary();
    updateRequiredButtons();
    updateProgress('start');
})();
