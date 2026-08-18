# Setup & Deployment Guide

This document contains step-by-step instructions to set up the project on a completely new system from scratch and how to deploy it to a live web hosting environment.

---

## Part 1: Local System Setup (From Scratch)

Since the new system has nothing installed, you need to install the necessary software first. This project is built with Laravel 9, requiring PHP 8.0+.

### 1. Prerequisites (Software to Install)
1. **XAMPP / WAMP (for Windows) or MAMP (for Mac)**: 
   - Download and install XAMPP with PHP version 8.0 or 8.1.
   - Start the **Apache** and **MySQL** modules from the XAMPP Control Panel.
2. **Composer**: 
   - Download and install [Composer](https://getcomposer.org/) (Dependency Manager for PHP).
3. **Node.js**:
   - Download and install [Node.js](https://nodejs.org/) (Required for frontend assets compilation).
4. **Git** (Optional but recommended):
   - Install Git if you are cloning the repository.

### 2. Project Setup
1. **Place the project files**: 
   - If using XAMPP, place your project folder (`fqmsapi-main`) inside the `htdocs` directory (e.g., `C:\xampp\htdocs\fqmsapi-main`).
2. **Open Terminal / Command Prompt**:
   - Navigate to the project root directory.
   - `cd C:\xampp\htdocs\fqmsapi-main`
3. **Install PHP Dependencies**:
   - Run the following command:
     ```bash
     composer install
     ```
4. **Install Node Modules & Compile Assets**:
   - Run the following commands:
     ```bash
     npm install
     npm run build
     ```
5. **Environment Configuration**:
   - Copy the `.env.example` file and rename it to `.env`.
   - You can also do this via command line:
     ```bash
     copy .env.example .env
     ```
6. **Generate Application Key**:
   - Run the following command to set the APP_KEY in your `.env` file:
     ```bash
     php artisan key:generate
     ```

### 3. Database Setup
1. Open your browser and go to `http://localhost/phpmyadmin`.
2. Create a new database (e.g., `fqms_db`).
3. Open the `.env` file in your project and update the database configuration:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fqms_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```
4. **Migrate the Database**:
   - Run the migrations to create tables:
     ```bash
     php artisan migrate
     ```
   - *(Optional)* If there are seeders, run: `php artisan db:seed`

### 4. Run the Application
1. Start the local development server:
   ```bash
   php artisan serve
   ```
2. Open your browser and visit `http://127.0.0.1:8000`.

---

## Part 2: Web Hosting Deployment (Live Setup)

Here are the steps to deploy this Laravel project to a standard web hosting (like cPanel/Shared Hosting).

### 1. Prepare Files for Upload
1. Open the project folder on your local machine.
2. Delete the `vendor` and `node_modules` folders (to reduce zip size, these will be installed on the server, or upload `vendor` if you don't have SSH access).
3. **Zip** all the project files into a file (e.g., `fqmsapi.zip`).

### 2. Upload to cPanel / Hosting
1. Login to your Web Hosting Control Panel (cPanel).
2. Go to **File Manager**.
3. It is recommended to upload your Laravel project files *outside* the `public_html` directory for security.
   - Create a folder named `fqms_app` in the root (same level as `public_html`).
   - Upload and extract `fqmsapi.zip` inside the `fqms_app` folder.
4. Open the `fqms_app/public` folder.
5. Move all contents of the `public` folder into your main `public_html` directory.

### 3. Update index.php Paths
1. Go to `public_html` and edit the `index.php` file.
2. Update the paths to point to your `fqms_app` directory:
   ```php
   // Change these lines:
   require __DIR__.'/../fqms_app/vendor/autoload.php';
   $app = require_once __DIR__.'/../fqms_app/bootstrap/app.php';
   ```

### 4. Set Up Live Database
1. In cPanel, go to **MySQL® Databases**.
2. Create a new database and a new database user.
3. Assign the user to the database and grant **All Privileges**.
4. Go to `fqms_app` and edit the `.env` file. Update the live database credentials:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=your_live_db_name
   DB_USERNAME=your_live_db_user
   DB_PASSWORD=your_live_db_password
   ```

### 5. Finalize Setup (Dependencies & Migrations)
**If you have SSH Access (Terminal in cPanel):**
1. Open Terminal in cPanel.
2. Navigate to your project folder: `cd fqms_app`
3. Run `composer install --optimize-autoloader --no-dev`
4. Run `php artisan migrate --force`
5. Run `php artisan config:cache` and `php artisan route:cache`

**If you DO NOT have SSH Access:**
1. You must upload the `vendor` folder from your local machine to the `fqms_app` folder on the server.
2. For the database, export your local database from `phpMyAdmin` (.sql file).
3. Import the .sql file into your live database via cPanel `phpMyAdmin`.

### 6. File Permissions
Ensure the following folders have proper write permissions (typically `775`):
- `storage/` and all subdirectories
- `bootstrap/cache/`

Your application should now be live on your domain!
