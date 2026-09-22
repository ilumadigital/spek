(function () {
    'use strict';

    const header = document.querySelector('[data-header]');
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const mobileMenu = document.querySelector('[data-mobile-menu]');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function handleHeaderScroll() {
        if (!header) {
            return;
        }

        header.classList.toggle('is-scrolled', window.scrollY > 20);
    }

    function setMobileMenuState(isOpen) {
        if (!menuToggle || !mobileMenu) {
            return;
        }

        menuToggle.classList.toggle('is-active', isOpen);
        menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

        mobileMenu.classList.toggle('is-open', isOpen);
        mobileMenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

        document.body.classList.toggle('has-mobile-menu-open', isOpen);
    }

    function toggleMobileMenu() {
        if (!mobileMenu) {
            return;
        }

        const isOpen = !mobileMenu.classList.contains('is-open');
        setMobileMenuState(isOpen);
    }

    function setupMobileMenu() {
        if (!menuToggle || !mobileMenu) {
            return;
        }

        menuToggle.addEventListener('click', toggleMobileMenu);

        mobileMenu.addEventListener('click', function (event) {
            if (event.target.closest('a')) {
                setMobileMenuState(false);
            }
        });

        document.addEventListener('keyup', function (event) {
            if (event.key === 'Escape') {
                setMobileMenuState(false);
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 1100) {
                setMobileMenuState(false);
            }
        });
    }

    function setupRevealAnimations() {
        const autoTargets = document.querySelectorAll(
            '.category-card, .feature-card, .finder-step'
        );

        autoTargets.forEach((element, index) => {
            if (!element.hasAttribute('data-reveal')) {
                element.setAttribute('data-reveal', '');
                element.style.setProperty('--reveal-delay', `${(index % 6) * 70}ms`);
            }
        });

        const revealTargets = document.querySelectorAll('[data-reveal]');

        if (!revealTargets.length) {
            return;
        }

        revealTargets.forEach((element) => {
            const customDelay = element.getAttribute('data-delay');

            if (customDelay) {
                element.style.setProperty('--reveal-delay', `${customDelay}ms`);
            }

            if (prefersReducedMotion) {
                element.classList.add('is-visible');
            }
        });

        if (prefersReducedMotion) {
            return;
        }

        const observer = new IntersectionObserver((entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -40px 0px',
        });

        revealTargets.forEach((element) => {
            observer.observe(element);
        });
    }

    function setupParallax() {
        if (prefersReducedMotion) {
            return;
        }

        const wrappers = document.querySelectorAll('[data-parallax-wrap]');

        wrappers.forEach((wrapper) => {
            const items = wrapper.querySelectorAll('[data-parallax]');

            if (!items.length) {
                return;
            }

            wrapper.addEventListener('mousemove', (event) => {
                const rect = wrapper.getBoundingClientRect();
                const offsetX = ((event.clientX - rect.left) / rect.width) - 0.5;
                const offsetY = ((event.clientY - rect.top) / rect.height) - 0.5;

                items.forEach((item) => {
                    const factor = Number(item.getAttribute('data-parallax')) || 10;
                    item.style.setProperty('--px', `${offsetX * factor}px`);
                    item.style.setProperty('--py', `${offsetY * factor}px`);
                });
            });

            wrapper.addEventListener('mouseleave', () => {
                items.forEach((item) => {
                    item.style.setProperty('--px', '0px');
                    item.style.setProperty('--py', '0px');
                });
            });
        });
    }


    function setupMediaSliders() {
        const sliders = document.querySelectorAll('[data-spek-slider]');

        if (!sliders.length) {
            return;
        }

        sliders.forEach((slider) => {
            const track = slider.querySelector('[data-slider-track]');
            const viewport = slider.querySelector('[data-slider-viewport]');
            const slides = Array.from(slider.querySelectorAll('[data-slider-slide]'));
            const prevButton = slider.querySelector('[data-slider-prev]');
            const nextButton = slider.querySelector('[data-slider-next]');
            const dotsContainer = slider.querySelector('[data-slider-dots]');
            const currentLabel = slider.querySelector('[data-slider-current]');

            if (!track || slides.length < 2) {
                return;
            }

            let currentIndex = 0;
            let autoplayTimer = null;
            let pointerStartX = null;
            const autoplayEnabled = slider.getAttribute('data-autoplay') !== 'false' && !prefersReducedMotion;
            const autoplayDelay = 5800;
            const dots = [];

            slider.setAttribute('tabindex', '0');

            function formatIndex(index) {
                return String(index + 1).padStart(2, '0');
            }

            function updateSlider(index, userInitiated = false) {
                currentIndex = (index + slides.length) % slides.length;
                track.style.transform = `translate3d(-${currentIndex * 100}%, 0, 0)`;

                slides.forEach((slide, slideIndex) => {
                    const isActive = slideIndex === currentIndex;
                    slide.classList.toggle('is-active', isActive);
                    slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                });

                dots.forEach((dot, dotIndex) => {
                    const isActive = dotIndex === currentIndex;
                    dot.classList.toggle('is-active', isActive);
                    dot.setAttribute('aria-current', isActive ? 'true' : 'false');
                });

                if (currentLabel) {
                    currentLabel.textContent = formatIndex(currentIndex);
                }

                if (userInitiated) {
                    restartAutoplay();
                }
            }

            function goNext(userInitiated = false) {
                updateSlider(currentIndex + 1, userInitiated);
            }

            function goPrev(userInitiated = false) {
                updateSlider(currentIndex - 1, userInitiated);
            }

            function stopAutoplay() {
                if (autoplayTimer) {
                    window.clearInterval(autoplayTimer);
                    autoplayTimer = null;
                }
            }

            function startAutoplay() {
                if (!autoplayEnabled || autoplayTimer) {
                    return;
                }

                autoplayTimer = window.setInterval(() => {
                    goNext(false);
                }, autoplayDelay);
            }

            function restartAutoplay() {
                stopAutoplay();
                startAutoplay();
            }

            if (dotsContainer) {
                slides.forEach((slide, index) => {
                    const dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'spek-media-slider__dot';
                    dot.setAttribute('aria-label', `Slide ${index + 1}`);
                    dot.addEventListener('click', () => updateSlider(index, true));
                    dotsContainer.appendChild(dot);
                    dots.push(dot);
                });
            }

            if (prevButton) {
                prevButton.addEventListener('click', () => goPrev(true));
            }

            if (nextButton) {
                nextButton.addEventListener('click', () => goNext(true));
            }

            slider.addEventListener('keydown', (event) => {
                if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    goPrev(true);
                }

                if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    goNext(true);
                }
            });

            slider.addEventListener('mouseenter', stopAutoplay);
            slider.addEventListener('mouseleave', startAutoplay);
            slider.addEventListener('focusin', stopAutoplay);
            slider.addEventListener('focusout', startAutoplay);

            if (viewport) {
                viewport.addEventListener('pointerdown', (event) => {
                    if (event.pointerType === 'mouse' && event.button !== 0) {
                        return;
                    }

                    pointerStartX = event.clientX;
                });

                viewport.addEventListener('pointerup', (event) => {
                    if (pointerStartX === null) {
                        return;
                    }

                    const deltaX = event.clientX - pointerStartX;
                    pointerStartX = null;

                    if (Math.abs(deltaX) < 45) {
                        return;
                    }

                    if (deltaX < 0) {
                        goNext(true);
                    } else {
                        goPrev(true);
                    }
                });

                viewport.addEventListener('pointercancel', () => {
                    pointerStartX = null;
                });
            }

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stopAutoplay();
                } else {
                    startAutoplay();
                }
            });

            updateSlider(0, false);
            startAutoplay();
        });
    }



    function setupHeroBackgroundSlider() {
        const heroes = document.querySelectorAll('[data-hero-slider]');

        if (!heroes.length) {
            return;
        }

        heroes.forEach((hero) => {
            const slides = Array.from(hero.querySelectorAll('[data-hero-slide]'));
            const dotsContainer = hero.querySelector('[data-hero-dots]');
            const currentLabel = hero.querySelector('[data-hero-current]');

            if (slides.length < 2) {
                return;
            }

            const dots = [];
            const autoplayDelay = Number(hero.getAttribute('data-hero-delay')) || 6000;
            let currentIndex = 0;
            let timer = null;

            function formatIndex(index) {
                return String(index + 1).padStart(2, '0');
            }

            function update(index) {
                currentIndex = (index + slides.length) % slides.length;

                slides.forEach((slide, slideIndex) => {
                    const isActive = slideIndex === currentIndex;
                    slide.classList.toggle('is-active', isActive);
                    slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
                });

                dots.forEach((dot, dotIndex) => {
                    const isActive = dotIndex === currentIndex;
                    dot.classList.toggle('is-active', isActive);
                    dot.setAttribute('aria-current', isActive ? 'true' : 'false');
                });

                if (currentLabel) {
                    currentLabel.textContent = formatIndex(currentIndex);
                }
            }

            function stopAutoplay() {
                if (timer) {
                    window.clearInterval(timer);
                    timer = null;
                }
            }

            function startAutoplay() {
                if (prefersReducedMotion || timer) {
                    return;
                }

                timer = window.setInterval(() => {
                    update(currentIndex + 1);
                }, autoplayDelay);
            }

            if (dotsContainer) {
                slides.forEach((slide, index) => {
                    const dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'spek-hero-v2__slider-dot';
                    dot.setAttribute('aria-label', `Hero slide ${index + 1}`);
                    dot.addEventListener('click', () => {
                        update(index);
                        stopAutoplay();
                        startAutoplay();
                    });
                    dotsContainer.appendChild(dot);
                    dots.push(dot);
                });
            }

            hero.addEventListener('mouseenter', stopAutoplay);
            hero.addEventListener('mouseleave', startAutoplay);
            hero.addEventListener('focusin', stopAutoplay);
            hero.addEventListener('focusout', startAutoplay);

            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    stopAutoplay();
                } else {
                    startAutoplay();
                }
            });

            update(0);
            startAutoplay();
        });
    }

    function setupCompanyDetailsCopy() {
        const copyButtons = document.querySelectorAll('[data-copy-value]');

        if (!copyButtons.length) {
            return;
        }

        async function copyText(value) {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(value);
                return;
            }

            const textarea = document.createElement('textarea');
            textarea.value = value;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            textarea.style.pointerEvents = 'none';
            textarea.style.left = '-9999px';

            document.body.appendChild(textarea);
            textarea.select();
            textarea.setSelectionRange(0, textarea.value.length);

            const didCopy = document.execCommand('copy');
            textarea.remove();

            if (!didCopy) {
                throw new Error('Copy command failed');
            }
        }

        copyButtons.forEach((button) => {
            const defaultLabel = button.getAttribute('data-copy-label') || button.getAttribute('aria-label') || 'Copy';
            const successLabel = button.getAttribute('data-copy-success') || 'Copied';
            let resetTimer = null;

            button.addEventListener('click', async () => {
                const value = button.getAttribute('data-copy-value') || '';

                if (!value) {
                    return;
                }

                try {
                    await copyText(value);

                    button.classList.add('is-copied');
                    button.setAttribute('aria-label', successLabel);
                    button.setAttribute('title', successLabel);

                    if (resetTimer) {
                        window.clearTimeout(resetTimer);
                    }

                    resetTimer = window.setTimeout(() => {
                        button.classList.remove('is-copied');
                        button.setAttribute('aria-label', defaultLabel);
                        button.setAttribute('title', defaultLabel);
                    }, 1400);
                } catch (error) {
                    button.classList.remove('is-copied');
                    button.setAttribute('aria-label', defaultLabel);
                    button.setAttribute('title', defaultLabel);
                }
            });
        });
    }

    window.addEventListener('scroll', handleHeaderScroll, { passive: true });

    handleHeaderScroll();
    setupMobileMenu();
    setupRevealAnimations();
    setupParallax();
    setupMediaSliders();
    setupHeroBackgroundSlider();
    setupCompanyDetailsCopy();
})();