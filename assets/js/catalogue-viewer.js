(function () {
    'use strict';

    var root = document.querySelector('[data-catalogue-viewer]');
    if (!root) {
        return;
    }

    var pdfUrl = root.getAttribute('data-pdf-url');
    var pdfJsUrl = root.getAttribute('data-pdfjs-url');
    var workerUrl = root.getAttribute('data-pdf-worker-url');

    if (!pdfUrl || !pdfJsUrl || !workerUrl) {
        return;
    }

    var spreadEl = root.querySelector('[data-pdf-spread]');
    var stageEl = root.querySelector('[data-pdf-stage]');
    var loadingEl = root.querySelector('[data-pdf-loading]');
    var loadingStatusEl = root.querySelector('[data-pdf-loading-status]');
    var fallbackEl = root.querySelector('[data-pdf-fallback]');
    var noticeEl = root.querySelector('[data-pdf-notice]');
    var pageStatusEl = root.querySelector('[data-pdf-page-status]');
    var prevButton = root.querySelector('[data-pdf-prev]');
    var nextButton = root.querySelector('[data-pdf-next]');
    var zoomOutButton = root.querySelector('[data-pdf-zoom-out]');
    var zoomResetButton = root.querySelector('[data-pdf-zoom-reset]');
    var zoomInButton = root.querySelector('[data-pdf-zoom-in]');
    var fullscreenButton = root.querySelector('[data-pdf-fullscreen]');
    var searchForm = root.querySelector('[data-pdf-search-form]');
    var searchInput = root.querySelector('[data-pdf-search-input]');
    var searchResultsEl = root.querySelector('[data-pdf-search-results]');
    var searchStatusEl = root.querySelector('[data-pdf-search-status]');
    var matchPrevButton = root.querySelector('[data-pdf-match-prev]');
    var matchNextButton = root.querySelector('[data-pdf-match-next]');

    var pdfDocument = null;
    var pageStart = 1;
    var spreadSize = getSpreadSize();
    var zoom = 1;
    var renderGeneration = 0;
    var renderTimer = null;
    var textCache = new Map();
    var matchPages = [];
    var matchIndex = -1;
    var searchGeneration = 0;
    var touchStartX = 0;
    var touchStartY = 0;

    function getSpreadSize() {
        return window.matchMedia('(min-width: 900px)').matches ? 2 : 1;
    }

    function normalizeText(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLocaleLowerCase('el-GR')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function setLoading(message) {
        if (!loadingEl) {
            return;
        }
        loadingEl.hidden = false;
        if (loadingStatusEl && message) {
            loadingStatusEl.textContent = message;
        }
    }

    function hideLoading() {
        if (loadingEl) {
            loadingEl.hidden = true;
        }
    }

    function showNotice(message, type) {
        if (!noticeEl) {
            return;
        }
        noticeEl.textContent = message;
        noticeEl.className = 'catalogue-viewer__notice' + (type ? ' is-' + type : '');
        noticeEl.hidden = false;
    }

    function hideNotice() {
        if (noticeEl) {
            noticeEl.hidden = true;
        }
    }

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function startForPage(pageNumber) {
        pageNumber = clamp(parseInt(pageNumber, 10) || 1, 1, pdfDocument ? pdfDocument.numPages : 1);

        if (spreadSize === 1 || pageNumber === 1) {
            return pageNumber;
        }

        return pageNumber % 2 === 0 ? pageNumber : pageNumber - 1;
    }

    function visiblePages() {
        if (!pdfDocument) {
            return [];
        }

        if (spreadSize === 1 || pageStart === 1) {
            return [pageStart];
        }

        var pages = [pageStart];
        if (pageStart + 1 <= pdfDocument.numPages) {
            pages.push(pageStart + 1);
        }
        return pages;
    }

    function updateControls() {
        if (!pdfDocument) {
            return;
        }

        var pages = visiblePages();
        var lastVisible = pages.length ? pages[pages.length - 1] : pageStart;

        if (prevButton) {
            prevButton.disabled = pageStart <= 1;
        }
        if (nextButton) {
            nextButton.disabled = lastVisible >= pdfDocument.numPages;
        }

        if (pageStatusEl) {
            if (pages.length === 2) {
                pageStatusEl.textContent = pages[0] + '–' + pages[1] + ' / ' + pdfDocument.numPages;
            } else {
                pageStatusEl.textContent = pageStart + ' / ' + pdfDocument.numPages;
            }
        }

        if (zoomResetButton) {
            zoomResetButton.textContent = Math.round(zoom * 100) + '%';
        }
    }

    function createPageShell(pageNumber) {
        var shell = document.createElement('figure');
        shell.className = 'catalogue-viewer__page';
        shell.setAttribute('data-page-number', String(pageNumber));

        if (matchPages.indexOf(pageNumber) !== -1) {
            shell.classList.add('is-search-hit');
        }

        var canvas = document.createElement('canvas');
        canvas.setAttribute('aria-label', 'Σελίδα ' + pageNumber);

        var caption = document.createElement('figcaption');
        caption.textContent = 'Σελίδα ' + pageNumber;

        if (matchPages.indexOf(pageNumber) !== -1) {
            var badge = document.createElement('span');
            badge.className = 'catalogue-viewer__match-badge';
            badge.textContent = 'Αποτέλεσμα';
            shell.appendChild(badge);
        }

        shell.appendChild(canvas);
        shell.appendChild(caption);

        return { shell: shell, canvas: canvas };
    }

    async function renderPage(pageNumber, targetWidth, generation) {
        var page = await pdfDocument.getPage(pageNumber);
        if (generation !== renderGeneration) {
            return null;
        }

        var baseViewport = page.getViewport({ scale: 1 });
        var fitScale = targetWidth / baseViewport.width;
        var viewport = page.getViewport({ scale: fitScale * zoom });
        var pageBits = createPageShell(pageNumber);
        var canvas = pageBits.canvas;
        var context = canvas.getContext('2d', { alpha: false });
        var outputScale = Math.min(window.devicePixelRatio || 1, 2);

        canvas.width = Math.floor(viewport.width * outputScale);
        canvas.height = Math.floor(viewport.height * outputScale);
        canvas.style.width = Math.floor(viewport.width) + 'px';
        canvas.style.height = Math.floor(viewport.height) + 'px';

        var renderContext = {
            canvasContext: context,
            viewport: viewport,
            transform: outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null,
            intent: 'display'
        };

        await page.render(renderContext).promise;

        if (generation !== renderGeneration) {
            return null;
        }

        return pageBits.shell;
    }

    async function renderSpread(options) {
        if (!pdfDocument || !spreadEl) {
            return;
        }

        options = options || {};
        var generation = ++renderGeneration;
        var pages = visiblePages();
        // Keep a consistent page size on desktop, including the cover (page 1).
        // The cover is shown on its own, but it should occupy exactly the same
        // width as one page of a two-page spread rather than being enlarged.
        var gap = spreadSize === 2 ? 22 : 0;
        var availableWidth = Math.max(spreadEl.clientWidth - gap, 280);
        var targetWidth = spreadSize === 2 ? availableWidth / 2 : Math.min(availableWidth, 860);

        if (options.animate) {
            spreadEl.classList.add('is-turning');
        }

        updateControls();

        try {
            var rendered = await Promise.all(pages.map(function (pageNumber) {
                return renderPage(pageNumber, targetWidth, generation);
            }));

            if (generation !== renderGeneration) {
                return;
            }

            spreadEl.innerHTML = '';
            rendered.filter(Boolean).forEach(function (shell) {
                spreadEl.appendChild(shell);
            });

            spreadEl.classList.toggle('is-single-page', rendered.filter(Boolean).length === 1);
        } catch (error) {
            console.error('SPEK catalogue render error:', error);
            showNotice('Δεν ήταν δυνατή η απόδοση αυτής της σελίδας.', 'error');
        } finally {
            window.setTimeout(function () {
                spreadEl.classList.remove('is-turning');
            }, 220);
        }
    }

    function goNext() {
        if (!pdfDocument) {
            return;
        }

        var pages = visiblePages();
        var lastVisible = pages.length ? pages[pages.length - 1] : pageStart;
        if (lastVisible >= pdfDocument.numPages) {
            return;
        }

        if (spreadSize === 2) {
            pageStart = pageStart === 1 ? 2 : Math.min(pageStart + 2, pdfDocument.numPages);
        } else {
            pageStart = Math.min(pageStart + 1, pdfDocument.numPages);
        }

        renderSpread({ animate: true });
    }

    function goPrev() {
        if (!pdfDocument || pageStart <= 1) {
            return;
        }

        if (spreadSize === 2) {
            pageStart = pageStart === 2 ? 1 : Math.max(2, pageStart - 2);
        } else {
            pageStart = Math.max(1, pageStart - 1);
        }

        renderSpread({ animate: true });
    }

    function goToPage(pageNumber, animate) {
        if (!pdfDocument) {
            return;
        }

        pageStart = startForPage(pageNumber);
        renderSpread({ animate: animate !== false });
    }

    function changeZoom(delta) {
        var nextZoom = clamp(Math.round((zoom + delta) * 100) / 100, 0.7, 1.8);
        if (nextZoom === zoom) {
            return;
        }
        zoom = nextZoom;
        renderSpread();
    }

    function resetZoom() {
        zoom = 1;
        renderSpread();
    }

    async function getPageText(pageNumber) {
        if (textCache.has(pageNumber)) {
            return textCache.get(pageNumber);
        }

        var page = await pdfDocument.getPage(pageNumber);
        var content = await page.getTextContent({ normalizeWhitespace: true });
        var text = content.items.map(function (item) {
            return item && typeof item.str === 'string' ? item.str : '';
        }).join(' ');
        var normalized = normalizeText(text);
        textCache.set(pageNumber, normalized);
        return normalized;
    }

    function updateSearchStatus() {
        if (!searchResultsEl || !searchStatusEl) {
            return;
        }

        searchResultsEl.hidden = false;

        if (!matchPages.length) {
            searchStatusEl.textContent = '0 αποτελέσματα';
            if (matchPrevButton) {
                matchPrevButton.disabled = true;
            }
            if (matchNextButton) {
                matchNextButton.disabled = true;
            }
            return;
        }

        searchStatusEl.textContent = (matchIndex + 1) + ' / ' + matchPages.length + ' · σελ. ' + matchPages[matchIndex];
        if (matchPrevButton) {
            matchPrevButton.disabled = matchPages.length < 2;
        }
        if (matchNextButton) {
            matchNextButton.disabled = matchPages.length < 2;
        }
    }

    async function runSearch(query) {
        if (!pdfDocument) {
            return;
        }

        var normalizedQuery = normalizeText(query);
        if (normalizedQuery.length < 2) {
            matchPages = [];
            matchIndex = -1;
            if (searchResultsEl) {
                searchResultsEl.hidden = true;
            }
            showNotice('Γράψε τουλάχιστον 2 χαρακτήρες για αναζήτηση.', 'info');
            renderSpread();
            return;
        }

        var tokens = normalizedQuery.split(' ').filter(Boolean);
        var generation = ++searchGeneration;
        matchPages = [];
        matchIndex = -1;
        hideNotice();

        if (searchResultsEl && searchStatusEl) {
            searchResultsEl.hidden = false;
            searchStatusEl.textContent = 'Αναζήτηση…';
        }
        if (matchPrevButton) {
            matchPrevButton.disabled = true;
        }
        if (matchNextButton) {
            matchNextButton.disabled = true;
        }

        for (var pageNumber = 1; pageNumber <= pdfDocument.numPages; pageNumber += 1) {
            if (generation !== searchGeneration) {
                return;
            }

            try {
                var pageText = await getPageText(pageNumber);
                var isMatch = tokens.every(function (token) {
                    return pageText.indexOf(token) !== -1;
                });

                if (isMatch) {
                    matchPages.push(pageNumber);
                }
            } catch (error) {
                console.warn('Could not index PDF page ' + pageNumber, error);
            }

            if (searchStatusEl && (pageNumber === 1 || pageNumber % 5 === 0 || pageNumber === pdfDocument.numPages)) {
                searchStatusEl.textContent = 'Αναζήτηση ' + pageNumber + ' / ' + pdfDocument.numPages + '…';
            }
        }

        if (generation !== searchGeneration) {
            return;
        }

        if (!matchPages.length) {
            updateSearchStatus();
            showNotice('Δεν βρέθηκε ο κωδικός ή η ονομασία μέσα στο PDF.', 'info');
            renderSpread();
            return;
        }

        matchIndex = 0;
        updateSearchStatus();
        hideNotice();
        goToPage(matchPages[matchIndex]);
    }

    function moveMatch(direction) {
        if (!matchPages.length) {
            return;
        }

        matchIndex = (matchIndex + direction + matchPages.length) % matchPages.length;
        updateSearchStatus();
        goToPage(matchPages[matchIndex]);
    }

    function handleResize() {
        window.clearTimeout(renderTimer);
        renderTimer = window.setTimeout(function () {
            var nextSpreadSize = getSpreadSize();
            var anchorPage = pageStart;
            if (nextSpreadSize !== spreadSize) {
                spreadSize = nextSpreadSize;
                pageStart = startForPage(anchorPage);
            }
            renderSpread();
        }, 160);
    }

    function enableInteractions() {
        if (prevButton) {
            prevButton.addEventListener('click', goPrev);
        }
        if (nextButton) {
            nextButton.addEventListener('click', goNext);
        }
        if (zoomOutButton) {
            zoomOutButton.addEventListener('click', function () { changeZoom(-0.1); });
        }
        if (zoomInButton) {
            zoomInButton.addEventListener('click', function () { changeZoom(0.1); });
        }
        if (zoomResetButton) {
            zoomResetButton.addEventListener('click', resetZoom);
        }

        if (fullscreenButton) {
            fullscreenButton.addEventListener('click', function () {
                if (!document.fullscreenElement && root.requestFullscreen) {
                    root.requestFullscreen();
                } else if (document.fullscreenElement && document.exitFullscreen) {
                    document.exitFullscreen();
                }
            });
        }

        document.addEventListener('fullscreenchange', function () {
            root.classList.toggle('is-fullscreen', document.fullscreenElement === root);
            window.setTimeout(handleResize, 80);
        });

        if (searchForm) {
            searchForm.addEventListener('submit', function (event) {
                event.preventDefault();
                runSearch(searchInput ? searchInput.value : '');
            });
        }

        if (searchInput) {
            searchInput.addEventListener('search', function () {
                if (searchInput.value.trim() === '') {
                    searchGeneration += 1;
                    matchPages = [];
                    matchIndex = -1;
                    if (searchResultsEl) {
                        searchResultsEl.hidden = true;
                    }
                    hideNotice();
                    renderSpread();
                }
            });
        }

        if (matchPrevButton) {
            matchPrevButton.addEventListener('click', function () { moveMatch(-1); });
        }
        if (matchNextButton) {
            matchNextButton.addEventListener('click', function () { moveMatch(1); });
        }

        document.addEventListener('keydown', function (event) {
            var activeTag = document.activeElement ? document.activeElement.tagName : '';
            var isTyping = activeTag === 'INPUT' || activeTag === 'TEXTAREA' || activeTag === 'SELECT';
            if (isTyping) {
                return;
            }

            if (event.key === 'ArrowRight' || event.key === 'PageDown') {
                event.preventDefault();
                goNext();
            } else if (event.key === 'ArrowLeft' || event.key === 'PageUp') {
                event.preventDefault();
                goPrev();
            }
        });

        if (stageEl) {
            stageEl.addEventListener('touchstart', function (event) {
                if (!event.touches || !event.touches.length) {
                    return;
                }
                touchStartX = event.touches[0].clientX;
                touchStartY = event.touches[0].clientY;
            }, { passive: true });

            stageEl.addEventListener('touchend', function (event) {
                if (!event.changedTouches || !event.changedTouches.length) {
                    return;
                }

                var deltaX = event.changedTouches[0].clientX - touchStartX;
                var deltaY = event.changedTouches[0].clientY - touchStartY;

                if (Math.abs(deltaX) < 55 || Math.abs(deltaX) < Math.abs(deltaY)) {
                    return;
                }

                if (deltaX < 0) {
                    goNext();
                } else {
                    goPrev();
                }
            }, { passive: true });
        }

        window.addEventListener('resize', handleResize);
    }

    async function init() {
        setLoading('Φόρτωση PDF.js…');

        try {
            var pdfjsLib = await import(pdfJsUrl);
            pdfjsLib.GlobalWorkerOptions.workerSrc = workerUrl;

            setLoading('Άνοιγμα PDF…');

            var loadingTask = pdfjsLib.getDocument({
                url: pdfUrl,
                isEvalSupported: false,
                useWorkerFetch: true
            });

            loadingTask.onProgress = function (progress) {
                if (!loadingStatusEl || !progress || !progress.total) {
                    return;
                }
                var percentage = Math.round((progress.loaded / progress.total) * 100);
                loadingStatusEl.textContent = 'Λήψη PDF ' + percentage + '%';
            };

            pdfDocument = await loadingTask.promise;
            pageStart = 1;
            spreadSize = getSpreadSize();
            enableInteractions();
            await renderSpread();
            hideLoading();
        } catch (error) {
            console.error('SPEK catalogue viewer error:', error);
            hideLoading();
            if (stageEl) {
                stageEl.hidden = true;
            }
            if (fallbackEl) {
                fallbackEl.hidden = false;
            }
        }
    }

    init();
}());
