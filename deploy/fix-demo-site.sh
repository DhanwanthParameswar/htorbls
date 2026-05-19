#!/usr/bin/env bash
# Fix config.local.php + MySQL after a bad deploy/switch.
set -euo pipefail

DEPLOY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${DEPLOY_DIR}/.." && pwd)"
APP_DIR="${APP_DIR:-${REPO_ROOT}}"
CREDS="${DEPLOY_DIR}/.credentials"
DB_USER="htorbls"
GIT_USER="${SUDO_USER:-ubuntu}"

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Run as root: sudo bash $0"
  exit 1
fi

BACKUP_DIR="$(ls -d /var/www/htorbls.bak.* 2>/dev/null | tail -1 || true)"

echo "==> Syncing config.local.php..."
if [[ -f "${CREDS}" ]]; then
  DB_PASS="$(awk -F': ' '/^  password:/{print $2}' "${CREDS}")"
  cat > "${APP_DIR}/config.local.php" <<PHP
<?php
\$username = "${DB_USER}";
\$user_pass = "${DB_PASS}";
PHP
  echo "Wrote ${APP_DIR}/config.local.php from ${CREDS}"
elif [[ -n "${BACKUP_DIR}" && -f "${BACKUP_DIR}/config.local.php" ]]; then
  cp -a "${BACKUP_DIR}/config.local.php" "${APP_DIR}/config.local.php"
  echo "Restored from ${BACKUP_DIR}"
elif [[ -f "${APP_DIR}/config.local.php" ]]; then
  echo "Keeping existing ${APP_DIR}/config.local.php (no ${CREDS})"
else
  echo "No config.local.php found." >&2
  exit 1
fi

chown www-data:www-data "${APP_DIR}/config.local.php"
chmod 640 "${APP_DIR}/config.local.php"

echo "==> Syncing MySQL password..."
DB_PASS="$(awk -F': ' '/^  password:/{print $2}' "${CREDS}")"
mysql <<SQL
ALTER USER IF EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
FLUSH PRIVILEGES;
SQL

if [[ -x "$(command -v php)" && -f "${DEPLOY_DIR}/fetch-demo-covers.php" ]]; then
  php "${DEPLOY_DIR}/fetch-demo-covers.php" || true
fi

"${DEPLOY_DIR}/reseed-demo-db.sh"

chown -R "${GIT_USER}:www-data" "${APP_DIR}"
find "${APP_DIR}" -type d -exec chmod 775 {} \;
find "${APP_DIR}" -type f -exec chmod 664 {} \;
chmod 640 "${APP_DIR}/config.local.php"
chown www-data:www-data "${APP_DIR}/config.local.php"
mkdir -p "${APP_DIR}/images/books"
chown www-data:www-data "${APP_DIR}/images/books"

echo "Done. https://lms.dhanwanth.com — demo / DemoPass123"
