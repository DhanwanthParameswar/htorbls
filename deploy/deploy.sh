#!/usr/bin/env bash
# HTOR BLS demo site — initial server setup (lms.dhanwanth.com)
set -euo pipefail

DEPLOY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${DEPLOY_DIR}/.." && pwd)"
APP_DIR="${APP_DIR:-${REPO_ROOT}}"
REPO_URL="https://github.com/DhanwanthParameswar/htorbls.git"
DB_NAME="library"
DB_USER="htorbls"
NGINX_SITE="htorbls"
DEMO_USER="demo"
DEMO_PASS="${HTOR_DEMO_PASSWORD:-DemoPass123}"
SITE_PORT="8090"
GIT_USER="${SUDO_USER:-ubuntu}"

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Run as root: sudo bash $0"
  exit 1
fi

echo "==> Installing PHP-FPM and MariaDB (if not present)..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get install -y php-fpm php-mysql php-gd php-mbstring mariadb-server git

echo "==> Ensuring MariaDB is running..."
systemctl enable --now mariadb

echo "==> Generating database password..."
DB_PASS="$(openssl rand -base64 24 | tr -d '/+=' | head -c 24)"

echo "==> Creating database and user..."
mysql <<SQL
CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

mysql "${DB_NAME}" < "${DEPLOY_DIR}/schema.sql"

echo "==> Ensuring application at ${APP_DIR}..."
if [[ ! -d "${APP_DIR}/.git" ]]; then
  rm -rf "${APP_DIR}"
  git clone --depth 1 -b demo "${REPO_URL}" "${APP_DIR}"
else
  sudo -u "${GIT_USER}" git -c safe.directory="${APP_DIR}" -C "${APP_DIR}" fetch origin demo
  sudo -u "${GIT_USER}" git -c safe.directory="${APP_DIR}" -C "${APP_DIR}" checkout demo
  sudo -u "${GIT_USER}" git -c safe.directory="${APP_DIR}" -C "${APP_DIR}" pull origin demo
fi

mkdir -p "${APP_DIR}/images/books"
chown -R "${GIT_USER}:www-data" "${APP_DIR}"
find "${APP_DIR}" -type d -exec chmod 775 {} \;
find "${APP_DIR}" -type f -exec chmod 664 {} \;
chmod 775 "${APP_DIR}/images/books"

cat > "${APP_DIR}/config.local.php" <<PHP
<?php
\$username = "${DB_USER}";
\$user_pass = "${DB_PASS}";
PHP
chown www-data:www-data "${APP_DIR}/config.local.php"
chmod 640 "${APP_DIR}/config.local.php"

echo "==> Configuring nginx on port ${SITE_PORT}..."
cp "${DEPLOY_DIR}/nginx-htorbls.conf" "/etc/nginx/sites-available/${NGINX_SITE}"
ln -sf "/etc/nginx/sites-available/${NGINX_SITE}" "/etc/nginx/sites-enabled/${NGINX_SITE}"
nginx -t
systemctl reload nginx
systemctl enable --now php8.1-fpm

echo "==> Saving credentials..."
CREDS_FILE="${DEPLOY_DIR}/.credentials"
cat > "${CREDS_FILE}" <<CREDS
# HTOR BLS deployment credentials - keep private
Deployed: $(date -Iseconds)
URL: https://lms.dhanwanth.com
App path: ${APP_DIR}

Demo login:
  username: ${DEMO_USER}
  password: ${DEMO_PASS}

Database:
  name: ${DB_NAME}
  user: ${DB_USER}
  password: ${DB_PASS}
  host: localhost
CREDS
chmod 600 "${CREDS_FILE}"

echo "==> Loading demo library data..."
"${DEPLOY_DIR}/reseed-demo-db.sh"

echo ""
echo "=============================================="
echo " HTOR BLS deployed successfully"
echo "=============================================="
echo " App:     ${APP_DIR}"
echo " Deploy:  ${DEPLOY_DIR}"
echo " Public:  https://lms.dhanwanth.com"
echo " Login:   ${DEMO_USER} / ${DEMO_PASS}"
echo " Creds:   ${CREDS_FILE}"
echo ""
echo " Daily reseed: sudo cp ${DEPLOY_DIR}/cron-demo-reseed /etc/cron.d/htorbls-demo-reseed"
