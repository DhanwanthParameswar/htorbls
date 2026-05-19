# Deploy scripts

Ops tooling for HTOR BLS. **Full documentation:** [../DEPLOYMENT.md](../DEPLOYMENT.md)

## Quick commands

```bash
cd /var/www/htorbls   # or your clone path

sudo deploy/reseed-demo-db.sh
sudo deploy/fix-demo-site.sh
sudo cp deploy/cron-demo-reseed /etc/cron.d/htorbls-demo-reseed
```

## Fresh server

```bash
sudo git clone -b demo https://github.com/DhanwanthParameswar/htorbls.git /var/www/htorbls
sudo bash /var/www/htorbls/deploy/deploy.sh
```

See [DEPLOYMENT.md](../DEPLOYMENT.md) for local dev, production, Cloudflare Tunnel, and troubleshooting.
