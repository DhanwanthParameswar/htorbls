#!/usr/bin/env bash
# Pull latest demo branch and reseed (repo should already be at APP_DIR).
set -euo pipefail

DEPLOY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${DEPLOY_DIR}/.." && pwd)"
APP_DIR="${APP_DIR:-${REPO_ROOT}}"
BACKUP_CONFIG="/tmp/htorbls-config.local.php.bak"
GIT_USER="${SUDO_USER:-ubuntu}"

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Run as root: sudo bash $0"
  exit 1
fi

if [[ ! -d "${APP_DIR}/.git" ]]; then
  echo "${APP_DIR} is not a git clone." >&2
  exit 1
fi

[[ -f "${APP_DIR}/config.local.php" ]] && cp -a "${APP_DIR}/config.local.php" "${BACKUP_CONFIG}"

sudo -u "${GIT_USER}" git -c safe.directory="${APP_DIR}" -C "${APP_DIR}" fetch origin
sudo -u "${GIT_USER}" git -c safe.directory="${APP_DIR}" -C "${APP_DIR}" checkout demo
sudo -u "${GIT_USER}" git -c safe.directory="${APP_DIR}" -C "${APP_DIR}" pull origin demo

[[ -f "${BACKUP_CONFIG}" ]] && cp -a "${BACKUP_CONFIG}" "${APP_DIR}/config.local.php" && rm -f "${BACKUP_CONFIG}"

chown -R "${GIT_USER}:www-data" "${APP_DIR}"
chmod 640 "${APP_DIR}/config.local.php"
chown www-data:www-data "${APP_DIR}/config.local.php"
mkdir -p "${APP_DIR}/images/books"
chown www-data:www-data "${APP_DIR}/images/books"

"${DEPLOY_DIR}/reseed-demo-db.sh"
echo "Live app updated. https://lms.dhanwanth.com"
