#!/bin/bash
# Setup MySQL database for HanViet backend (XAMPP / Homebrew MySQL)
set -e

DB_NAME="${DB_NAME:-hanviet}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASS:-}"
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"

echo "Creating database: $DB_NAME on $DB_HOST:$DB_PORT"

mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" ${DB_PASS:+-p"$DB_PASS"} -e "
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
"

cd "$(dirname "$0")"

if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate
fi

# Update .env for MySQL
if grep -q "^DB_CONNECTION=" .env; then
  sed -i.bak "s/^DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
  sed -i.bak "s/^DB_HOST=.*/DB_HOST=$DB_HOST/" .env
  sed -i.bak "s/^DB_PORT=.*/DB_PORT=$DB_PORT/" .env
  sed -i.bak "s/^DB_DATABASE=.*/DB_DATABASE=$DB_NAME/" .env
  sed -i.bak "s/^DB_USERNAME=.*/DB_USERNAME=$DB_USER/" .env
  sed -i.bak "s/^DB_PASSWORD=.*/DB_PASSWORD=$DB_PASS/" .env
  rm -f .env.bak
fi

composer install --no-interaction
php artisan migrate:fresh --seed --force

echo ""
echo "✓ MySQL ready: $DB_NAME"
echo "  Start API: php artisan serve"
echo "  Health:    curl http://localhost:8000/api/health"
