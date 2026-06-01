#!/usr/bin/env bash
set -euo pipefail

APP_NAME="loan-manager"
RELEASE_DIR="release"
STAMP="$(date +%Y%m%d%H%M%S)"
ARCHIVE="${RELEASE_DIR}/${APP_NAME}-${STAMP}.tar.gz"

mkdir -p "${RELEASE_DIR}"

if [ ! -d vendor ]; then
    composer install --no-dev --optimize-autoloader
else
    composer install --no-dev --optimize-autoloader
fi

npm install
npm run build

php artisan config:clear
php artisan route:clear
php artisan view:clear

COPYFILE_DISABLE=1 tar \
    --exclude='.git' \
    --exclude='.env' \
    --exclude='node_modules' \
    --exclude='release' \
    --exclude='storage/logs/*.log' \
    --exclude='storage/framework/cache/data/*' \
    --exclude='storage/framework/sessions/*' \
    --exclude='storage/framework/views/*.php' \
    -czf "${ARCHIVE}" .

echo "Created ${ARCHIVE}"
