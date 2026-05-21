#!/bin/sh
set -e

# ─── Phase 1: root setup (chown / mkdir on volumes) ───

# Copy public assets to the shared volume so nginx can serve them
cp -r /var/www/html/public-image/* /var/www/html/public/ 2>/dev/null || true

# Make storage + public + bootstrap/cache writable for www-data
chown -R www-data:www-data \
    /var/www/html/storage \
    /var/www/html/public \
    /var/www/html/bootstrap/cache
find /var/www/html/storage -type d -exec chmod 775 {} +
find /var/www/html/storage -type f -exec chmod 664 {} +
find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} +

# SQLite DB lives in the storage volume (survives image rebuilds)
DB_DIR=/var/www/html/storage/app/database
DB_FILE=$DB_DIR/database.sqlite
mkdir -p "$DB_DIR"
[ -f "$DB_FILE" ] || touch "$DB_FILE"
chown -R www-data:www-data "$DB_DIR"

# Symlink standard location → storage volume
mkdir -p /var/www/html/database
chown www-data:www-data /var/www/html/database
rm -f /var/www/html/database/database.sqlite
ln -sf "$DB_FILE" /var/www/html/database/database.sqlite
chown -h www-data:www-data /var/www/html/database/database.sqlite

# ─── Phase 2: drop privileges, finish setup, hand off ───

gosu www-data php artisan migrate --force
gosu www-data php artisan config:cache
gosu www-data php artisan route:cache
gosu www-data php artisan view:cache
gosu www-data php artisan event:cache
gosu www-data php artisan storage:link 2>/dev/null || true

# php-fpm master runs as www-data; workers inherit that uid (no setuid needed).
exec gosu www-data "$@"
