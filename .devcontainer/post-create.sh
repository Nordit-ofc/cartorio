#!/usr/bin/env bash

set -euo pipefail

cd /workspace/cartorios

if [[ ! -f .env ]]; then
    cp .env.example .env
    echo "Arquivo .env criado a partir de .env.example."
fi

sudo mkdir -p vendor storage/logs bootstrap/cache
sudo chown -R "$(id -u):$(id -g)" vendor storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

composer install --no-interaction --prefer-dist

if ! grep -Eq '^APP_KEY=.+$' .env; then
    php artisan key:generate --force --no-interaction
fi

php artisan optimize:clear

echo "Aguardando o MySQL..."
database_ready=false
for _ in {1..60}; do
    if MYSQL_PWD="${DB_PASSWORD}" mysqladmin ping -h "${DB_HOST}" -P "${DB_PORT}" -u "${DB_USERNAME}" --silent; then
        database_ready=true
        break
    fi
    sleep 2
done

if [[ "$database_ready" != true ]]; then
    echo "O MySQL não ficou disponível dentro do tempo esperado." >&2
    exit 1
fi

# Papéis, áreas de interesse e sysadmin (se SYSADMIN_EMAIL/SYSADMIN_PASSWORD estiverem no .env).
php artisan migrate --seed --force --no-interaction

echo "Ambiente preparado."
