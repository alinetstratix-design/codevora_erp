# Changelog

## Unreleased: multi-company accounts and bug fixes

### Added
- Super Admin role (platform owner) with a Companies console: create company and its first Admin, activate/deactivate companies, reset admin passwords.
- Company team management for Admins: add, edit (name, role, status), reset password. Roles: Admin, Manager, Staff.
- Temporary passwords for new accounts, shown once; users must change them at first sign-in.
- Forgot password and reset password pages (Laravel password broker).
- Change password page for every user.
- Immediate sign-out when a user or company is deactivated.
- `php artisan users:create-super-admin` (replaces `users:create-admin`).
- Tests for tenancy, roles, deactivation, temporary passwords and password reset.

### Fixed
- Logged-in users opening `/login` were redirected to `/home`, which does not exist (404). Now `/dashboard`.
- Users of an inactive company could still sign in through the API token endpoint.
- Settings menu was visible to Staff, who then got a 403. Now Admin and Manager only.
- Error flash messages (for example "Approved quotations cannot be edited") were not displayed. Now shown.
- Approve and edit could race: a quotation could be approved while being edited. Both now lock the row and re-check the status inside the transaction.
- Opening a quotation page regenerated its PDF on a GET request. The PDF is now created on save and served from storage.
- `users:create-admin` used the first company in the database, so a second company could not be bootstrapped correctly. Replaced by the Super Admin flow.
- Brand strings unified to Codevora AI.

### Changed
- Layout updated to Bootstrap 5.3.3 (stable).
- Landing page FAQ explains how accounts are created.

## Quotation review (v3)

### Fixed
- PDF footer (page number, contact phone, "powered by") was never rendered. DomPDF skipped the footer script because PHP execution was disabled. Enabled, with values escaped safely.
- Commercial terms, payment terms and service charges were hardcoded as defaults in the PDF, so another company's terms could be printed. Terms now come only from Company Settings. Template text lives in `config/quotation_defaults.php` and is copied into a company when it is created.
- The "Warranty / terms defined" approval check always passed because of those defaults. It now checks the company's actual terms.
- Footer brand changed from "Codevora Tech" to "Powered by Codevora AI".
- Approve could run on a Sent, Accepted or Rejected quotation and move it back to Approved. Approval now works only from Draft.

### Added
- Quotation lifecycle: Approved → Sent → Accepted or Rejected, with buttons on the quotation page. Draft quotations cannot be marked as Sent.
- Quotation list: search by quote number, client or project, and filter by status.
- Status colours on the list (Accepted green, Rejected red).

## Product specifications and PDF refresh (v4)

### Fixed
- Product specification claims are no longer printed as defaults. Wall thickness, glass make, locking, hinge, drainage, warranty, delivery timeline, inclusions and exclusions now come from Company Settings. Missing values print as "—".
- Removed brand-specific defaults: "CORA" (profile, hardware, supplier), "Saint-Gobain", "SS304", "Master Bedroom" location, and the profile dimension tables that belong to one manufacturer.
- Bank details no longer fall back to payment-term text ("50% Advance with Order").
- Company name fallback is "Codevora AI" (was "Codevora ERP").
- Draft quotations are rebuilt with the new settings the next time their PDF is downloaded or shared. Approved, sent, accepted and rejected quotations keep their snapshot.

### Added
- Company Settings: product specification fields, inclusions and exclusions.
- Approval check: "Product specifications and inclusions defined".
