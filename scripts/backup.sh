#!/bin/bash
# HTOR BLS nightly backup — local archive + rclone sync to Google Drive.
# Matches legacy layout: Date-MM-DD-YYYY-Time-HH-MM-AMPM/*.sql, *.tar.gz, crontab.txt

set -euo pipefail

BACKUP_DIR="/var/backups/library"
DATABASE="library"
WEBSITE_DIR="/var/www/library.htor.org"
APACHE_CONFIG_DIR="/etc/apache2"
MYSQL_CONFIG_DIR="/etc/mysql"
KEEP_DAYS=14
RCLONE_REMOTE="${RCLONE_REMOTE:-gdrive}"
RCLONE_DEST="${RCLONE_DEST:-Archive/Backups/HTOR BLS}"
MYSQL_CNF="/etc/htor-backup.cnf"
LOG_FILE="/var/log/htor-backup.log"

log() { echo "[$(date -Iseconds)] $*" | tee -a "$LOG_FILE"; }

if [[ "$(id -u)" -ne 0 ]]; then
  echo "Run as root: sudo $0" >&2
  exit 1
fi

if [[ ! -f "$MYSQL_CNF" ]]; then
  log "ERROR: Missing $MYSQL_CNF"
  exit 1
fi

mkdir -p "$BACKUP_DIR"
TIMESTAMP=$(date '+Date-%m-%d-%Y-Time-%I-%M-%p')
BACKUP_PATH="$BACKUP_DIR/$TIMESTAMP"
mkdir -p "$BACKUP_PATH"

log "Starting backup -> $BACKUP_PATH"

mysqldump --defaults-extra-file="$MYSQL_CNF" \
  --single-transaction --skip-lock-tables \
  "$DATABASE" > "$BACKUP_PATH/$DATABASE.sql"

tar -czf "$BACKUP_PATH/website.tar.gz" \
  --exclude='.git' \
  -C "$WEBSITE_DIR" .

tar -czf "$BACKUP_PATH/apache_config.tar.gz" -C "$APACHE_CONFIG_DIR" .

PHP_CONFIG_DIR=$(find /etc/php -type d -name "apache2" -print -quit 2>/dev/null || true)
if [[ -n "$PHP_CONFIG_DIR" ]]; then
  tar -czf "$BACKUP_PATH/php_config.tar.gz" -C "$PHP_CONFIG_DIR" .
fi

if [[ -d "$MYSQL_CONFIG_DIR" ]]; then
  tar -czf "$BACKUP_PATH/mysql_config.tar.gz" -C "$MYSQL_CONFIG_DIR" .
fi

if [[ -d /etc/cloudflared ]]; then
  tar -czf "$BACKUP_PATH/cloudflared_config.tar.gz" -C /etc/cloudflared .
fi

if [[ -d /usr/local/bin ]]; then
  tar -czf "$BACKUP_PATH/local_bin.tar.gz" -C /usr/local/bin \
    backup.sh 2>/dev/null || true
fi

crontab -l > "$BACKUP_PATH/crontab.txt" 2>/dev/null || true

if rclone listremotes 2>/dev/null | grep -q "^${RCLONE_REMOTE}:$"; then
  log "Syncing $BACKUP_DIR to ${RCLONE_REMOTE}:${RCLONE_DEST}"
  rclone sync "$BACKUP_DIR" "${RCLONE_REMOTE}:${RCLONE_DEST}" \
    --log-file "$LOG_FILE" --log-level INFO
  log "rclone sync completed"
else
  log "WARN: rclone remote '${RCLONE_REMOTE}' not configured — local backup only"
fi

find "$BACKUP_DIR" -mindepth 1 -maxdepth 1 -type d -mtime +"$KEEP_DAYS" -exec rm -rf {} +

log "Backup finished: $BACKUP_PATH"
