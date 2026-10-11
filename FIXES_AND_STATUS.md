# Codevora ERP: fixes and go-live status

## Verification status (read this first)
- All fixes were made by static review and have NOT been executed. See RUN_LOCALLY.md to run and test them. PHP was NOT available in the build sandbox,
  so the test suite was NOT executed. Run the checks in "Before going live" below.
- Brackets and `use App\...` imports were checked with a script. No PHP parser was run.

## Fixed: security and tenancy
- Default admin passwords removed. Seeders no longer create users with known passwords.
  Admin is created with `php artisan users:create-admin`.
- Demo credentials removed from the login page. `DevelopmentSeeder` runs only in `local`.
- Login: throttled (5/min), deactivated users (status != Active) cannot sign in.
- API: token login (`/api/login`, throttled), user management limited to own company.
- Product search: grouped where clause, so the company scope always applies.
- Calculation: products and designs are resolved inside the user's company only.
  Foreign IDs return a validation error. Costs are never taken from client input for them.
- Company settings: one row per company (`CompanySetting::forCompany`). No `::first()` fallbacks.
  Settings and product/material edits are restricted to Admin and Manager.
- Materials: the hardcoded company id `14` is removed.
- Customers: saved with the user's company. Phone and e-mail are unique per company.
- Dashboard API cache is per company.
- Stored XSS on the quotation edit page fixed (`Js::from`).

## Fixed: quotation workflow
- Approved quotations cannot be edited (controller and policy). Edit never changes status.
- Quotation numbers are server-generated per company (`QT-YYYYMMDD-NNNN`), unique index per company.
- Saving a quotation is transactional. If the PDF fails, the draft is removed.
- Quotation delete (drafts only) implemented.
- Approval blocks incomplete costing (zero-cost materials or zero item totals).
- Customer e-mail/phone validation rule corrected.
- Discount (flat or %), freight and installation are applied and shown in the UI.
- GST comes from company settings (`default_tax_percent`), not a hardcoded 18.
- Item overrides (brand, series, glass thickness, hardware brand, names) are validated and kept.
- Custom rate per sq.ft. keeps the margin consistent with the final line total.
- GET requests no longer change data (customer auto-heal removed).

## Fixed: costing and PDF accuracy
- Amount in words rewritten (Indian grouping, correct singular/plural, paise).
- Formula evaluation no longer uses `eval()` (`SafeMathEvaluator`).
  Unknown variables and invalid formulas are errors, not zero.
- BOM problems (missing material, inactive material, rule or unit error) are reported
  and block saving, instead of silently under-costing.
- Unit conversion is only allowed within one unit family (length, area, mass, count).
- Hardcoded fallbacks removed (profile series, handle type, hardware colour).
- Timezone set to Asia/Kolkata; dates use it.
- PDFs are stored on the private disk (`storage/app/quotation_pdfs`), not the public disk.
  Logos are read from storage, so PDFs do not need `storage:link`.
- DomPDF remote access and PHP execution disabled.
- BOM view reads only fields that exist (weight, unit, dimensions).
- SQL in BOM lookups is portable across MySQL and PostgreSQL.

## Added: client sharing
- Show page: WhatsApp (pre-filled message, customer number if known), e-mail draft
  (customer address if known) and "Copy Client Link".
- Client link: signed route `/share/quotations/{id}/pdf`, valid 30 days, no login.
  Tampering with the id or signature returns 403. Revoking early needs a code change (not built).
- Download PDF and Preview PDF already existed (logged-in users only).

## Fixed: PDF layout
- Items with several sizes get one drawing per size (in addition to the size breakdown row).

## Fixed: deployment
- Docker image: PHP 8.2, `.dockerignore` added, seeding removed from container start.
- `.env.example`: debug off, secure session cookies, error-level logging.
- Route `/` is a redirect (route cache works).

## Known limitations (not fixed here)
1. Framework is Laravel 9 (security support ended in Feb 2024). Upgrading to Laravel 11 or 12 is a separate, planned task.
2. Single-company assumption in `users:create-admin` (uses the first company).
3. Multi-company onboarding UI is not built (planned after go-live).
4. Old quotation PDFs on the public disk: run `php artisan quotations:purge-legacy-pdfs` (dry run), then add `--force`.

## Before going live
1. `composer install`, then `php artisan test` on a MySQL test database. Fix any failure.
2. Run migrations on a COPY of the production database first. The new unique indexes fail if
   duplicate customer codes, product codes, SKUs or quotation numbers already exist
   (check with a GROUP BY query).
3. `php artisan users:create-admin` on the server.
4. `php artisan storage:link` (only needed for showing logos in the web UI).
5. Enter company settings (GSTIN, bank details, terms) and default GST %.
6. Generate one quotation, check the PDF line by line against the reference PDF.
