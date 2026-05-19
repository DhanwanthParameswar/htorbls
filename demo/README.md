# Demo branch assets

Deployed to **lms.dhanwanth.com** only.

| File / folder | Purpose |
|---------------|---------|
| `banner.php` | Portfolio notice (via `demo_render_banner()`) |
| `seed.sql` | MySQL snapshot — source of truth for demo data |
| `book-covers/` | Cover art bundled as `{bookId}.jpeg` (on reseed: all `images/books/*.jpeg` removed, then bundle copied in) |

Refresh covers: `php deploy/fetch-demo-covers.php` (writes `demo/book-covers/` and `images/books/`).

Daily reset: `sudo /var/www/htorbls/deploy/reseed-demo-db.sh`  
Login: `demo` / `DemoPass123`
