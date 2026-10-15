# Codevora AI: Quotation ERP for uPVC and Aluminium Windows & Doors

Laravel 9 application. Multi-company SaaS: one platform owner (Super Admin) creates companies.
Each company has its own Admin, who manages its own team and has access to every ERP feature
for that company.

## Roles

| Role        | Scope      | Can do                                                                  |
|-------------|------------|-------------------------------------------------------------------------|
| SuperAdmin  | Platform   | Create companies and their first Admin, activate/deactivate companies, reset admin passwords |
| Admin       | Company    | Everything in the company: team, settings, products, master data, quotations, approvals |
| Manager     | Company    | Products, master data, settings, quotations, approvals                  |
| Staff       | Company    | Customers and quotations (create, edit drafts, share PDFs)              |

Accounts created by an admin get a temporary password, shown once. The user must change it at first sign-in.
Deactivating a company or a user signs them out on their next request.

## Requirements
- PHP 8.1 or 8.2: pdo_mysql (or pdo_pgsql), mbstring, gd, zip, dom, xml, bcmath, fileinfo, tokenizer, ctype, json, openssl, curl
- MySQL 8 / MariaDB 10.5+ (shared hosting) or PostgreSQL (Render)
- Composer 2

## Local setup
```
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed                   # lookup values
php artisan users:create-super-admin  # the platform owner (once)
php artisan storage:link
php artisan serve
```
Then sign in as the Super Admin, open **Companies**, and create the first company.

Optional demo data for one company: `php artisan db:seed --class=ProductionMasterSeeder`.

## Tests
```
php artisan test
```
Use a separate test database (see `phpunit.xml`). The test run wipes it.

## Deployment
See `DEPLOY.md` for the step-by-step guide (shared hosting or Render).

## Security notes
- No credentials are stored in this repository. Set them in the host's environment or `.env` only.
- Quotation PDFs are stored on the private disk (`storage/app/quotation_pdfs`).
- Shared client links are signed and expire after 30 days.
- Login, password reset and token login are rate limited.

## Project principles
See `agent.md`.
