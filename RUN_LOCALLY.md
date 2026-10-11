# Run Codevora ERP on your computer (localhost)

## 1. Requirements
- PHP 8.1 or 8.2 with extensions: pdo_mysql, mbstring, gd, zip, dom, xml, bcmath, fileinfo, tokenizer, ctype, json, openssl, curl
- MySQL 8 (or MariaDB 10.5+), e.g. through XAMPP, Laragon, or MAMP
- Composer 2 (https://getcomposer.org)
- Git is optional

## 2. Install the project
Extract the zip, open a terminal in the `codevora_erp-main` folder, then run:

```
composer install
cp .env.example .env          (Windows: copy .env.example .env)
php artisan key:generate
```

Open `.env` and set:

```
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_DATABASE=fqms_db
DB_USERNAME=root
DB_PASSWORD=                  (your MySQL password, if any)
SESSION_SECURE_COOKIE=false
```

## 3. Create the two databases
In phpMyAdmin (or the MySQL shell) create:
- `fqms_db`       (the app)
- `fqms_db_test`  (automated tests; it is wiped by the test run, so never use it for real data)

Both with utf8mb4_unicode_ci collation.

## 4. Set up the app
```
php artisan migrate
php artisan db:seed                         # lookup values (dropdowns)
php artisan users:create-admin              # asks for name, email, password; creates the company too
php artisan db:seed --class=ProductionMasterSeeder   # demo products, designs, materials, BOM (optional, for testing)
php artisan storage:link                    # logos on the web pages
```

Tip for step `db:seed --class=ProductionMasterSeeder`: set `SEED_COMPANY_NAME`, `SEED_COMPANY_GSTIN`, etc. in `.env` first, or fill them later on the Company Settings page. It only runs on a company without master data.

## 5. Start the app
```
php artisan serve
```
Open http://127.0.0.1:8000 and sign in with the admin you created.

## 6. Run the automated tests
```
php artisan test
```
All tests should pass. If one fails, copy the test name and the error text, plus the last lines of `storage/logs/laravel.log`, and send them to the developer.

## 7. Manual test checklist
1. **Login:** wrong password 5 times within a minute should show a "too many attempts" error.
2. **Company Settings:** enter company name, GSTIN, bank details (one per line), terms (one per line), GST %. Upload a logo.
3. **Customers:** add a customer. It must appear in the list immediately.
4. **Create quotation:** choose the customer, add a product and a design, enter width/height in mm, add discount / freight / installation. The live total must update. Save.
5. **Show page:** check the validation score, the line items and the amount in words ("... Rupees ... Only").
6. **PDF:** click Preview PDF and Download PDF. Check the logo, bank details, GSTIN, totals and every item drawing (also for items with several sizes).
7. **Share:** click WhatsApp (opens WhatsApp Web with the message), Email (opens the mail app), and Copy Client Link. Paste the link in a private/incognito window: the PDF must open without login. Change one character in the link: it must show 403.
8. **Edit and approve:** edit a draft and save. Approve it (needs Admin or Manager; the score must be 100%). After approval the Edit button must disappear and the edit URL must be refused.
9. **Factory BOM:** open "Factory BOM" on a quotation. Weight and supplier columns must show real values where data exists.
10. **Delete:** delete a draft quotation. An approved quotation cannot be deleted.
11. **Roles:** create a Staff user for testing:
    ```
    php artisan tinker
    >>> App\Models\User::create(['company_id' => 1, 'name' => 'Staff Test', 'email' => 'staff@test.com', 'password' => bcrypt('Staff@12345'), 'role' => 'Staff', 'status' => 'Active'])
    ```
    Log in as this user. It must not open Products, Materials or Company Settings, and it must not approve quotations.
    (`company_id => 1` is the company created by `users:create-admin`; check with `App\Models\Company::all()` if unsure.)

## Troubleshooting
- **Login does not stick / redirects back to login:** `SESSION_SECURE_COOKIE` must be `false` on http, and run `php artisan config:clear`.
- **Blank page or 500:** set `APP_DEBUG=true`, reload, and read the error. Logs are in `storage/logs/laravel.log`.
- **Logo missing:** run `php artisan storage:link` (Windows: run the terminal as administrator if the link fails).
- **PDF fails with a font or GD error:** enable the `gd` extension in php.ini and restart the web server.
- **"Access denied" during tests:** check `DB_USERNAME`/`DB_PASSWORD` in `.env`; the test database is set in `phpunit.xml`.
