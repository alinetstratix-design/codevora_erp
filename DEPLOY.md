# Deployment guide: erp.codevoraai.com

This project runs on Laravel 9. The web root must be the `public` folder.

## 1. Build the package (on your computer)
```
composer install --no-dev --optimize-autoloader
```
Keep the generated `vendor/` folder. Shared hosts often have no Composer.

## 2. Create the database (hosting control panel)
1. Create a MySQL database and a user with full rights on it.
2. Note: database name, user, password, host (usually `localhost`).

## 3. Upload
1. Upload the project (including `vendor/`) outside `public_html`, for example `/home/USER/erp`.
2. Point the domain document root to `/home/USER/erp/public`
   (in the panel: Domains → erp.codevoraai.com → Document root).
3. Make `storage/` and `bootstrap/cache/` writable (permission 775).

## 4. Environment file
Copy `.env.example` to `.env` on the server and set:
```
APP_NAME="Codevora AI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://erp.codevoraai.com
APP_KEY=              # generated in step 5

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_SECURE_COOKIE=true
CACHE_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=...           # your hosting SMTP server
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@codevoraai.com
MAIL_FROM_NAME="Codevora AI"
```
Password reset e-mails need a working SMTP account. Without it, reset requests show an error.

## 5. Commands (SSH or the panel's terminal)
```
php artisan key:generate --force
php artisan migrate --force
php artisan db:seed --force
php artisan users:create-super-admin
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
If the host has no terminal, ask the hosting support to run these, or run them once on your computer
against a copy of the production database and upload the result.

## 6. First login
1. Open https://erp.codevoraai.com/login with the Super Admin email.
2. Go to **Companies → New company**. Enter the company and its admin.
3. Send the temporary password to the admin securely. The admin signs in, sets a new password,
   and adds the team under **Team**.

## 7. After going live
- Open **Company Settings** and enter GSTIN, bank details, terms and default GST %.
- Create one test quotation and check the PDF.
- Back up the database and the `storage/app/quotation_pdfs` folder daily (hosting backup or cron).
- Do not set `APP_DEBUG=true` on the live site.

## Troubleshooting
- **500 error:** check `storage/logs/laravel.log` (create the file if the folder is empty) and confirm `APP_KEY` is set.
- **Login loops or session lost:** keep `SESSION_SECURE_COOKIE=true` only on HTTPS. Run `php artisan config:clear`.
- **PDF or logo missing:** run `php artisan storage:link` and check the folder permissions.
- **Reset e-mail not received:** verify SMTP settings; check spam.

## Existing companies: fill commercial terms once
Companies created before this version have empty terms. Quotation approval is blocked until
the terms are filled, which is intended. To copy the template terms into an existing company:
```
php artisan tinker
>>> $s = App\Models\CompanySetting::where('company_id', 1)->first();
>>> $s->terms_conditions = config('quotation_defaults.terms');
>>> $s->installation_prerequisites = config('quotation_defaults.installation_prerequisites');
>>> $s->save();
```
Then review the text in Company Settings and replace it with the company's own terms.
Quotations saved before this change keep their stored terms. Re-save a draft to apply new terms.

## Product specifications (required before approving quotations)
Each company enters its own item specifications in **Company Settings → Product Specifications**
(wall thickness, glass make, warranty, delivery timeline, and so on) and at least one inclusion.
Until these are filled, the approval checklist shows "Product specifications and inclusions defined" as failed.
Product-level details (profile brand, glass type, hardware brand, handle) come from each product's
configuration. If a product has no profile details, its PDF rows show "—".
