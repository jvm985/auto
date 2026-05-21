#!/usr/bin/env bash
#
# One-time provisioning for auto.irishof.cloud.
# Run on the production server as root (or with sudo) after cloning to /opt/irishof/12-auto.
#
# Prerequisites on the host:
#   - Docker + docker compose
#   - nginx, certbot, python3-certbot-nginx
#
# Usage:
#   sudo /opt/irishof/12-auto/deploy/server-setup.sh
#
set -euo pipefail

APP_DIR="/opt/irishof/12-auto"
DOMAIN="auto.irishof.cloud"
EMAIL="admin@irishof.cloud"

step() { printf '\n\033[1;36m═══ %s ═══\033[0m\n' "$*"; }
ok()   { printf '\033[1;32m✓\033[0m %s\n' "$*"; }
fail() { printf '\033[1;31m✗\033[0m %s\n' "$*" >&2; exit 1; }

[ -d "$APP_DIR" ] || fail "App not found at $APP_DIR — clone the repo there first."
cd "$APP_DIR"

step "Checking .env"
if [ ! -f .env ]; then
    cp .env.example .env
    sed -i 's|^APP_ENV=.*|APP_ENV=production|' .env
    sed -i 's|^APP_DEBUG=.*|APP_DEBUG=false|' .env
    sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" .env
    sed -i "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=https://$DOMAIN/auth/google/callback|" .env
    echo
    echo "⚠️  .env aangemaakt — vul GOOGLE_CLIENT_ID en GOOGLE_CLIENT_SECRET in, en run nogmaals."
    echo "    $APP_DIR/.env"
    exit 1
fi

step "Generating APP_KEY (if missing)"
if ! grep -qE '^APP_KEY=base64:.+' .env; then
    docker run --rm -v "$APP_DIR":/app -w /app composer:latest \
        sh -c "composer install --no-interaction --no-dev --quiet && php artisan key:generate --force"
    ok "APP_KEY generated"
fi

step "Building and starting containers"
docker compose up -d --build

step "Installing host nginx vhost (HTTP only — certbot will add TLS)"
cp "$APP_DIR/deploy/nginx.conf" /etc/nginx/sites-available/auto
ln -sf /etc/nginx/sites-available/auto /etc/nginx/sites-enabled/auto
nginx -t
systemctl reload nginx

step "Requesting Let's Encrypt certificate"
certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "$EMAIL" --redirect

step "Installing final proxy vhost (HTTPS)"
cp "$APP_DIR/deploy/nginx-proxy.conf" /etc/nginx/sites-available/auto
nginx -t
systemctl reload nginx

step "Smoke test"
sleep 2
HTTP_CODE=$(curl -sk -o /dev/null -w '%{http_code}' "https://$DOMAIN/login" || echo "0")
if [ "$HTTP_CODE" = "200" ]; then
    ok "https://$DOMAIN/login responded HTTP 200"
else
    fail "https://$DOMAIN/login responded HTTP $HTTP_CODE"
fi

printf '\n\033[1;32m✅ Server klaar — app draait op https://%s\033[0m\n' "$DOMAIN"
