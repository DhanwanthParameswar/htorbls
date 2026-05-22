# Backups — HTOR BLS

Nightly backups mirror the **legacy server layout** (same files as the Dropbox restore zip):

- `library.sql` — MariaDB dump
- `website.tar.gz` — app files (`.git` excluded)
- `apache_config.tar.gz`, `php_config.tar.gz`, `mysql_config.tar.gz`
- `cloudflared_config.tar.gz` — tunnel config (this server)
- `local_bin.tar.gz` — `backup.sh`
- `crontab.txt`

## Schedule

**Daily at 3:00 AM** (server local time), via root cron:

```cron
0 3 * * * /usr/local/bin/backup.sh >> /var/log/htor-backup.log 2>&1
```

## Local storage

| Path | Purpose |
|------|---------|
| `/var/backups/library/` | Timestamped backup folders |
| `/var/log/htor-backup.log` | Backup + rclone logs |

Folders older than **14 days** are deleted locally after sync (same as before).

## Google Drive (rclone)

Synced with:

```bash
rclone sync /var/backups/library gdrive:"Archive/Backups/HTOR BLS"
```

Remote name defaults to **`gdrive`** (override with env `RCLONE_REMOTE` / `RCLONE_DEST`).

### One-time: connect Google Drive

On the server (as **admin**, then copy config for root if needed):

```bash
rclone config
```

Suggested answers:

1. `n` new remote  
2. Name: `gdrive`  
3. Storage: `drive` (Google Drive)  
4. Leave client_id / client_secret blank (Enter)  
5. Scope: `1` (full access) or `drive` scope that includes your backup folder  
6. Root folder ID: blank  
7. Service account: `n`  
8. Auto config: `n` (headless server)  
9. Paste the URL into your browser, authorize, paste token back  
10. Configure as Shared Drive: `n` (unless backups go to a Shared Drive)  
11. Confirm  

Test:

```bash
rclone lsd gdrive:
rclone lsd "gdrive:Archive/Backups/HTOR BLS"
```

Root’s cron uses **root’s** rclone config. After configuring as admin:

```bash
sudo mkdir -p /root/.config/rclone
sudo cp ~/.config/rclone/rclone.conf /root/.config/rclone/
sudo chmod 600 /root/.config/rclone/rclone.conf
```

### Manual backup

```bash
sudo /usr/local/bin/backup.sh
sudo tail -50 /var/log/htor-backup.log
```

## Secrets (not in git)

| File | Purpose |
|------|---------|
| `/etc/htor-backup.cnf` | `mysqldump` credentials (`htorbls` user), mode `600` |
| `/root/.config/rclone/rclone.conf` | Google Drive OAuth token |

## Restore (short)

1. Download a timestamp folder from Google Drive (or use local `/var/backups/library/...`).
2. Import SQL (fix MySQL 8 collation if needed — see [SERVER-SETUP.md](SERVER-SETUP.md)).
3. Extract `website.tar.gz` over `/var/www/library.htor.org` (keep `config.local.php` and `images/books/`).
4. Restore images from `website.tar.gz` or separate copy if needed.

## Script location

Source: [`scripts/backup.sh`](../scripts/backup.sh)  
Installed: `/usr/local/bin/backup.sh` (symlink)

After `git pull`, the symlink picks up script changes automatically.
