#!/usr/bin/env bash
# deploy.sh — flujo de deploy para Laravel + Docker
# Uso:
#   ./deploy.sh pull              # git pull + (nada más)
#   ./deploy.sh php               # rebuild app + migraciones + repoblar public + restart web
#   ./deploy.sh assets            # npm build en host + copiar public/ a volumen + copiar build/ a app + limpiar caches + restart web
#   ./deploy.sh nginx             # rebuild web (o reload) después de cambiar ops/nginx/app.conf
#   ./deploy.sh env               # recargar app por cambios en .env + recachear config
#   ./deploy.sh full              # pull + php + assets (todo)
# Variables:
#   PROJECT (default: arkend)
# Requisitos: docker compose, git, tar
set -euo pipefail

PROJECT="${PROJECT:-arkend}"

compose() {
  docker compose -p "$PROJECT" "$@"
}

app_cid() {
  # intenta por nombre de contenedor; si no, por servicio
  docker ps -qf "name=l_app" || true
}

ensure_tar() {
  command -v tar >/dev/null 2>&1 || { echo "ERROR: 'tar' es requerido en el host."; exit 1; }
}

pull_code() {
  echo "==> git pull --rebase"
  git pull --rebase
}

rebuild_app() {
  echo "==> Rebuild app (PHP-FPM)"
  compose up -d --build app
}

rebuild_web() {
  echo "==> Rebuild web (Nginx)"
  compose up -d --build web
}

migrate() {
  echo "==> Migraciones (prod)"
  compose exec app sh -lc "php artisan migrate --force"
}

clear_cache() {
  echo "==> Limpiar caches Laravel"
  compose exec app sh -lc "php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear || true"
}

recache() {
  echo "==> Cachear config/rutas/vistas"
  compose exec app sh -lc "php artisan config:cache && php artisan route:cache && php artisan view:cache || true"
}

build_assets_host() {
  echo "==> Build de assets (Vite) en host (node:18-bullseye)"
  docker run --rm -v "$PWD":/workspace -w /workspace node:18-bullseye sh -lc "npm ci --legacy-peer-deps && npm run build"
}

copy_public_to_volume() {
  echo '==> Copiar public/ -> volumen ${PROJECT}_app_public (para Nginx)'
  ensure_tar
  docker run --rm \
    -v "$PWD/public":/src:ro \
    -v "${PROJECT}_app_public":/dst \
    busybox sh -lc 'cd /src && tar cf - . | (cd /dst && tar xvf -)'
}

copy_build_to_app() {
  echo "==> Copiar public/build -> contenedor app (para manifest.json)"
  CID="$(app_cid)"
  if [ -z "$CID" ]; then
    echo "No encontré contenedor l_app. Intentando con servicio 'app'..."
    compose up -d app
    CID="$(docker ps -qf "name=l_app" || true)"
  fi
  if [ -z "$CID" ]; then
    echo "ERROR: no se pudo localizar el contenedor 'app' (l_app)."; exit 1;
  fi
  docker cp ./public/build "$CID":/var/www/html/public
}

restart_web() {
  echo "==> Restart web (Nginx)"
  compose restart web
}

case "${1:-}" in
  pull)
    pull_code
    ;;
  php)
    rebuild_app
    migrate
    recache
    ;;
  assets)
    build_assets_host
    copy_public_to_volume
    copy_build_to_app
    clear_cache
    restart_web
    ;;
  nginx)
    rebuild_web
    ;;
  env)
    echo "==> Recargando app por cambios en .env"
    compose up -d app
    clear_cache
    recache
    ;;
  full)
    pull_code
    rebuild_app
    migrate
    build_assets_host
    copy_public_to_volume
    copy_build_to_app
    recache
    restart_web
    ;;
  *)
    echo "Uso: $0 {pull|php|assets|nginx|env|full}"
    exit 1
    ;;
esac

echo "✅ Listo."
