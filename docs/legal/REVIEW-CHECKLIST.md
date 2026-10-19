# SPEK Legal Pages — release review (19 October 2026)

This branch is **review-only**, not a compliance sign-off. Four bilingual WordPress pages are created with `post_status=draft` when an administrator next visits WP Admin. Any existing pages are left untouched. Editorial content remains editable in WP (Greek main content; English in existing SPEK translated-content meta). Footer links appear only after publication. **No banner/CMP is claimed as installed.**

## Verified from current WordPress theme source

- `templates/template-contact.php` and `inc/contact-submissions.php`: mandatory first name, last name, email, phone, subject, message, request category, plus optional linked product fields; stored in private custom post type `spek_contact`; notification to SPEK and acknowledgement to sender via `wp_mail`.
- `template-parts/careers/application-form.php` and `inc/career-applications.php`: recruitment fields, compulsory CV, optional references; candidate data in admin-only `spek_job_app` posts, applicant uploads in a dedicated private directory with server-deny rules, email notifications and CV attachments.
- No automatic retention purge was observed in the above form-handling files; confirm retention policy and implement corresponding deletion for stored records/files, messages and backups.
- `inc/enqueue.php`: partner locator loads an external markerclusterer library from unpkg + Google Maps API if a browser API key is configured; otherwise Leaflet from unpkg and TomTom tiles/geocoding. `assets/js/store-locator-google.js` injects a Google Maps SDK script dynamically; `assets/js/store-locator.js` uses localStorage for partner-coordinate caching.
- The preview on the homepage uses inline SVG art, not the external interactive locator.
- The source code **cannot** establish all active cookies, third-party scripts, installed plugins, server logs, hosting provider, external analytics or runtime domains. Live site/browser/network auditing is indispensable.

## Release blockers — do NOT publish until resolved

1. Confirm exact legal entity name, registration number/GEMI, office address, email privacy contact (and DPO status if relevant), and correct telephone. Old public spek.gr materials and current theme agree broadly but require company approval.
2. Inventory active WordPress plugins, hosting, mailbox/SMTP service, backups, anti-spam/analytics/CDN integrations, and third-party processors, including processing contracts and transfer locations.
3. Audit actual cookies and similar storage using browser DevTools on first visit with a clean profile, on each form, on product pages, partner locator, in both languages, with/without accepted consent. Record cookie/storage item, domain/provider, purpose, duration and necessity, verifying across mobile and desktop.
4. Add and test a cookie consent platform with clear and equal-level **Accept / Reject / Preferences** controls, non-essential script gating, persistent settings access, consent logging and withdrawal. Do not permit Google Maps, Leaflet tiles or comparable external integrations to load in a manner inconsistent with the selected choice.
5. Define documented retention periods for contact enquiries, recruitment applications (including CV and attachments), email copies, security/server logs and backups. Implement actual deletion/export processes and verify end-to-end. Candidate form acknowledgement is **not** consent for future recruitment retention.
6. Review lawful bases, international transfers, rights/DSAR workflow, vendor agreements and all draft language with legal counsel.
7. Perform manual and automated live-site accessibility review (keyboard, screen readers, focus, contrast, reflow, forms, map alternative, catalogue viewer/PDFs and motion). Replace draft status statement with evidence-based findings and review date.
8. Set final revision dates in all four pages; test /en/ links, page templates, canonical URLs, contact form notices and responsive design.
9. Only then publish each WordPress page. Publishing makes its footer link appear automatically. Existing pages with matching slugs are never overwritten.

## Draft page slugs

- `/privacy-policy/`
- `/cookie-policy/`
- `/terms-of-use/`
- `/accessibility-statement/`

English paths are `/en/{slug}/` with existing theme routing.

## Notes

The statutory transparency information follows GDPR Articles 12–14. Greek cookie consent guidance: Hellenic Data Protection Authority Recommendations 1/2020; the Authority requires equal ease and equal prominence for acceptance and rejection. The European Accessibility Act does not automatically cover every informational B2B site; determine legal scope separately. We do not claim current WCAG conformance.
