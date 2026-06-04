#!/bin/sh
set -e
cd /var/www/html

# Auto-generate .env from Docker environment variables if not exists
#if [ ! -f .env ]; then
#  printenv | grep -v "^_\|^HOSTNAME\|^HOME\|^PATH\|^PWD\|^SHLVL\|^TERM" \
#    > .env
# echo ".env generated from environment variables"
#fi

# Writable dirs (volumes / first boot)
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache 2>/dev/null || true

php artisan storage:link 2>/dev/null || true

# Avoid stale route/config cache after redeploy (not the changelog file itself)
php artisan config:clear --no-ansi 2>/dev/null || true
php artisan route:clear --no-ansi 2>/dev/null || true
php artisan cache:clear --no-ansi 2>/dev/null || true
php artisan view:clear --no-ansi 2>/dev/null || true

if [ "${MIGRATE_ON_START:-}" = "true" ]; then
  php artisan migrate --force
fi

# Seed changelog from CHANGELOG.md when settings row is empty (survives redeploy)
php artisan changelog:ensure --no-ansi 2>/dev/null || true

exec /usr/bin/supervisord -c /etc/supervisord.conf
