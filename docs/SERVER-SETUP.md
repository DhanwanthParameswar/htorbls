# Server setup — library.htor.org (Lightsail)

Notes from the **2026-05-22** deployment on AWS Lightsail (Ohio, LAMP blueprint). Use this when maintaining the production VM, not for generic local development.

## Environment

| Item | Value |
|------|--------|
| Host | AWS Lightsail — 2 GB RAM, 2 vCPU, 60 GB |
| Blueprint | LAMP (Apache, PHP 8.2, MariaDB 10.11) |
| Public URL | https://library.htor.org |
| Git remote | https://github.com/DhanwanthParameswar/htorbls.git |
| Live branch | `main` |

## Important paths

| Path | Purpose |
|------|---------|
| `/var/www/library.htor.org` | **Git repo + Apache docroot** for the app |
| `/var/www/library.htor.org/config.local.php` | DB user `htorbls` credentials (gitignored) |
| `/var/www/library.htor.org/images/books/` | Book cover JPEGs (gitignored) |
| `/var/www/html` | Default LAMP site (stock page + phpMyAdmin symlink) |
| `/etc/apache2/sites-available/library.htor.org.conf` | Apache vhost |
| `/var/log/apache2/library-*.log` | Apache logs for this site |
| `/etc/cloudflared/config.yml` | Cloudflare Tunnel ingress |
| `/etc/cloudflared/*.json` | Tunnel credentials (secret) |
| `/home/admin/.htorbls_db_pass` | `htorbls` MariaDB password (mode 600) |
| `/opt/aws/lamp/credentials.log` | Lightsail blueprint MariaDB **admin** credentials |
| `/var/lib/mysql/` | MariaDB data directory (`library` database) |

Repo ownership on server: `admin:www-data` (group-writable for `git pull` without breaking Apache).

## What was installed / configured

### Application

1. Cloned [htorbls](https://github.com/DhanwanthParameswar/htorbls) into `/var/www/library.htor.org`.
2. Created `config.local.php` with dedicated DB user `htorbls`.
3. Linked directory to GitHub (`git` tracks `origin/main`).

### Database

1. Created database `library` (utf8mb4).
2. Imported backup from Dropbox zip (`library.sql`).
3. **MariaDB fix:** dump from MySQL 8 used collation `utf8mb4_0900_ai_ci`; converted to `utf8mb4_unicode_ci` before import:

   ```bash
   sed 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' library.sql > library_mariadb.sql
   sudo mysql library < library_mariadb.sql
   ```

4. Restored **800** cover images from `website.tar.gz` → `images/books/`.

**Post-import counts (2026-05-22):** 850 books, 20 active log rows, 276 archive rows, 1 user (`librarian`).

### Apache

Enabled vhost `library.htor.org` with `DocumentRoot /var/www/library.htor.org` and `ServerName library.htor.org`.

Verify locally:

```bash
curl -sI -H 'Host: library.htor.org' http://127.0.0.1/login.php
```

### Cloudflare Tunnel

- Installed `cloudflared` (package from Cloudflare apt repo).
- Authenticated via `cloudflared tunnel login` (cert in `~/.cloudflared/cert.pem`).
- Tunnel name: **`htor-library`**
- Tunnel ID: `67d0b3e6-9955-421d-806f-4baa10b7329a`
- DNS: `library.htor.org` CNAME → tunnel (`cloudflared tunnel route dns -f` overwrote old A/AAAA records that caused **523** errors).
- Systemd: `cloudflared.service` (enabled on boot).

`/etc/cloudflared/config.yml` routes HTTPS to `http://127.0.0.1:80` with `httpHostHeader: library.htor.org` so Apache matches the correct vhost.

**Lightsail firewall:** ports 80/443 do not need to be public when using the tunnel. SSH (22) is enough.

Server public IPv4 (reference only): `3.147.61.92` — not used as origin while tunnel is active.

## Day-to-day operations

### Update app from GitHub

```bash
cd /var/www/library.htor.org
git pull
```

`config.local.php` and `images/books/` are untouched by git.

### Service status

```bash
sudo systemctl status apache2 cloudflared
sudo systemctl restart apache2    # after PHP/config changes
sudo systemctl restart cloudflared
```

### Database CLI

```bash
# App user (password in /home/admin/.htorbls_db_pass)
mysql -u htorbls -p library

# Admin (password in /opt/aws/lamp/credentials.log)
sudo mysql library
```

### Logs

```bash
sudo tail -f /var/log/apache2/library-error.log
sudo journalctl -u cloudflared -f
```

## Database schema (reference)

Tables used by the app:

| Table | Role |
|-------|------|
| `users` | Login (`username`, bcrypt `password`) |
| `booklist` | Catalog (`bookId` unique) |
| `librarylog` | Active checkouts (`bookId` unique, FK to `booklist`) |
| `libraryarchive` | Returned items |

No schema SQL ships in the repo; use a backup dump or export from production.

## Backup restore (repeat deploy)

1. Upload zip (contains `library.sql`, `website.tar.gz`, etc.).
2. Fix collation if source is MySQL 8 → MariaDB (see above).
3. Import SQL; rsync `images/books/` from `website.tar.gz`.
4. Keep existing `config.local.php` unless rotating DB passwords.

## Security notes

- Do not commit `config.local.php` or tunnel JSON credentials.
- phpMyAdmin on `/var/www/html` should not be exposed publicly; prefer SSH tunnel if needed.
- App uses legacy SQL string building; treat as **internal/trusted** staff tool.
- Consider Cloudflare Access for extra login protection in front of the site.

## Troubleshooting

| Symptom | Check |
|---------|--------|
| Cloudflare **523/530** | `systemctl status cloudflared`; tunnel connector registered? |
| **DB connection error** | `config.local.php`, `htorbls` grants, `systemctl status mariadb` |
| **Login fails** | User exists in `users`; password is bcrypt hash |
| **Images missing** | File at `images/books/{BOOKID}.jpeg`; permissions `www-data` |
| **Wrong site on URL** | Apache vhost `ServerName`; tunnel `httpHostHeader` |

## Related files on server (not in git)

- `/home/admin/htor-backup.zip` — original Dropbox backup
- `/home/admin/htor-backup-extract/` — extracted backup
- `/home/admin/DEPLOYMENT_NOTES.txt` — short pointer (superseded by this doc in repo)

---

*Maintainers: update this file when infrastructure changes (new host, tunnel name, DB user, etc.).*
