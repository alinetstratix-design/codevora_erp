#!/bin/sh
set -e

# Port configuration for Render
PORT="${PORT:-80}"
sed -i "s/listen [0-9]*;/listen $PORT;/g" /etc/nginx/sites-available/default

# Set default DB environment variables if not provided
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_DATABASE="${DB_DATABASE:-/var/www/database/database.sqlite}"

# Create .env from .env.example if missing
if [ ! -f /var/www/.env ]; then
    echo "Creating .env file from .env.example..."
    cp /var/www/.env.example /var/www/.env
fi

# Ensure SQLite file and directory permissions exist
mkdir -p /var/www/database
touch /var/www/database/database.sqlite
chown -R www-data:www-data /var/www/database /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/database

# Clear any cached configs
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Generate APP_KEY if missing in .env and not in environment
if [ -z "$APP_KEY" ]; then
    if ! grep -q "^APP_KEY=base64:" /var/www/.env; then
        echo "Generating APP_KEY..."
        php artisan key:generate --force || true
    fi
else
    echo "APP_KEY provided by environment."
fi

# Run database migrations
php artisan migrate --force

# Seed reference lookups (safe/idempotent)
php artisan db:seed --force || true

# Ensure an admin user exists for demo on fresh install
php artisan tinker --execute="
if (\App\Models\User::count() === 0) {
    \$company = \App\Models\Company::firstOrCreate(['name' => 'Codevora ERP', 'is_active' => true]);
    \App\Models\User::create([
        'company_id' => \$company->id,
        'name' => 'Admin User',
        'email' => 'admin@codevora.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        'role' => 'Admin',
        'status' => 'Active',
    ]);
}
" || true
# Optimize caches for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure permissions after artisan commands
chown -R www-data:www-data /var/www/database /var/www/storage /var/www/bootstrap/cache

# Start Nginx service and PHP-FPM daemon
service nginx start
exec php-fpm
