#!/usr/bin/env bash
# One-time: move credentials + cron from ~/htorbls-deploy to /var/www/htorbls/deploy
set -euo pipefail

OLD="${HOME}/htorbls-deploy"
NEW="/var/www/htorbls/deploy"

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Run as root: sudo bash $0"
  exit 1
fi

if [[ ! -d "${NEW}" ]]; then
  echo "Missing ${NEW}. git pull the demo branch first." >&2
  exit 1
fi

chmod +x "${NEW}"/*.sh

if [[ -f "${OLD}/.credentials" && ! -f "${NEW}/.credentials" ]]; then
  cp -a "${OLD}/.credentials" "${NEW}/.credentials"
  chmod 600 "${NEW}/.credentials"
  echo "Moved .credentials"
fi

if [[ -f /etc/cron.d/htorbls-demo-reseed ]]; then
  sed -i "s|${OLD}/reseed-demo-db.sh|${NEW}/reseed-demo-db.sh|g" /etc/cron.d/htorbls-demo-reseed
  echo "Updated cron path"
else
  cp "${NEW}/cron-demo-reseed" /etc/cron.d/htorbls-demo-reseed
  echo "Installed cron"
fi

echo ""
echo "Use scripts from: ${NEW}"
echo "  sudo bash ${NEW}/reseed-demo-db.sh"
echo ""
echo "Optional: rm -rf ${OLD}  (after you confirm everything works)"
