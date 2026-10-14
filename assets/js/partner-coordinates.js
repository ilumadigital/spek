/**
 * SPEK WordPress admin: populate latitude & longitude when a Google Maps
 * URL containing /@lat,lng is pasted. No external API calls.
 *
 * /@lat,lng is the map camera centre; it may differ from the actual business
 * position (!3d...!4d...). Admins can adjust the fields manually afterward.
 */
(function () {
    'use strict';

    function extractCoordinates(value) {
        let url = String(value || '').trim();
        if (!url) { return null; }
        for (let i = 0; i < 3; i++) {
            try {
                url = decodeURIComponent(url);
            } catch (error) {
                break;
            }
        }
        const number = '(-?(?:[0-9]+(?:\\.[0-9]+)?|\\.[0-9]+))';
        const patterns = [
            {source: 'map_center', pattern: new RegExp('@' + number + ',' + number + '(?=,|/|\\?|&|$)', 'i')},
            {source: 'place', pattern: new RegExp('!3d' + number + '!4d' + number, 'i')},
            {source: 'query', pattern: new RegExp('[?&](?:q|query|ll|destination)=' + number + ',\\s*' + number + '(?=&|$)', 'i')}
        ];
        for (const item of patterns) {
            const matches = url.match(item.pattern);
            if (!matches) { continue; }
            const latitude = Number(matches[1]);
            const longitude = Number(matches[2]);
            if (!Number.isFinite(latitude) || !Number.isFinite(longitude)
                || latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) {
                continue;
            }
            return {lat: matches[1], lng: matches[2], source: item.source};
        }
        return null;
    }

    function attach(input) {
        const form = input.closest('form');
        if (!form) { return; }
        const latitudeInput = form.querySelector('input[name="partner_latitude"]');
        const longitudeInput = form.querySelector('input[name="partner_longitude"]');
        if (!latitudeInput || !longitudeInput) { return; }

        const message = document.createElement('small');
        message.className = 'description spek-partner-coordinates-hint';
        message.style.display = 'block';
        message.style.marginTop = '7px';
        input.insertAdjacentElement('afterend', message);

        function update(onlyEmpty) {
            const coords = extractCoordinates(input.value);
            if (!coords) {
                message.textContent = input.value.trim()
                    ? 'Δεν βρέθηκαν ενσωματωμένες συντεταγμένες στο URL. Συμπλήρωσε χειροκίνητα ή χρησιμοποίησε πλήρες link Google Maps.'
                    : '';
                return;
            }
            if (!onlyEmpty || (!latitudeInput.value.trim() && !longitudeInput.value.trim())) {
                latitudeInput.value = coords.lat;
                longitudeInput.value = coords.lng;
                latitudeInput.dispatchEvent(new Event('change', {bubbles: true}));
                longitudeInput.dispatchEvent(new Event('change', {bubbles: true}));
            }
            if (coords.source === 'map_center') {
                message.textContent = 'Αυτόματη συμπλήρωση από το @: ' + coords.lat + ', ' + coords.lng
                    + '. Προσοχή: είναι το κέντρο προβολής του χάρτη και μπορεί να διαφέρει από το pin καταστήματος.';
            } else {
                message.textContent = 'Συντεταγμένες από Google Maps: ' + coords.lat + ', ' + coords.lng + '.';
            }
        }

        // Work immediately on paste, typing or changing the Google URL.
        input.addEventListener('input', function () { update(false); });
        input.addEventListener('change', function () { update(false); });
        // For older partners with a saved URL but no pin, populate the empty
        // form fields when the editor is opened; nothing is saved until submit.
        if (input.value.trim() && (!latitudeInput.value.trim() && !longitudeInput.value.trim())) {
            update(true);
        }
    }

    document.querySelectorAll('input[name="partner_maps_url"]').forEach(attach);
}());
