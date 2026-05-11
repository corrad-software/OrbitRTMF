#!/bin/sh
set -e
cd /var/www/html

# Writable dirs (volumes / first boot)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

php artisan storage:link 2>/dev/null || true

if [ "${MIGRATE_ON_START:-}" = "true" ]; then
  php artisan migrate --force
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
