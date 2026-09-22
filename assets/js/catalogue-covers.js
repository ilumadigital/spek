(function () {
    'use strict';

    var covers = Array.prototype.slice.call(document.querySelectorAll('[data-catalogue-cover]'));

    if (!covers.length) {
        return;
    }

    var pdfJsPromise = null;

    function loadPdfJs(root) {
        if (pdfJsPromise) {
            return pdfJsPromise;
        }

        var pdfJsUrl = root.getAttribute('data-pdfjs-url');
        var workerUrl = root.getAttribute('data-pdf-worker-url');

        if (!pdfJsUrl || !workerUrl) {
            return Promise.reject(new Error('PDF.js configuration missing'));
        }

        pdfJsPromise = import(pdfJsUrl).then(function (pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = workerUrl;
            return pdfjsLib;
        });

        return pdfJsPromise;
    }

    async function renderCover(root) {
        if (!root || root.dataset.coverRendered === '1' || root.dataset.coverRendering === '1') {
            return;
        }

        var pdfUrl = root.getAttribute('data-pdf-url');
        var canvas = root.querySelector('[data-catalogue-cover-canvas]');
        var loading = root.querySelector('[data-catalogue-cover-loading]');

        if (!pdfUrl || !canvas) {
            return;
        }

        root.dataset.coverRendering = '1';

        try {
            var pdfjsLib = await loadPdfJs(root);
            var loadingTask = pdfjsLib.getDocument({
                url: pdfUrl,
                cMapPacked: true,
                enableXfa: true
            });
            var pdf = await loadingTask.promise;
            var page = await pdf.getPage(1);

            var baseViewport = page.getViewport({ scale: 1 });
            var availableWidth = Math.max(220, root.clientWidth || 320);
            var cssWidth = Math.min(availableWidth, 720);
            var scale = cssWidth / baseViewport.width;
            var viewport = page.getViewport({ scale: scale });
            var outputScale = Math.min(window.devicePixelRatio || 1, 2);
            var context = canvas.getContext('2d', { alpha: false });

            canvas.width = Math.floor(viewport.width * outputScale);
            canvas.height = Math.floor(viewport.height * outputScale);
            canvas.style.width = '100%';
            canvas.style.height = 'auto';

            await page.render({
                canvasContext: context,
                viewport: viewport,
                transform: outputScale !== 1
                    ? [outputScale, 0, 0, outputScale, 0, 0]
                    : null
            }).promise;

            root.dataset.coverRendered = '1';
            root.classList.add('is-rendered');

            if (loading) {
                loading.hidden = true;
            }

            if (typeof pdf.destroy === 'function') {
                pdf.destroy();
            }
        } catch (error) {
            root.classList.add('is-preview-fallback');
            if (loading) {
                loading.hidden = true;
            }
        } finally {
            root.dataset.coverRendering = '0';
        }
    }

    if (!('IntersectionObserver' in window)) {
        covers.forEach(renderCover);
        return;
    }

    var observer = new IntersectionObserver(function (entries, obs) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) {
                return;
            }

            renderCover(entry.target);
            obs.unobserve(entry.target);
        });
    }, {
        rootMargin: '500px 0px',
        threshold: 0.01
    });

    covers.forEach(function (cover) {
        observer.observe(cover);
    });
}());
