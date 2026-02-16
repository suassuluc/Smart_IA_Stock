#!/bin/sh
set -e
cd /var/www

# Instalar dependências se vendor não existir
if [ ! -f vendor/autoload.php ]; then
  echo "Instalando dependências Composer..."
  composer install --no-interaction --prefer-dist
fi

exec php artisan serve --host=0.0.0.0 --port=8000
