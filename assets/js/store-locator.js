(function () {
    function spekText(text) { return (window.spekI18n && window.spekI18n[text]) || text; }
    'use strict';

    const root = document.querySelector('[data-partner-locator]');
    const mapElement = document.querySelector('[data-partner-map]');
    const searchInput = document.querySelector('[data-partner-search]');
    const radiusSelect = document.querySelector('[data-partner-radius]');
    const searchButton = document.querySelector('[data-partner-submit]');
    const locationButton = document.querySelector('[data-partner-use-location]');
    const resetButton = document.querySelector('[data-partner-reset]');
    const statusElement = document.querySelector('[data-partner-status]');
    const cards = Array.from(document.querySelectorAll('[data-partner-card]'));
    const emptyElement = document.querySelector('[data-partner-empty]');
    const resultsElement = document.querySelector('[data-partner-results]');

    if (!root || !mapElement || !cards.length || typeof L === 'undefined') {
        return;
    }

    const defaultCenter = [38.2749, 23.8103];
    const defaultZoom = 6;

    const map = L.map(mapElement, {
        scrollWheelZoom: false,
    }).setView(defaultCenter, defaultZoom);

    const tomtomKey = window.spekMapConfig?.tomtomKey || '';
    
    L.tileLayer(
        `https://{s}.api.tomtom.com/maps/orbis/display/raster/tile/{z}/{x}/{y}?apiVersion=2&style=street-light&tileSize=256&geopoliticalView=Unified&key=${encodeURIComponent(tomtomKey)}`,
        {
            subdomains: 'abcd',
            maxZoom: 22,
            attribution: '&copy; TomTom, &copy; OpenStreetMap',
        }
    ).addTo(map);

    const markerLayer = L.layerGroup().addTo(map);
    let radiusLayer = null;

    const spekPinIcon = L.divIcon({
        className: 'spek-map-pin',
        html: '<span></span>',
        iconSize: [34, 42],
        iconAnchor: [17, 42],
        popupAnchor: [0, -38],
    });

    function extractLatLngFromMapsUrl(url) {
        if (!url) {
            return null;
        }

        let decodedUrl = String(url);

        for (let i = 0; i < 3; i++) {
            try {
                decodedUrl = decodeURIComponent(decodedUrl);
            } catch (error) {
                break;
            }
        }

        const patterns = [
            /!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/,
            /[?&](?:q|query|ll|destination)=(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)/,
            /@(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)/,
        ];

        for (const pattern of patterns) {
            const matches = decodedUrl.match(pattern);

            if (!matches) {
                continue;
            }

            const lat = Number.parseFloat(matches[1]);
            const lng = Number.parseFloat(matches[2]);

            if (
                Number.isFinite(lat) &&
                Number.isFinite(lng) &&
                lat >= -90 &&
                lat <= 90 &&
                lng >= -180 &&
                lng <= 180
            ) {
                return { lat, lng };
            }
        }

        return null;
    }

    function normalize(value) {
        return String(value || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/ς/g, 'σ')
            .trim();
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value || '';
        return div.innerHTML;
    }

    function getRadiusKm() {
        const value = Number.parseFloat(radiusSelect ? radiusSelect.value : '10');
        return Number.isFinite(value) ? value : 10;
    }

    function toRadians(value) {
        return value * Math.PI / 180;
    }

    function distanceKm(origin, partner) {
        if (!origin || partner.lat === null || partner.lng === null) {
            return null;
        }

        const earthRadiusKm = 6371;
        const dLat = toRadians(partner.lat - origin.lat);
        const dLng = toRadians(partner.lng - origin.lng);
        const lat1 = toRadians(origin.lat);
        const lat2 = toRadians(partner.lat);

        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
            + Math.cos(lat1) * Math.cos(lat2)
            * Math.sin(dLng / 2) * Math.sin(dLng / 2);

        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

        return earthRadiusKm * c;
    }

    function setStatus(message) {
        if (statusElement) {
            statusElement.textContent = message;
        }
    }

    const partners = cards.map((card) => {
        let lat = Number.parseFloat(card.dataset.lat || '');
        let lng = Number.parseFloat(card.dataset.lng || '');

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            const extractedCoordinates = extractLatLngFromMapsUrl(card.dataset.mapsUrl || '');

            if (extractedCoordinates) {
                lat = extractedCoordinates.lat;
                lng = extractedCoordinates.lng;
            }
        }

        return {
            id: card.dataset.partnerId || '',
            title: card.dataset.title || '',
            area: card.dataset.area || '',
            postcode: card.dataset.postcode || '',
            address: card.dataset.address || '',
            search: card.dataset.search || '',
            mapsUrl: card.dataset.mapsUrl || '',
            lat: Number.isFinite(lat) ? lat : null,
            lng: Number.isFinite(lng) ? lng : null,
            marker: null,
            card,
        };
    });

    function clearMap() {
        markerLayer.clearLayers();

        if (radiusLayer) {
            map.removeLayer(radiusLayer);
            radiusLayer = null;
        }

        partners.forEach((partner) => {
            partner.marker = null;
        });
    }

    function hideAll(message) {
        partners.forEach((partner) => {
            partner.card.classList.add('is-hidden');
        });

        clearMap();

        if (emptyElement) {
            emptyElement.hidden = false;
        }

        setStatus(message || spekText('Συμπληρώστε περιοχή ή ΤΚ για να εμφανιστούν αποτελέσματα.'));
        map.setView(defaultCenter, defaultZoom);

        window.setTimeout(function () {
            map.invalidateSize();
        }, 150);
    }

    function focusPartnerCard(partner) {
        if (resultsElement) {
            const resultRect = resultsElement.getBoundingClientRect();
            const cardRect = partner.card.getBoundingClientRect();

            resultsElement.scrollTop += cardRect.top - resultRect.top - 16;
        } else {
            partner.card.scrollIntoView({
                block: 'nearest',
                behavior: 'smooth',
            });
        }

        partner.card.classList.add('is-highlighted');

        window.setTimeout(function () {
            partner.card.classList.remove('is-highlighted');
        }, 1300);
    }

    function showPartners(matches, origin) {
        const bounds = [];
        const radiusKm = getRadiusKm();

        clearMap();

        partners.forEach((partner) => {
            partner.card.classList.add('is-hidden');
        });

        matches.forEach((partner) => {
            partner.card.classList.remove('is-hidden');

            if (partner.lat !== null && partner.lng !== null) {
                const marker = L.marker([partner.lat, partner.lng], {
                    title: partner.title,
                    icon: spekPinIcon,
                }).addTo(markerLayer);

                const details = [partner.address, partner.postcode, partner.area].filter(Boolean).join(', ');
                const directionsLink = partner.mapsUrl
                    ? `<br><a href="${escapeHtml(partner.mapsUrl)}" target="_blank" rel="noopener">${escapeHtml(spekText('Οδηγίες'))}</a>`
                    : '';

                marker.bindPopup(
                    `<strong>${escapeHtml(partner.title)}</strong>${details ? `<br>${escapeHtml(details)}` : ''}${directionsLink}`
                );

                marker.on('click', function () {
                    focusPartnerCard(partner);
                });

                partner.marker = marker;
                bounds.push([partner.lat, partner.lng]);
            }
        });

        if (origin) {
            radiusLayer = L.circle([origin.lat, origin.lng], {
                radius: radiusKm * 1000,
            }).addTo(map);

            bounds.push([origin.lat, origin.lng]);
        }

        if (bounds.length > 1) {
            map.fitBounds(bounds, {
                padding: [42, 42],
                maxZoom: 14,
            });
        } else if (bounds.length === 1) {
            map.setView(bounds[0], 15);
        } else {
            map.setView(defaultCenter, defaultZoom);
        }

        if (emptyElement) {
            emptyElement.hidden = matches.length > 0;
        }

        const missingCoords = matches.filter((partner) => partner.lat === null || partner.lng === null).length;
        let message = spekText('Βρέθηκαν %s σημεία πώλησης.').replace('%s', matches.length);

        if (missingCoords > 0) {
            message += ' ' + spekText('%s δεν έχουν συντεταγμένες και δεν φαίνονται στον χάρτη.').replace('%s', missingCoords);
        }

        setStatus(message);

        window.setTimeout(function () {
            map.invalidateSize();
        }, 150);
    }

    function textMatches(query) {
        const normalizedQuery = normalize(query);

        if (!normalizedQuery) {
            return [];
        }

        return partners.filter((partner) => normalize(partner.search).includes(normalizedQuery));
    }

    async function geocodeQuery(query) {
        const normalizedQuery = query.trim();
    
        if (!normalizedQuery || !tomtomKey) {
            return null;
        }
    
        const params = new URLSearchParams({
            query: normalizedQuery,
            countryCodesIso2: 'GR',
            maxResults: '1',
            geopoliticalView: 'Unified',
        });
    
        const response = await fetch(
            `https://api.tomtom.com/maps/orbis/places/geocode?${params.toString()}`,
            {
                headers: {
                    'TomTom-Api-Key': tomtomKey,
                    'TomTom-Api-Version': '2',
                    'Attributes': 'results.position',
                    'Accept': 'application/json',
                    'Accept-Language': 'el-GR,el;q=0.9,en;q=0.8',
                },
            }
        );
    
        if (!response.ok) {
            return null;
        }
    
        const data = await response.json();
        const first = Array.isArray(data.results) ? data.results[0] : null;
    
        if (
            !first ||
            !first.position ||
            !Array.isArray(first.position.coordinates)
        ) {
            return null;
        }
    
        const lng = Number.parseFloat(first.position.coordinates[0]);
        const lat = Number.parseFloat(first.position.coordinates[1]);
    
        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return null;
        }
    
        return { lat, lng };
    }

    function radiusMatches(origin) {
        const radiusKm = getRadiusKm();

        if (!origin) {
            return [];
        }

        return partners
            .map((partner) => ({
                partner,
                distance: distanceKm(origin, partner),
            }))
            .filter((item) => item.distance !== null && item.distance <= radiusKm)
            .sort((a, b) => a.distance - b.distance)
            .map((item) => item.partner);
    }

    function uniquePartners(items) {
        const seen = new Set();
        const unique = [];

        items.forEach((partner) => {
            const key = partner.id || partner.title;

            if (!seen.has(key)) {
                seen.add(key);
                unique.push(partner);
            }
        });

        return unique;
    }

    async function runSearch() {
        const query = searchInput ? searchInput.value.trim() : '';

        if (!query) {
            hideAll(spekText('Συμπληρώστε περιοχή ή ΤΚ για να εμφανιστούν αποτελέσματα.'));
            return;
        }

        setStatus(spekText('Γίνεται αναζήτηση...'));

        const textResults = textMatches(query);
        let origin = null;
        let distanceResults = [];

        try {
            origin = await geocodeQuery(query);
            distanceResults = radiusMatches(origin);
        } catch (error) {
            origin = null;
            distanceResults = [];
        }

        const matches = uniquePartners(distanceResults.concat(textResults));

        showPartners(matches, origin);

        if (!matches.length) {
            setStatus(spekText('Δεν βρέθηκαν σημεία πώλησης για αυτή την αναζήτηση. Δοκιμάστε άλλη περιοχή, ΤΚ ή μεγαλύτερη ακτίνα.'));
        }
    }

    function searchNearUser() {
        if (!navigator.geolocation) {
            setStatus(spekText('Ο browser δεν υποστηρίζει εντοπισμό τοποθεσίας.'));
            return;
        }

        setStatus(spekText('Εντοπίζεται η θέση σας...'));

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const origin = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };

                const matches = radiusMatches(origin);

                showPartners(matches, origin);

                if (!matches.length) {
                    setStatus(spekText('Δεν βρέθηκαν σημεία πώλησης κοντά σας. Δοκιμάστε μεγαλύτερη ακτίνα.'));
                }
            },
            function () {
                setStatus(spekText('Δεν δόθηκε πρόσβαση στην τοποθεσία. Συμπληρώστε περιοχή ή ΤΚ χειροκίνητα.'));
            },
            {
                enableHighAccuracy: false,
                timeout: 10000,
                maximumAge: 300000,
            }
        );
    }

    function setupLockedLocatorScroll() {
        const grid = document.querySelector('.store-locator-grid');

        if (!grid || !resultsElement) {
            return;
        }

        grid.addEventListener('wheel', function (event) {
            if (window.innerWidth <= 900) {
                return;
            }

            const maxScroll = resultsElement.scrollHeight - resultsElement.clientHeight;

            if (maxScroll <= 0) {
                return;
            }

            const scrollingDown = event.deltaY > 0;
            const scrollingUp = event.deltaY < 0;

            const canScrollDown = scrollingDown && resultsElement.scrollTop < maxScroll - 2;
            const canScrollUp = scrollingUp && resultsElement.scrollTop > 2;

            if (canScrollDown || canScrollUp) {
                event.preventDefault();
                resultsElement.scrollTop += event.deltaY;
            }
        }, { passive: false });
    }

    partners.forEach((partner) => {
        partner.card.addEventListener('mouseenter', function () {
            if (partner.marker) {
                partner.marker.openPopup();
            }
        });
    });

    if (searchButton) {
        searchButton.addEventListener('click', runSearch);
    }

    if (searchInput) {
        searchInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                runSearch();
            }
        });
    }

    if (radiusSelect) {
        radiusSelect.addEventListener('change', function () {
            if (searchInput && searchInput.value.trim()) {
                runSearch();
            }
        });
    }

    if (locationButton) {
        locationButton.addEventListener('click', searchNearUser);
    }

    if (resetButton) {
        resetButton.addEventListener('click', function () {
            if (searchInput) {
                searchInput.value = '';
            }

            hideAll();
        });
    }

    window.addEventListener('resize', function () {
        window.setTimeout(function () {
            map.invalidateSize();
        }, 150);
    });

    setupLockedLocatorScroll();
    hideAll();
})();