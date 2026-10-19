# SPEK Legal Pages — release review (19 October 2026)

This branch is **review-only**, not a compliance sign-off. No GitHub Actions or workflow files are included. The optional test PHP scripts are manual tools, not scheduled jobs. Four bilingual WordPress pages are created with `post_status=draft` when an administrator next visits WP Admin. Any existing pages are left untouched. Editorial content remains editable in WP (Greek main content; English in existing SPEK translated-content meta). Footer links appear only after publication. **No banner/CMP is claimed as installed.**

## Verified from current WordPress theme source

- `templates/template-contact.php` and `inc/contact-submissions.php`: mandatory first name, last name, email, phone, subject, message, request category, plus optional linked product fields; stored in private custom post type `spek_contact`; notification to SPEK and acknowledgement to sender via `wp_mail`.
- `template-parts/careers/application-form.php` and `inc/career-applications.php`: recruitment fields, compulsory CV, optional references; candidate data in admin-only `spek_job_app` posts, applicant uploads in a dedicated private directory with server-deny rules, email notifications and CV attachments.
- User-confirmed policy: contact enquiries are retained for **12 months from submission**; recruitment applications, CVs and references for **9 months from submission**. Newsletter subscribers are independent of enquiry retention; only minimum required subscriber data survives while lawful subscription remains active.
- A daily WordPress cleanup job is prepared in `inc/data-retention.php`, **DISABLED by default**. It can only be enabled after legal, infrastructure and backup review via `define('SPEK_RETENTION_CLEANUP_ENABLED', true);` in `wp-config.php`. It deletes old `spek_contact` and `spek_job_app` records; the existing `before_delete_post` hook removes corresponding private CVs/references. It deliberately excludes records with `_spek_retention_hold=1`. Host/server cron reliability, backups, mailboxes and third-party subscriptions require separate checks and deletion mechanisms.
- Optional scientific/professional interest + separate (non-preselected) consent for other suitable roles is present in the proposed career form and saved to application metadata. Withdrawal handling via rights requests needs operational procedures.
- `inc/newsletter-interest.php` implements a provider-independent WordPress subscriber list under Contacts → Newsletter. On a **voluntary, unticked** checkbox in the contact form, the system stores email, first and last name, language, recorded opt-in time, source, consent-notice version/hash and sends a single verification email. Until verification the record is **pending** and never exported as an active subscriber.
- Single-use confirmation tokens expire after 7 days. Unconfirmed records are scheduled for deletion after 14 days via WP-Cron (verify real host cron). The email includes a long random withdrawal link; using that link deletes the local subscriber record. Admins can also delete it from WordPress.
- Admin CSV export requires `manage_options`, a WordPress nonce and exports **confirmed only**; spreadsheets are protected against CSV formula injection. Email-confirmation traffic is functional, **not a marketing newsletter send**. No actual external newsletter provider or sending campaign exists yet.
- Keep newsletter email/name/consent proof separately from the 12-month enquiry (including its message/phone). Do **not** automatically import all Contact entries as subscribers.

- `inc/enqueue.php`: partner locator loads an external markerclusterer library from unpkg + Google Maps API if a browser API key is configured; otherwise Leaflet from unpkg and TomTom tiles/geocoding. `assets/js/store-locator-google.js` injects a Google Maps SDK script dynamically; `assets/js/store-locator.js` uses localStorage for partner-coordinate caching.
- The preview on the homepage uses inline SVG art, not the external interactive locator.
- The source code **cannot** establish all active cookies, third-party scripts, installed plugins, server logs, hosting provider, external analytics or runtime domains. Live site/browser/network auditing is indispensable.

## Release blockers — do NOT publish until resolved

1. Confirm exact legal entity name, registration number/GEMI, office address, email privacy contact (and DPO status if relevant), and correct telephone. Old public spek.gr materials and current theme agree broadly but require company approval.
2. Inventory active WordPress plugins, hosting, mailbox/SMTP service, backups, anti-spam/analytics/CDN integrations, and third-party processors, including processing contracts and transfer locations. **The SPEK newsletter list is currently local inside the proposed theme; no provider selected.** Check email-confirmation deliverability and consent records. After selecting a platform, connect only confirmed subscribers, verify vendor contractual details and implement appropriate unsubscribe synchronisation. Consider renewed confirmation if substantial time passes.
3. Audit actual cookies and similar storage using browser DevTools on first visit with a clean profile, on each form, on product pages, partner locator, in both languages, with/without accepted consent. Record cookie/storage item, domain/provider, purpose, duration and necessity, verifying across mobile and desktop.
4. Add and test a cookie consent platform with clear and equal-level **Accept / Reject / Preferences** controls, non-essential script gating, persistent settings access, consent logging and withdrawal. Do not permit Google Maps, Leaflet tiles or comparable external integrations to load in a manner inconsistent with the selected choice.
5. **Confirmed retention policy:** 12 months for contact enquiries and 9 months for CV/applications. Test and explicitly enable the gated cleanup only after identifying and backing up any records that must legally be preserved. Verify email copies, logs and backups. Candidate form acknowledgement is **not** consent for future recruitment retention; test the distinct optional choice. Live production WP-Cron and deletion are **not yet validated**.
6. Review lawful bases, international transfers, rights/DSAR workflow, vendor agreements and all draft language with legal counsel.
7. Perform manual and automated live-site accessibility review (keyboard, screen readers, focus, contrast, reflow, forms, map alternative, catalogue viewer/PDFs and motion). Replace draft status statement with evidence-based findings and review date.
8. Set final revision dates in all four pages; test /en/ links, page templates, canonical URLs, contact form notices, newsletter confirmation/withdrawal/unconfirmed cleanup/CSV export, and responsive design.
9. Only then publish each WordPress page. Publishing makes its footer link appear automatically. Existing pages with matching slugs are never overwritten.

## Draft page slugs

- `/privacy-policy/`
- `/cookie-policy/`
- `/terms-of-use/`
- `/accessibility-statement/`

English paths are `/en/{slug}/` with existing theme routing.

## Notes

The statutory transparency information follows GDPR Articles 12–14. Greek cookie consent guidance: Hellenic Data Protection Authority Recommendations 1/2020; the Authority requires equal ease and equal prominence for acceptance and rejection. The European Accessibility Act does not automatically cover every informational B2B site; determine legal scope separately. We do not claim current WCAG conformance.
