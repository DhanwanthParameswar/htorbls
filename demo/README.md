# Demo branch assets

Deployed to **lms.dhanwanth.com** only.

| File | Purpose |
|------|---------|
| `banner.php` | Portfolio notice (via `demo_render_banner()`) |
| `seed.sql` | MySQL snapshot — source of truth for demo data |

Daily reset: `sudo /var/www/htorbls/deploy/reseed-demo-db.sh`  
Login: `demo` / `DemoPass123`
