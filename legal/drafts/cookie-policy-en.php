<?php
if (!defined('ABSPATH')) { exit; }
return <<<'SPEK_LEGAL_HTML'
<p class="spek-pending"><strong>Verification needed before publication:</strong> a browser-level audit of cookies and localStorage, both before and after any consent; a review of active WordPress plugins and hosting; and an accurate inventory of cookie names, domains, providers, purposes and lifetimes. This policy alone does not implement a consent mechanism.</p>
<h2>1. Cookies and similar technology</h2>
<p>Cookies are small files stored by your browser. Local storage and other technologies can also retain information on your device. Some tools are technically essential, while others require prior consent under applicable law.</p>
<h2>2. Technology found in the SPEK theme</h2>
<p>The website uses WordPress, contact and careers forms, product information and a partner locator. The partner locator is designed to use <strong>Google Maps</strong> when a browser API key has been configured, or alternatively <strong>Leaflet</strong> with <strong>TomTom</strong> mapping services and code loaded from <strong>unpkg</strong>. Google Maps is loaded dynamically from an external provider when the feature is used. The fallback map can request map tiles and geocoding data from external services.</p>
<p>In the fallback partner locator, localStorage can be used to cache partner coordinates on the visitor's device. This should not automatically be described as advertising tracking.</p>
<p>Reviewing the theme source cannot identify every cookie potentially created by WordPress plugins, analytics products, the hosting platform or third-party services.</p>
<h2>3. Categories</h2>
<table><thead><tr><th>Category</th><th>Purpose</th><th>Consent</th></tr></thead><tbody>
<tr><td>Strictly necessary</td><td>Essential site operation, session management and security where used.</td><td>Typically exempt, where statutory conditions are met.</td></tr>
<tr><td>Third-party functionality</td><td>Interactive maps, mapping requests and external libraries.</td><td>To be assessed; non-essential trackers must not load before the required consent.</td></tr>
<tr><td>Analytics or marketing</td><td>Potential analytics or advertising services if installed separately.</td><td>Prior consent where required.</td></tr>
</tbody></table>
<h2>4. Detailed cookie inventory</h2>
<p><strong>[Pending live technical audit: exact cookie/storage identifiers, provider, domain, purpose, duration, category and consent requirement.]</strong> No fabricated cookie identifiers are presented.</p>
<h2>5. Managing your preferences</h2>
<p>For technologies requiring consent, visitors must be able to freely accept or reject them with equal ease, and withdraw consent afterwards. <strong>[A tested consent interface and permanent “Cookie settings” link must be in place before publication.]</strong></p>
<h2>6. Browser settings and contact</h2>
<p>You can also manage stored website data using your browser's settings; removing essential information can affect website functions. Questions may be sent to <a href="mailto:spek@spek.gr">spek@spek.gr</a>. <strong>[Last-updated date pending.]</strong></p>
SPEK_LEGAL_HTML;
