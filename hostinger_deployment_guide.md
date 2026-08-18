# Hostinger Deployment Guide for Laravel

This document provides a comprehensive, step-by-step guide to deploying your Laravel 9 application (`codevora_erp`) to your Hostinger Premium Shared Hosting plan. 

Hostinger’s Premium Shared Hosting is excellent for Laravel. You don't have terminal/SSH access by default in the same way a VPS does, so the standard approach is to use the **File Manager** and **Database Manager** provided by Hostinger’s hPanel.

---

## Part 1: Local Preparation

Before uploading your files to Hostinger, you need to prepare your project locally.

### 1. Build Frontend Assets
Since your project uses Vite for frontend assets (based on `package.json`), you must compile them for production.

Run this command in your local project terminal (`c:\xampp\htdocs\codevora_erp`):
```bash
npm install
npm run build
```
This generates a `public/build/` directory containing your minified CSS and JS files.

### 2. Export Your Local Database
Export your current MySQL database from XAMPP (phpMyAdmin):
1. Open XAMPP phpMyAdmin (`http://localhost/phpmyadmin`).
2. Select your `codevora_erp` database.
3. Click the **Export** tab.
4. Keep the default settings and click **Export** to download the `.sql` file.

### 3. Create a ZIP Archive
You need to package your files to upload them efficiently.
1. Navigate to your project folder (`c:\xampp\htdocs\codevora_erp`).
2. **Exclude** the `node_modules` folder (it's not needed for production).
3. **Include** the `vendor` folder (so you don't have to run `composer install` on the server).
4. Select all files and folders, right-click, and **Compress to ZIP file** (e.g., `codevora_erp.zip`).

> [!TIP]
> Ensure the hidden `.env` file is included in your ZIP.

---

## Part 2: Hostinger hPanel Setup

### 1. Upload Your Files
1. Log in to your **Hostinger Account** and go to **hPanel**.
2. Click **Manage** next to your hosting plan.
3. Under the **Files** section, click **File Manager**.
4. Navigate to the `public_html` directory (this is your domain's root).
5. Click the **Upload** icon (top right) and upload your `codevora_erp.zip`.
6. Once uploaded, right-click the zip file and choose **Extract**. Choose `public_html` as the destination.

### 2. Adjust Directory Structure (Crucial for Security)
Laravel’s entry point is the `public` folder, but Hostinger expects files in `public_html`. To prevent exposing sensitive files (like `.env`), you need to adjust this.

**Option A (Recommended & Secure): Move files out of public_html**
1. In File Manager, create a new folder *outside* `public_html` (e.g., in the root directory alongside `public_html`). Name it `codevora_app`.
2. Move **all** your extracted Laravel files (except the `public` folder) into `codevora_app`.
3. Move the contents of your Laravel `public` folder directly into `public_html`.
4. Edit the `index.php` file inside `public_html` to point to the new location:
   ```php
   // Change this line (approx line 34):
   require __DIR__.'/../vendor/autoload.php';
   // To:
   require __DIR__.'/../codevora_app/vendor/autoload.php';
   
   // Change this line (approx line 47):
   $app = require_once __DIR__.'/../bootstrap/app.php';
   // To:
   $app = require_once __DIR__.'/../codevora_app/bootstrap/app.php';
   ```

### 3. Set Up the Database
1. Go back to your Hostinger hPanel.
2. Under the **Databases** section, click **MySQL Databases**.
3. Create a new MySQL database, user, and password. **Save these credentials.**
4. Go back to the Databases section and click **phpMyAdmin**.
5. Select the database you just created.
6. Click the **Import** tab and upload the `.sql` file you exported in Part 1.

---

## Part 3: Environment Configuration

You must update your production `.env` file to connect to the live database and reflect the production environment.

1. Open **File Manager** in Hostinger.
2. Navigate to where you placed your Laravel files (e.g., `codevora_app`).
3. Right-click the `.env` file and click **Edit**.
4. Update the following lines carefully:

```env
APP_NAME="Codevora ERP"
APP_ENV=production
APP_KEY=base64:YOUR_APP_KEY_HERE (Keep your existing generated key from local)
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1 (Leave as 127.0.0.1 or localhost on Hostinger)
DB_PORT=3306
DB_DATABASE=your_hostinger_db_name
DB_USERNAME=your_hostinger_db_user
DB_PASSWORD=your_hostinger_db_password
```

> [!WARNING]
> It is extremely important that `APP_DEBUG=false` on a live server. Leaving it `true` can expose sensitive database credentials if an error occurs.

---

## Part 4: Final Checks and Optimization

### File Permissions
Ensure the `storage` and `bootstrap/cache` directories have the correct permissions so Laravel can write to them.
1. In File Manager, right-click the `storage` directory -> **Permissions**. Set it to `775`.
2. Do the same for the `bootstrap/cache` directory.

### Storage Link
If you are uploading files (images, documents) via Laravel, you need a symbolic link. Since you can't run `php artisan storage:link` easily without SSH:
1. You can create a temporary route in `routes/web.php` to create the link:
   ```php
   Route::get('/create-symlink', function () {
       $target = storage_path('app/public');
       $link = public_path('storage');
       symlink($target, $link);
       return 'Symlink process completed';
   });
   ```
2. Visit `https://yourdomain.com/create-symlink` in your browser.
3. Delete the route from `web.php` after running it once.

### Cache Configuration (Optional but recommended)
Similarly, if you want to optimize your app by caching config and routes:
1. Create temporary routes to run artisan commands:
   ```php
   Route::get('/optimize', function() {
       Artisan::call('optimize:clear');
       Artisan::call('config:cache');
       Artisan::call('route:cache');
       Artisan::call('view:cache');
       return 'Optimized';
   });
   ```
2. Visit `https://yourdomain.com/optimize`.
3. Remove the route afterward.

Your Codevora ERP application should now be live on your Hostinger domain!
