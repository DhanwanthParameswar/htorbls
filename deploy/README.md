# Deploy — lms.dhanwanth.com

Ops scripts for the **demo** branch. The live app and this folder are the same git repo at `/var/www/htorbls`.

## Layout

| Path | Purpose |
|------|---------|
| `deploy/` | These scripts (version controlled) |
| `deploy/.credentials` | Server-only secrets (**gitignored**) |
| `demo/seed.sql` | DB snapshot for daily reseed |
| `config.local.php` | DB config on server (**gitignored**) |

## Common commands

```bash
cd /var/www/htorbls

# Pull app + deploy script updates
git pull origin demo

# Reseed demo data now
sudo deploy/reseed-demo-db.sh

# Full fix (config + mysql + reseed)
sudo deploy/fix-demo-site.sh

# Daily cron (once)
sudo cp deploy/cron-demo-reseed /etc/cron.d/htorbls-demo-reseed
```

## Fresh server install

```bash
sudo git clone -b demo https://github.com/DhanwanthParameswar/htorbls.git /var/www/htorbls
sudo bash /var/www/htorbls/deploy/deploy.sh
```

## Migrate from `~/htorbls-deploy`

```bash
sudo bash /var/www/htorbls/deploy/migrate-from-htorbls-deploy.sh
```

## Branches

- `main` — production library
- `demo` — portfolio site (this server)
