#!/usr/bin/env bash
# Resets the demo database to demo/seed.sql snapshot.
set -euo pipefail

DEPLOY_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "${DEPLOY_DIR}/.." && pwd)"
CREDS="${DEPLOY_DIR}/.credentials"
SEED="${REPO_ROOT}/demo/seed.sql"
DB_NAME="library"
DEMO_USER="demo"
DEMO_PASS="${HTOR_DEMO_PASSWORD:-DemoPass123}"

if [[ ! -f "${CREDS}" ]]; then
  echo "Missing ${CREDS}. Run deploy/deploy.sh first." >&2
  exit 1
fi

if [[ ! -f "${SEED}" ]]; then
  echo "Missing ${SEED}" >&2
  exit 1
fi

DB_USER="$(awk -F': ' '/^  user:/{print $2}' "${CREDS}")"
DB_PASS="$(awk -F': ' '/^  password:/{print $2}' "${CREDS}")"
DB_HOST="$(awk -F': ' '/^  host:/{print $2}' "${CREDS}")"
DB_HOST="${DB_HOST:-localhost}"

if [[ -z "${DB_USER}" || -z "${DB_PASS}" ]]; then
  echo "Could not parse DB credentials from ${CREDS}" >&2
  exit 1
fi

export MYSQL_PWD="${DB_PASS}"
DEMO_HASH="$(php -r "echo password_hash('${DEMO_PASS}', PASSWORD_DEFAULT);")"

mysql -h "${DB_HOST}" -u "${DB_USER}" "${DB_NAME}" < "${SEED}"
mysql -h "${DB_HOST}" -u "${DB_USER}" "${DB_NAME}" -e \
  "INSERT INTO users (username, password) VALUES ('${DEMO_USER}', '${DEMO_HASH}');"

unset MYSQL_PWD
echo "$(date -Iseconds) Demo DB reseeded (${DB_NAME}@${DB_HOST})"
