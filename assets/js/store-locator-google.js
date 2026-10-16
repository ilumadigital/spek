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
    let countryViewport = null;
    let countryLabel = '';
    let countryBoundariesPromise = null;
    const geocodeCache = new Map();

    function isGreekPostcode(query) {
        return /^[0-9]{5}$/.test(String(query).replace(/\s+/g, ''));
    }

    const partners = cards.map(function (card) {
        const postcode = String(card.dataset.postcode || '').replace(/\s+/g, '');
        let lat = Number.parseFloat(card.dataset.lat || '');
        let lng = Number.parseFloat(card.dataset.lng || '');
        let url = card.dataset.mapsUrl || '';
        try { url = decodeURIComponent(url); } catch (ignored) { /* keep raw URL */ }
        const place = url.match(/!3d(-?\d+(?:\.\d+)?)!4d(-?\d+(?:\.\d+)?)/);
        const center = url.match(/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)(?=,|\/|\?|&|$)/);
        const coordsNear = function (one, two) {
            return Number.isFinite(one) && Number.isFinite(two) && Math.abs(one - two) <= 0.000001;
        };

        // Immediately display the actual Google place pin for legacy records
        // whose saved coordinates still equal the old /@ camera location.
        // Manually changed coordinates that differ from /@ remain untouched.
        if (place) {
            const placeLat = Number.parseFloat(place[1]);
            const placeLng = Number.parseFloat(place[2]);
            const storedAtCamera = center
                && coordsNear(lat, Number.parseFloat(center[1]))
                && coordsNear(lng, Number.parseFloat(center[2]));
            if ((!validCoordinate(lat, lng) || storedAtCamera)
                && validCoordinate(placeLat, placeLng)) {
                lat = placeLat;
                lng = placeLng;
            }
        }
        if (!validCoordinate(lat, lng)) {
            const query = url.match(/[?&](?:q|query|ll|destination)=(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)/);
            if (query) {
                lat = Number.parseFloat(query[1]);
                lng = Number.parseFloat(query[2]);
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
            title: (card.dataset.title || '').toLocaleUpperCase('el-GR'),
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

    function setRadiusAvailability(enabled) {
        if (!radiusSelect) { return; }
        radiusSelect.disabled = !enabled;
        radiusSelect.title = enabled
            ? ''
            : 'Η αναζήτηση χώρας εμφανίζει όλους τους συνεργάτες χωρίς περιορισμό χιλιομέτρων.';
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


    // The same set of points controls map pins and cards. Moving/zooming
    // the map only filters the cards; no extra Google requests are made.
    let activePartners = [];
    let statusDetail = '';
    let mapReady = false;

    function syncVisibleCards() {
        if (!map || !mapReady) { return; }
        const bounds = typeof map.getBounds === 'function' ? map.getBounds() : null;
        if (!bounds || typeof bounds.contains !== 'function') { return; }
        const center = typeof map.getCenter === 'function' ? map.getCenter() : null;
        const focus = lastOrigin || (center ? {
            lat: typeof center.lat === 'function' ? center.lat() : center.lat,
            lng: typeof center.lng === 'function' ? center.lng() : center.lng
        } : initialCenter);
        const shown = activePartners.filter(function (partner) {
            return partner.lat !== null && partner.lng !== null
                && bounds.contains({lat: partner.lat, lng: partner.lng})
                && (!lastOrigin || distanceKm(lastOrigin, partner) <= radiusKm());
        }).sort(function (a, b) {
            return distanceKm(focus, a) - distanceKm(focus, b)
                || a.title.localeCompare(b.title, 'el');
        });

        const visible = new Set(shown);
        partners.forEach(function (partner) {
            partner.card.classList.toggle('is-hidden', !visible.has(partner));
        });
        if (resultsElement) {
            const fragment = document.createDocumentFragment();
            shown.forEach(function (partner) {
                let badge = partner.card.querySelector('[data-partner-distance]');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'partner-card__distance';
                    badge.setAttribute('data-partner-distance', '');
                    const header = partner.card.querySelector('.partner-card__header');
                    if (header) { header.appendChild(badge); }
                }
                if (badge) {
                    badge.textContent = distanceKm(focus, partner).toLocaleString('el-GR', {
                        minimumFractionDigits: 1, maximumFractionDigits: 1
                    }) + ' km';
                    badge.title = lastOrigin
                        ? 'Ευθεία απόσταση από το σημείο αναζήτησης'
                        : 'Ευθεία απόσταση από το κέντρο του χάρτη';
                }
                fragment.appendChild(partner.card);
            });
            resultsElement.appendChild(fragment);
        }

        if (emptyElement) {
            emptyElement.hidden = shown.length !== 0;
            if (!shown.length) {
                const heading = emptyElement.querySelector('h3');
                const description = emptyElement.querySelector('p');
                if (heading) { heading.textContent = 'Δεν υπάρχουν συνεργάτες στην ορατή περιοχή'; }
                if (description) {
                    description.textContent = lastOrigin
                        ? 'Δεν υπάρχουν καταστήματα που να βρίσκονται ταυτόχρονα εντός του χάρτη και της επιλεγμένης ακτίνας.'
                        : 'Μετακινήστε τον χάρτη ή μειώστε το zoom για να εμφανιστούν καταστήματα.';
                }
            }
        }
        setStatus(lastMode === 'country'
            ? 'Εμφανίζονται ' + shown.length + ' από ' + activePartners.length
                + ' καταστήματα στη χώρα ' + countryLabel
                + '. Η επιλογή ακτίνας δεν εφαρμόζεται.'
                + (statusDetail ? ' ' + statusDetail : '')
            : lastOrigin
            ? 'Εμφανίζονται ' + shown.length + ' από ' + activePartners.length
                + ' σημεία εντός ' + radiusKm()
                + ' km από την αναζήτηση, σε αύξουσα απόσταση ευθείας.'
                + (statusDetail ? ' ' + statusDetail : '')
            : 'Εμφανίζονται ' + shown.length + ' συνεργάτες εντός της ορατής περιοχής του χάρτη.'
                + (statusDetail ? ' ' + statusDetail : ''));
    }

    function showPartners(matches, origin) {
        if (!map) { return; }
        // Every new search starts from the closest visible result. Panning the
        // map never resets scroll position, because it only calls syncVisibleCards.
        if (resultsElement) { resultsElement.scrollTop = 0; }
        clearMarkers();
        lastOrigin = origin;
        activePartners = matches.filter(function (partner) {
            return partner.lat !== null && partner.lng !== null
                && (!origin || distanceKm(origin, partner) <= radiusKm());
        });
        const icon = pinSVG();
        activePartners.forEach(function (partner) {
            const marker = new google.maps.Marker({
                position: {lat: partner.lat, lng: partner.lng},
                title: partner.title,
                icon: icon
            });
            marker.addListener('click', function () { openPartner(partner, true); });
            partner.marker = marker;
            markers.push(marker);
        });

        if (window.markerClusterer && window.markerClusterer.MarkerClusterer) {
            try {
                clusterer = new window.markerClusterer.MarkerClusterer({
                    map: map,
                    markers: markers,
                    renderer: {
                        render: function (cluster) {
                            const count = cluster.count;
                            const svg = '<svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 50 50">'
                                + '<circle cx="25" cy="25" r="24" fill="#0f4c81" stroke="#fff" stroke-width="3"/></svg>';
                            return new google.maps.Marker({
                                position: cluster.position,
                                label: {text: String(count), color: '#ffffff', fontSize: '13px', fontWeight: '700'},
                                icon: {
                                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg),
                                    scaledSize: new google.maps.Size(50, 50)
                                },
                                zIndex: 1000 + count
                            });
                        }
                    }
                });
            } catch (error) {
                console.warn('SPEK: marker clustering unavailable', error);
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
            const circleBounds = typeof radiusCircle.getBounds === 'function'
                ? radiusCircle.getBounds() : null;
            if (circleBounds) {
                map.fitBounds(circleBounds, 36);
            } else {
                map.setCenter(origin);
                map.setZoom(11);
            }
        } else if (lastMode === 'country' && countryViewport) {
            // Frame the whole selected country and its partner pins, so the
            // initial list can show every partner across the country.
            try {
                const countryBounds = new google.maps.LatLngBounds();
                if (typeof countryBounds.union === 'function') {
                    countryBounds.union(countryViewport);
                }
                activePartners.forEach(function (partner) {
                    countryBounds.extend({lat: partner.lat, lng: partner.lng});
                });
                if (activePartners.length > 0 || typeof countryBounds.union === 'function') {
                    map.fitBounds(countryBounds, 30);
                } else {
                    map.fitBounds(countryViewport, 30);
                }
            } catch (error) {
                map.fitBounds(countryViewport, 30);
            }
        } else {
            map.setCenter(initialCenter);
            map.setZoom(initialZoom);
        }
        syncVisibleCards();
    }

    function isGreece(query) {
        return ['ελλαδα', 'ελλασ', 'greece', 'ellada'].includes(norm(query));
    }

    /**
     * Local country polygons (ISO-3166 alpha-2), fetched only for a country
     * search. Geocoder viewports are rectangles and include neighbouring
     * countries; never use viewport or radius as country membership.
     */
    function loadCountryBoundaries() {
        if (!countryBoundariesPromise) {
            const url = config.countryBoundariesUrl;
            if (!url) {
                return Promise.reject(new Error('country-boundaries-url-missing'));
            }
            countryBoundariesPromise = fetch(url, {credentials: 'same-origin'}).then(function (response) {
                if (!response.ok) { throw new Error('country-boundaries-http-' + response.status); }
                return response.json();
            }).then(function (data) {
                if (!data || data.type !== 'FeatureCollection' || !Array.isArray(data.features)) {
                    throw new Error('country-boundaries-invalid');
                }
                return data.features;
            }).catch(function (error) {
                countryBoundariesPromise = null;
                throw error;
            });
        }
        return countryBoundariesPromise;
    }

    function countryCodeFromResult(result, query) {
        const components = Array.isArray(result.address_components) ? result.address_components : [];
        const component = components.find(function (entry) {
            return Array.isArray(entry.types) && entry.types.includes('country');
        });
        const code = component && String(component.short_name || '').toUpperCase();
        if (code && /^[A-Z]{2}$/.test(code)) { return code; }
        const aliases = {
            ελλαδα: 'GR', ελλασ: 'GR', greece: 'GR', ellada: 'GR',
            ρουμανια: 'RO', romania: 'RO', κυπροσ: 'CY', κυπρο: 'CY',
            cyprus: 'CY', βουλγαρια: 'BG', bulgaria: 'BG',
            αλβανια: 'AL', albania: 'AL', ιταλια: 'IT', italy: 'IT',
            τουρκια: 'TR', turkey: 'TR', turkiye: 'TR',
            γερμανια: 'DE', germany: 'DE', γαλλια: 'FR', france: 'FR',
            ισπανια: 'ES', spain: 'ES', σερβια: 'RS', serbia: 'RS',
            μακεδονια: 'MK', northmacedonia: 'MK', 'north macedonia': 'MK',
            'united kingdom': 'GB', uk: 'GB', 'united states': 'US', usa: 'US'
        };
        return aliases[norm(query)] || '';
    }

    /** Ray casting over GeoJSON polygon, respecting holes and multipolygons. */
    function pointInRing(lat, lng, ring) {
        let inside = false;
        for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
            const a = ring[i], b = ring[j];
            if ((a[1] > lat) !== (b[1] > lat)) {
                const x = (b[0] - a[0]) * (lat - a[1]) / (b[1] - a[1]) + a[0];
                if (lng < x) { inside = !inside; }
            }
        }
        return inside;
    }

    function pointInPolygon(lat, lng, rings) {
        if (!rings || !rings.length || !pointInRing(lat, lng, rings[0])) {
            return false;
        }
        for (let i = 1; i < rings.length; i++) {
            if (pointInRing(lat, lng, rings[i])) { return false; }
        }
        return true;
    }

    function pointInCountry(partner, geometry, countryCode) {
        if (partner.lat === null || partner.lng === null) { return false; }
        // The bundled global boundary dataset is simplified. Small Greek
        // islands may be omitted. Greek five-digit postcodes + Greek envelope
        // cover those stores without including Cyprus (four-digit postcodes).
        if (countryCode === 'GR' && isGreekPostcode(partner.postcode)
            && inGreece(partner.lat, partner.lng)) {
            return true;
        }
        const parts = geometry.type === 'Polygon'
            ? [geometry.coordinates]
            : geometry.type === 'MultiPolygon' ? geometry.coordinates : [];
        return parts.some(function (polygon) {
            return pointInPolygon(partner.lat, partner.lng, polygon);
        });
    }

    async function getCountryPartners(countryCode) {
        const features = await loadCountryBoundaries();
        const feature = features.find(function (item) {
            return item.properties && item.properties.iso2 === countryCode;
        });
        if (!feature || !feature.geometry) {
            throw new Error('country-boundaries-unsupported-' + countryCode);
        }
        return partners.filter(function (partner) {
            return pointInCountry(partner, feature.geometry, countryCode);
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
            const postcode = isGreekPostcode(query);
            const zip = postcode ? query.replace(/\s+/g, '') : '';
            const countryQuery = isGreece(query)
                || /\b(?:greece|romania|cyprus|bulgaria|albania|italy|france|germany|turkey|north macedonia|serbia|uk|united kingdom)\b/i.test(query)
                || /(?:ελλαδ|ρουμαν|κυπρ|βουλγαρ|αλβαν|ιταλ|γαλλ|γερμαν|τουρκ|σερβ|μακεδον)/i.test(query);
            const requests = postcode
                ? [
                    {address: zip + ', Ελλάδα', region: 'gr',
                        componentRestrictions: {country: 'GR'}},
                    {address: zip.slice(0, 3) + ' ' + zip.slice(3) + ', Ελλάδα',
                        region: 'gr', componentRestrictions: {country: 'GR'}}
                ]
                : countryQuery
                    ? [{address: query, region: 'gr'}]
                    : [
                        {address: query + ', Ελλάδα', region: 'gr',
                            componentRestrictions: {country: 'GR'}},
                        {address: query, region: 'gr'}
                    ];
            for (const request of requests) {
                try {
                    const response = await coder.geocode(request);
                    const location = response.results && response.results[0]
                        && response.results[0].geometry.location;
                    if (!location) {
                        geocodeFailure = 'no_results';
                        continue;
                    }
                    const place = response.results[0];
                    const origin = {lat: location.lat(), lng: location.lng()};
                    const isCountry = Array.isArray(place.types) && place.types.includes('country');
                    if (isCountry) {
                        const countryCode = countryCodeFromResult(place, query);
                        if (countryCode) {
                            origin.countryCode = countryCode;
                            origin.countryName = (Array.isArray(place.address_components)
                                ? place.address_components.find(function (entry) {
                                    return Array.isArray(entry.types) && entry.types.includes('country');
                                }) : null)?.long_name || query;
                            origin.viewport = place.geometry.viewport || place.geometry.bounds || null;
                        }
                    }
                    if (!validCoordinate(origin.lat, origin.lng)
                        || (postcode && !inGreece(origin.lat, origin.lng))) {
                        geocodeFailure = 'no_results';
                        continue;
                    }
                    geocodeCache.set(cacheKey, origin);
                    return origin;
                } catch (error) {
                    const message = String(error && (error.code || error.message || error));
                    if (/ZERO_RESULTS/i.test(message)) {
                        geocodeFailure = 'no_results';
                        continue;
                    }
                    geocodeFailure = 'api_error';
                    console.warn('SPEK Google Geocoding:', message);
                    break;
                }
            }
        } catch (error) {
            geocodeFailure = 'api_error';
            console.warn('SPEK Geocoding library:', error);
        }
        return null;
    }

    async function runSearch() {
        const version = ++searchVersion;
        const query = searchInput ? searchInput.value.trim() : '';
        if (!query) {
            lastMode = 'all';
            setRadiusAvailability(true);
            countryViewport = null;
            countryLabel = '';
            statusDetail = '';
            showPartners(partners, null);
            return;
        }
        lastMode = 'search';
        setRadiusAvailability(true);
        countryViewport = null;
        countryLabel = '';
        setStatus('Εντοπίζεται το σημείο αναζήτησης...');
        const origin = await geocode(query);
        if (version !== searchVersion) { return; }

        if (!origin) {
            // Never show text matches or unrelated stores when the geographic
            // search has no valid zero point.
            lastOrigin = null;
            statusDetail = geocodeFailure === 'api_error'
                ? 'Η υπηρεσία γεωγραφικής αναζήτησης δεν είναι διαθέσιμη. Ελέγξτε το Geocoding API.'
                : 'Η τοποθεσία δεν αναγνωρίστηκε. Δοκιμάστε άλλη περιοχή, ΤΚ ή χώρα.';
            showPartners([], null);
            return;
        }
        if (origin.countryCode) {
            lastMode = 'country';
            setRadiusAvailability(false);
            countryLabel = origin.countryName || query;
            countryViewport = origin.viewport || null;
            setStatus('Εντοπίζονται όλα τα καταστήματα στη χώρα ' + countryLabel + '...');
            try {
                const matches = await getCountryPartners(origin.countryCode);
                if (version !== searchVersion) { return; }
                statusDetail = '';
                showPartners(matches, null);
            } catch (error) {
                if (version !== searchVersion) { return; }
                console.warn('SPEK country filtering:', error);
                statusDetail = 'Δεν ήταν δυνατή η φόρτωση των ορίων της χώρας. Δοκιμάστε ξανά.';
                showPartners([], null);
            }
            return;
        }
        countryViewport = null;
        countryLabel = '';
        statusDetail = '';
        showPartners(partners, origin);
    }

    function nearMyLocation() {
        if (!navigator.geolocation) {
            setStatus('Ο browser δεν υποστηρίζει εντοπισμό τοποθεσίας.');
            return;
        }
        const version = ++searchVersion;
        setStatus('Εντοπίζεται η θέση σας...');
        navigator.geolocation.getCurrentPosition(function (position) {
            if (version !== searchVersion) { return; }
            const origin = {lat: position.coords.latitude, lng: position.coords.longitude};
            lastMode = 'location';
            setRadiusAvailability(true);
            countryViewport = null;
            countryLabel = '';
            statusDetail = '';
            showPartners(partners, origin);
        }, function () {
            if (version === searchVersion) {
                setStatus('Δεν δόθηκε πρόσβαση στην τοποθεσία. Συμπληρώστε περιοχή ή ΤΚ χειροκίνητα.');
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
            // Synchronize cards on every pan, zoom, drag or map resize.
            map.addListener('idle', function () {
                mapReady = true;
                syncVisibleCards();
            });
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
                        showPartners(partners, lastOrigin);
                    } else if (lastMode === 'search' && searchInput && searchInput.value.trim()) {
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
                    setRadiusAvailability(true);
                    countryViewport = null;
                    countryLabel = '';
                    statusDetail = '';
                    showPartners(partners, null);
                });
            }
            window.addEventListener('resize', function () {
                google.maps.event.trigger(map, 'resize');
            });
            setupWheelScroll();
            mapReady = true;
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
