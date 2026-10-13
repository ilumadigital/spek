/**
 * SPEK Google Maps partner locator.
 * Uses WordPress-supplied coordinates and optional user-driven geocoding.
 * Does NOT bulk-geocode all partners in visitors' browsers.
 */
(function () {
    'use strict';

    const root = document.querySelector('[data-partner-locator]');
    const mapElement = document.querySelector('[data-partner-map]');
    const searchInput = document.querySelector('[data-partner-search]');
    const radiusSelect = document.querySelector('[data-partner-radius]');
    const searchButton = document.querySelector('[data-partner-submit]');
    const locationButton = document.querySelector('[data-partner-use-location]');
    const resetButton = document.querySelector('[data-partner-reset]');
    const statusElement = document.querySelector('[data-partner-status]');
    const emptyElement = document.querySelector('[data-partner-empty]');
    const resultsElement = document.querySelector('[data-partner-results]');
    const cards = Array.from(document.querySelectorAll('[data-partner-card]'));
    const config = window.spekMapConfig || {};
    const key = config.googleBrowserKey || '';

    if (!root || !mapElement || !cards.length || !key) {
        return;
    }

    const initialCenter = {lat: 38.2749, lng: 23.8103};
    const initialZoom = 6;
    // Approximate geographic envelope of Greece, generously including islands.
    // Used only to detect demonstrably incorrect coordinates on Greek 5-digit ZIPs.
    const greekBounds = {south: 34.2, north: 42.4, west: 19.0, east: 30.5};
    const inGreece = function (lat, lng) {
        return lat >= greekBounds.south && lat <= greekBounds.north
            && lng >= greekBounds.west && lng <= greekBounds.east;
    };
    const text = function (value) {
        return (window.spekI18n && window.spekI18n[value]) || value;
    };
    const norm = function (value) {
        return String(value || '').toLocaleLowerCase('el-GR')
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/ς/g, 'σ').trim();
    };
    const escapeHTML = function (value) {
        const element = document.createElement('div');
        element.textContent = String(value || '');
        return element.innerHTML;
    };
    const validCoordinate = function (lat, lng) {
        return Number.isFinite(lat) && Number.isFinite(lng)
            && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180;
    };

    let map = null;
    let popup = null;
    let clusterer = null;
    let markers = [];
    let radiusCircle = null;
    let lastOrigin = null;
    let lastMode = 'all';
    let searchVersion = 0;
    let geocodeFailure = '';
    const geocodeCache = new Map();

    function isGreekPostcode(query) {
        return /^[0-9]{5}$/.test(String(query).trim());
    }

    // Let the listings keep working even if the optional Geocoding API is
    // disabled or some partners have not yet been geocoded by the importer.
    function localSearchOrigin(items, query) {
        const normalized = norm(query);
        const candidates = items.filter(function (partner) {
            return partner.lat !== null && partner.lng !== null
                && (isGreekPostcode(query)
                    ? partner.postcode.replace(/\s/g, '') === String(query).trim()
                    : norm(partner.area).includes(normalized));
        });
        if (!candidates.length) {
            return null;
        }
        return {
            lat: candidates.reduce(function (sum, partner) { return sum + partner.lat; }, 0) / candidates.length,
            lng: candidates.reduce(function (sum, partner) { return sum + partner.lng; }, 0) / candidates.length
        };
    }

    const partners = cards.map(function (card) {
        const postcode = String(card.dataset.postcode || '').replace(/\s+/g, '');
        let lat = Number.parseFloat(card.dataset.lat || '');
        let lng = Number.parseFloat(card.dataset.lng || '');
        if (!validCoordinate(lat, lng)) {
            const url = card.dataset.mapsUrl || '';
            // Google /@ coordinates are a camera position and are intentionally ignored.
            let found = url.match(/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/);
            if (!found) {
                found = url.match(/[?&](?:q|query|ll|destination)=(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)/);
            }
            if (found) {
                lat = Number.parseFloat(found[1]);
                lng = Number.parseFloat(found[2]);
            }
        }
        const globallyValid = validCoordinate(lat, lng);
        // Bad TomTom/old importer geocodes have placed Greek stores in the USA.
        // Hide the bad marker, not the partner or their contact details.
        const wrongCountry = globallyValid && /^[0-9]{5}$/.test(postcode)
            && !inGreece(lat, lng);
        if (wrongCountry) {
            card.dataset.pinWarning = 'outside-greece';
        }
        return {
            id: card.dataset.partnerId || '',
            title: card.dataset.title || '',
            area: card.dataset.area || '',
            postcode: postcode,
            address: card.dataset.address || '',
            searchable: norm(card.dataset.search || ''),
            mapsUrl: card.dataset.mapsUrl || '',
            placeId: card.dataset.placeId || '',
            lat: globallyValid && !wrongCountry ? lat : null,
            lng: globallyValid && !wrongCountry ? lng : null,
            card: card,
            marker: null
        };
    });

    function setStatus(message) {
        if (statusElement) {
            statusElement.textContent = message;
        }
    }

    function radiusKm() {
        const value = Number.parseFloat(radiusSelect ? radiusSelect.value : '10');
        return Number.isFinite(value) ? value : 10;
    }

    function distanceKm(origin, partner) {
        if (!origin || partner.lat === null || partner.lng === null) {
            return Infinity;
        }
        const radians = Math.PI / 180;
        const dLat = (partner.lat - origin.lat) * radians;
        const dLng = (partner.lng - origin.lng) * radians;
        const a = Math.sin(dLat / 2) ** 2
            + Math.cos(origin.lat * radians) * Math.cos(partner.lat * radians)
            * Math.sin(dLng / 2) ** 2;
        return 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function shopDirections(partner) {
        const params = new URLSearchParams({api: '1'});
        if (partner.placeId) {
            params.set('destination', [partner.title, partner.address, partner.area].filter(Boolean).join(', '));
            params.set('destination_place_id', partner.placeId);
        } else if (partner.lat !== null && partner.lng !== null) {
            params.set('destination', partner.lat + ',' + partner.lng);
        } else {
            params.set('destination', [partner.title, partner.address, partner.area].filter(Boolean).join(', '));
        }
        return 'https://www.google.com/maps/dir/?' + params.toString();
    }

    function pinSVG() {
        const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="49" viewBox="0 0 40 49">'
            + '<path d="M20 2C10 2 2 10 2 20c0 13 18 27 18 27s18-14 18-27C38 10 30 2 20 2z" fill="#0f4c81" stroke="#fff" stroke-width="3"/>'
            + '<circle cx="20" cy="20" r="7" fill="#fff"/></svg>';
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
            size: new google.maps.Size(40, 49),
            scaledSize: new google.maps.Size(40, 49),
            anchor: new google.maps.Point(20, 47)
        };
    }

    function focusPartnerCard(partner) {
        const card = partner.card;
        if (resultsElement) {
            const rect = resultsElement.getBoundingClientRect();
            const item = card.getBoundingClientRect();
            resultsElement.scrollTop += item.top - rect.top - 16;
        } else {
            card.scrollIntoView({block: 'nearest', behavior: 'smooth'});
        }
        card.classList.add('is-highlighted');
        window.setTimeout(function () { card.classList.remove('is-highlighted'); }, 1300);
    }

    function openPartner(partner, highlightCard) {
        if (!popup || !map || !partner.marker) {
            return;
        }
        const details = [partner.address, partner.postcode, partner.area].filter(Boolean).join(', ');
        const content = '<div class="spek-google-popup"><strong>' + escapeHTML(partner.title) + '</strong>'
            + (details ? '<p>' + escapeHTML(details) + '</p>' : '')
            + '<a href="' + escapeHTML(shopDirections(partner)) + '" target="_blank" rel="noopener noreferrer">'
            + escapeHTML(text('Οδηγίες')) + '</a></div>';
        popup.setContent(content);
        popup.open({map: map, anchor: partner.marker, shouldFocus: false});
        if (highlightCard) {
            focusPartnerCard(partner);
        }
    }

    function clearMarkers() {
        if (clusterer) {
            clusterer.clearMarkers();
            clusterer.setMap(null);
            clusterer = null;
        }
        markers.forEach(function (marker) { marker.setMap(null); });
        markers = [];
        partners.forEach(function (partner) { partner.marker = null; });
        if (radiusCircle) {
            radiusCircle.setMap(null);
            radiusCircle = null;
        }
        if (popup) {
            popup.close();
        }
    }

    function showPartners(matches, origin) {
        if (!map) {
            return;
        }
        clearMarkers();
        const bounds = new google.maps.LatLngBounds();
        let points = 0;
        const icon = pinSVG();
        const visible = new Set(matches);
        partners.forEach(function (partner) {
            partner.card.classList.toggle('is-hidden', !visible.has(partner));
        });

        matches.forEach(function (partner) {
            if (partner.lat === null || partner.lng === null) {
                return;
            }
            const position = {lat: partner.lat, lng: partner.lng};
            const marker = new google.maps.Marker({
                position: position,
                title: partner.title,
                icon: icon
            });
            marker.addListener('click', function () { openPartner(partner, true); });
            partner.marker = marker;
            markers.push(marker);
            bounds.extend(position);
            points += 1;
        });

        // Use the Google Maps open-source clusterer when available; gracefully
        // fall back to individual markers if the CDN is blocked.
        if (window.markerClusterer && window.markerClusterer.MarkerClusterer) {
            try {
                clusterer = new window.markerClusterer.MarkerClusterer({
                map: map,
                markers: markers,
                renderer: {
                    render: function (cluster) {
                        const number = cluster.count;
                        const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">'
                            + '<circle cx="25" cy="25" r="24" fill="#0f4c81" stroke="#fff" stroke-width="3"/>'
                            + '</svg>';
                        return new google.maps.Marker({
                            position: cluster.position,
                            label: {text: String(number), color: '#ffffff', fontSize: '13px', fontWeight: '700'},
                            icon: {
                                url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                                scaledSize: new google.maps.Size(50, 50)
                            },
                            zIndex: 1000 + number
                        });
                    }
                }
                });
            } catch (clusterError) {
                // Never fail the entire map just because clustering is unavailable.
                console.warn('SPEK: map clustering unavailable, using standard markers', clusterError);
                clusterer = null;
                markers.forEach(function (marker) { marker.setMap(map); });
            }
        } else {
            markers.forEach(function (marker) { marker.setMap(map); });
        }

        if (origin) {
            radiusCircle = new google.maps.Circle({
                map: map,
                center: origin,
                radius: radiusKm() * 1000,
                strokeColor: '#0f4c81',
                strokeOpacity: 0.7,
                strokeWeight: 1.5,
                fillColor: '#0f4c81',
                fillOpacity: 0.08,
                clickable: false
            });
            bounds.extend(origin);
        }
        // Never auto-fit all published pins: one erroneous or international
        // location previously zoomed the entire locator out to the world.
        const fitSelection = !!origin || (matches.length > 0 && matches.length < partners.length);
        if (fitSelection && (points > 1 || (points > 0 && origin))) {
            map.fitBounds(bounds, 44);
            google.maps.event.addListenerOnce(map, 'idle', function () {
                if (map.getZoom() > 14) {
                    map.setZoom(14);
                }
            });
        } else if (fitSelection && points === 1) {
            map.setCenter(bounds.getCenter());
            map.setZoom(15);
        } else if (origin) {
            map.setCenter(origin);
            map.setZoom(12);
        } else {
            map.setCenter(initialCenter);
            map.setZoom(initialZoom);
        }

        if (emptyElement) {
            emptyElement.hidden = matches.length > 0;
            if (!matches.length) {
                const heading = emptyElement.querySelector('h3');
                const description = emptyElement.querySelector('p');
                if (heading) {
                    heading.textContent = text('Δεν βρέθηκαν συνεργάτες');
                }
                if (description) {
                    description.textContent = text('Δοκιμάστε άλλη περιοχή ή μεγαλύτερη ακτίνα. Μπορείτε επίσης να πατήσετε Καθαρισμός για να δείτε όλους τους συνεργάτες.');
                }
            }
        }
        const missing = matches.length - points;
        let message = text('Βρέθηκαν %s σημεία πώλησης.').replace('%s', matches.length);
        if (missing > 0) {
            message += ' ' + text('%s δεν έχουν συντεταγμένες και δεν φαίνονται στον χάρτη.').replace('%s', missing);
        }
        setStatus(message);
        // The map container can change dimensions with responsive layouts.
        google.maps.event.trigger(map, 'resize');
    }

    function unique(items) {
        const ids = new Set();
        return items.filter(function (partner) {
            const identity = partner.id || partner.title;
            if (ids.has(identity)) {
                return false;
            }
            ids.add(identity);
            return true;
        });
    }

    function isGreece(query) {
        return ['ελλαδα', 'ελλασ', 'greece', 'ellada'].includes(norm(query));
    }

    function isForeign(partner) {
        return ['romania', 'oradea', 'bihor', 'skopje', 'macedonia']
            .some(function (word) {
                return norm([partner.title, partner.address, partner.area].join(' ')).includes(word);
            });
    }

    async function geocode(query) {
        geocodeFailure = '';
        const cacheKey = norm(query);
        if (geocodeCache.has(cacheKey)) {
            return geocodeCache.get(cacheKey);
        }

        try {
            const library = await google.maps.importLibrary('geocoding');
            const coder = new library.Geocoder();
            // Restrict to Greece without requiring an exact postal-code component
            // match. Google's postal area records sometimes omit that component.
            const postcode = isGreekPostcode(query);
            const requests = [{
                address: query + ', Ελλάδα',
                region: 'gr',
                componentRestrictions: {country: 'GR'}
            }];
            if (postcode) {
                requests.push({
                    address: query.slice(0, 3) + ' ' + query.slice(3) + ', Ελλάδα',
                    region: 'gr',
                    componentRestrictions: {country: 'GR'}
                });
            }
            for (const request of requests) {
                try {
                    const response = await coder.geocode(request);
                    const location = response.results && response.results[0]
                        && response.results[0].geometry.location;
                    if (!location) {
                        geocodeFailure = 'no_results';
                        continue;
                    }
                    const origin = {lat: location.lat(), lng: location.lng()};
                    if (!validCoordinate(origin.lat, origin.lng)
                        || !inGreece(origin.lat, origin.lng)) {
                        geocodeFailure = 'outside_greece';
                        continue;
                    }
                    geocodeCache.set(cacheKey, origin);
                    return origin;
                } catch (error) {
                    const message = String(error && (error.code || error.message || error));
                    // Only retry when the query has no results, not when an API
                    // key, quota or Google Cloud API setting is blocking us.
                    if (/ZERO_RESULTS/i.test(message)) {
                        geocodeFailure = 'no_results';
                        continue;
                    }
                    geocodeFailure = 'api_error';
                    console.warn('SPEK Geocoding request failed:', message);
                    break;
                }
            }
        } catch (error) {
            geocodeFailure = 'api_error';
            console.warn('SPEK Geocoding library unavailable:', error);
        }
        return null;
    }

    async function runSearch() {
        const version = ++searchVersion;
        const query = searchInput ? searchInput.value.trim() : '';
        lastMode = 'search';

        if (!query) {
            lastMode = 'all';
            showPartners(partners, null);
            return;
        }
        if (isGreece(query)) {
            showPartners(partners.filter(function (partner) { return !isForeign(partner); }), null);
            return;
        }

        setStatus(text('Γίνεται αναζήτηση...'));
        const matchesByText = partners.filter(function (partner) {
            return partner.searchable.includes(norm(query));
        });
        // Try accurate Google geocoding first; existing shop coordinates in
        // the same postcode/city provide a local fallback if Google is denied.
        const googleOrigin = await geocode(query);
        if (version !== searchVersion) {
            return;
        }
        const origin = googleOrigin || localSearchOrigin(matchesByText, query);
        lastOrigin = origin;

        let near = [];
        if (origin) {
            near = partners.filter(function (partner) {
                return distanceKm(origin, partner) <= radiusKm();
            }).sort(function (a, b) {
                return distanceKm(origin, a) - distanceKm(origin, b);
            });
        }
        const matches = unique(near.concat(matchesByText));
        if (matches.length) {
            showPartners(matches, origin);
            if (!googleOrigin && origin) {
                setStatus(
                    text('Η Google δεν επέστρεψε ακριβή τοποθεσία για την αναζήτηση. Εμφανίζονται συνεργάτες με βάση τις ήδη αποθηκευμένες τοποθεσίες.')
                );
            } else if (!origin) {
                setStatus(text('Βρέθηκαν %s συνεργάτες με αντιστοίχιση ονόματος, περιοχής ή ΤΚ. Η αναζήτηση ακτίνας δεν είναι διαθέσιμη χωρίς γεωγραφικό εντοπισμό.').replace('%s', matches.length));
            }
            return;
        }

        // Never leave the sales network completely invisible after a failed
        // geocoding/search. Explain the fallback rather than claiming that
        // no partners exist.
        if (!origin) {
            showPartners(partners, null);
            setStatus(
                geocodeFailure === 'api_error'
                    ? text('Η γεωγραφική αναζήτηση Google δεν είναι διαθέσιμη. Εμφανίζονται όλοι οι συνεργάτες. Ο διαχειριστής πρέπει να ελέγξει αν το Geocoding API είναι ενεργό και επιτρέπεται από το κλειδί Google Maps.')
                    : text('Δεν αναγνωρίστηκε η συγκεκριμένη περιοχή ή ο ΤΚ στην Google. Εμφανίζονται όλοι οι συνεργάτες· δοκιμάστε και το όνομα της περιοχής.')
            );
        } else {
            showPartners(partners, null);
            setStatus(text('Δεν βρέθηκαν επιβεβαιωμένες τοποθεσίες συνεργατών στην επιλεγμένη ακτίνα. Εμφανίζονται όλοι οι συνεργάτες. Δοκιμάστε μεγαλύτερη ακτίνα ή διαφορετική περιοχή.'));
        }
    }

    function nearMyLocation() {
        if (!navigator.geolocation) {
            setStatus(text('Ο browser δεν υποστηρίζει εντοπισμό τοποθεσίας.'));
            return;
        }
        const version = ++searchVersion;
        setStatus(text('Εντοπίζεται η θέση σας...'));
        navigator.geolocation.getCurrentPosition(function (position) {
            if (version !== searchVersion) {
                return;
            }
            const origin = {lat: position.coords.latitude, lng: position.coords.longitude};
            lastOrigin = origin;
            lastMode = 'location';
            const nearby = partners.filter(function (partner) {
                return distanceKm(origin, partner) <= radiusKm();
            }).sort(function (a, b) {
                return distanceKm(origin, a) - distanceKm(origin, b);
            });
            if (nearby.length) {
                showPartners(nearby, origin);
            } else {
                showPartners(partners, null);
                setStatus(text('Δεν υπάρχουν συνεργάτες με επιβεβαιωμένο pin στην επιλεγμένη ακτίνα. Εμφανίζονται όλοι οι συνεργάτες.'));
            }
        }, function () {
            if (version === searchVersion) {
                setStatus(text('Δεν δόθηκε πρόσβαση στην τοποθεσία. Συμπληρώστε περιοχή ή ΤΚ χειροκίνητα.'));
            }
        }, {enableHighAccuracy: false, timeout: 10000, maximumAge: 300000});
    }

    function setupWheelScroll() {
        const grid = document.querySelector('.store-locator-grid');
        if (!grid || !resultsElement) {
            return;
        }
        grid.addEventListener('wheel', function (event) {
            if (window.innerWidth <= 900 || event.target.closest('.partners-map')) {
                return;
            }
            const max = resultsElement.scrollHeight - resultsElement.clientHeight;
            if (max <= 0) {
                return;
            }
            const down = event.deltaY > 0;
            const up = event.deltaY < 0;
            if ((down && resultsElement.scrollTop < max - 2) || (up && resultsElement.scrollTop > 2)) {
                event.preventDefault();
                resultsElement.scrollTop += event.deltaY;
            }
        }, {passive: false});
    }

    let sdkLoadPromise = null;
    let failed = false;

    function showMapFailure(message) {
        if (failed) {
            return;
        }
        failed = true;
        mapElement.textContent = message;
        mapElement.classList.add('spek-map-error');
        partners.forEach(function (partner) {
            partner.card.classList.remove('is-hidden');
        });
        if (emptyElement) {
            emptyElement.hidden = true;
        }
        setStatus('Η λίστα συνεργατών είναι διαθέσιμη, αλλά ο χάρτης δεν φορτώθηκε.');
    }

    function loadMapsSDK() {
        if (window.google && window.google.maps &&
            typeof window.google.maps.importLibrary === 'function') {
            return Promise.resolve();
        }
        if (sdkLoadPromise) {
            return sdkLoadPromise;
        }

        sdkLoadPromise = new Promise(function (resolve, reject) {
            // Google recommends the callback, not the script 'load' event,
            // when using loading=async. Otherwise importLibrary can be called
            // before the Maps SDK is ready.
            const callbackName = '__spekGoogleMapsReady';
            let settled = false;
            const timeout = window.setTimeout(function () {
                finish(new Error('Google Maps SDK timed out after 20 seconds'));
            }, 20000);

            function finish(error) {
                if (settled) {
                    return;
                }
                settled = true;
                window.clearTimeout(timeout);
                try {
                    delete window[callbackName];
                } catch (ignored) {
                    window[callbackName] = undefined;
                }
                if (error) {
                    reject(error);
                } else {
                    resolve();
                }
            }

            window[callbackName] = function () {
                if (!window.google || !window.google.maps ||
                    typeof window.google.maps.importLibrary !== 'function') {
                    finish(new Error('Google Maps callback fired without the Maps library'));
                    return;
                }
                finish();
            };

            // Auth failures may occur after the SDK script has loaded. Surface
            // a practical message without hiding the partner list.
            const oldAuthFailure = window.gm_authFailure;
            window.gm_authFailure = function () {
                const error = new Error('Google Maps API key was rejected: check website referrers, API restrictions and billing');
                showMapFailure('Η Google απέρριψε το κλειδί χάρτη. Ελέγξτε ότι επιτρέπεται το https://spek.iluma.gr/*, ότι είναι ενεργό το Maps JavaScript API και το Billing.');
                if (typeof oldAuthFailure === 'function') {
                    oldAuthFailure();
                }
                finish(error);
            };

            const script = document.createElement('script');
            script.dataset.spekGoogleMaps = '1';
            const params = new URLSearchParams({
                key: key,
                v: 'weekly',
                loading: 'async',
                callback: callbackName,
                language: 'el',
                region: 'GR'
            });
            script.src = 'https://maps.googleapis.com/maps/api/js?' + params.toString();
            script.async = true;
            script.onerror = function () {
                finish(new Error('Google Maps SDK network request failed'));
            };
            document.head.appendChild(script);
        });
        return sdkLoadPromise;
    }

    async function init() {
        try {
            setStatus(text('Γίνεται φόρτωση σημείων πώλησης...'));
            await loadMapsSDK();
            // The Google callback confirms the SDK is ready. Load only the
            // required maps library; Geocoding is requested on actual search.
            await Promise.all([
                google.maps.importLibrary('maps'),
                google.maps.importLibrary('marker')
            ]);
            if (failed) {
                return;
            }
            map = new google.maps.Map(mapElement, {
                center: initialCenter,
                zoom: initialZoom,
                minZoom: 3,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true,
                clickableIcons: false,
                gestureHandling: 'cooperative',
                styles: [
                    {featureType: 'poi.business', stylers: [{visibility: 'off'}]},
                    {featureType: 'transit', stylers: [{visibility: 'off'}]},
                    {featureType: 'water', elementType: 'geometry', stylers: [{color: '#d9e8f4'}]}
                ]
            });
            popup = new google.maps.InfoWindow({maxWidth: 310});
            partners.forEach(function (partner) {
                partner.card.addEventListener('mouseenter', function () {
                    if (partner.marker) {
                        openPartner(partner, false);
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
                    if (lastMode === 'location' && lastOrigin) {
                        ++searchVersion;
                        const nearby = partners.filter(function (partner) {
                            return distanceKm(lastOrigin, partner) <= radiusKm();
                        });
                        if (nearby.length) {
                            showPartners(nearby, lastOrigin);
                        } else {
                            showPartners(partners, null);
                            setStatus(text('Δεν υπάρχουν συνεργάτες με επιβεβαιωμένο pin στην επιλεγμένη ακτίνα. Εμφανίζονται όλοι οι συνεργάτες.'));
                        }
                    } else if (searchInput && searchInput.value.trim()) {
                        runSearch();
                    }
                });
            }
            if (locationButton) {
                locationButton.addEventListener('click', nearMyLocation);
            }
            if (resetButton) {
                resetButton.addEventListener('click', function () {
                    ++searchVersion;
                    if (searchInput) {
                        searchInput.value = '';
                    }
                    lastMode = 'all';
                    lastOrigin = null;
                    showPartners(partners, null);
                });
            }
            window.addEventListener('resize', function () {
                google.maps.event.trigger(map, 'resize');
            });
            setupWheelScroll();
            showPartners(partners, null);
        } catch (error) {
            showMapFailure('Ο χάρτης Google Maps δεν φορτώθηκε. Ελέγξτε τη σύνδεση, το API key, τους περιορισμούς domain και το Billing του Google Cloud.');
            if (window.console && window.console.error) {
                window.console.error('SPEK Google Maps initialization:', error);
            }
        }
    }
    init();
}());
