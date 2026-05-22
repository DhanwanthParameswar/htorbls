# HTOR BLS — Balvihar Library System

Web app for managing the **Balvihar Library** at the [Hindu Temple of Rochester](https://www.hindutempleofrochester.com/). Staff can check books in and out, track fines, maintain the catalog, and view history.

**Production:** [https://library.htor.org](https://library.htor.org)

## Features

- **Checkout log** — New entries, renewals, returns, fines (auto-calculated weekly)
- **Book catalog** — Add/edit books with categories, notes, and cover images
- **Book ID tool** — Lookup, renew, return by book ID
- **Archive** — Returned items (last 6 months shown by default)
- **Authentication** — Session-based login with bcrypt passwords

## Requirements

| Component | Version / notes |
|-----------|-----------------|
| PHP | 8.0+ with `mysqli`, `gd`, `mbstring` |
| Database | MariaDB or MySQL |
| Web server | Apache (or equivalent) with `mod_php` or php-fpm |
| Composer | Not required |

Front-end assets load from CDNs (Bootstrap 5, jQuery, DataTables).

## Quick start (local / new server)

### 1. Clone and document root

```bash
git clone https://github.com/DhanwanthParameswar/htorbls.git
```

Apache (or nginx) **document root** should be the repo root. Several scripts expect book images at:

`/var/www/library.htor.org/images/books/`

If you use a different path, update the hardcoded paths in `new_book.php`, `edit_book.php`, `book_edit_complete.php`, and `book_list.php`.

### 2. Database

Create database `library` and import a dump, or create tables: `users`, `booklist`, `librarylog`, `libraryarchive` (see [docs/SERVER-SETUP.md](docs/SERVER-SETUP.md) for schema notes).

Create a dedicated DB user with `SELECT`, `INSERT`, `UPDATE`, `DELETE` on `library.*`.

### 3. Configuration

Copy and edit local config (not in git):

```bash
cp config.local.php.example config.local.php   # if example exists; otherwise create manually
```

`config.local.php` must define:

```php
<?php
$username = 'your_db_user';
$user_pass = 'your_db_password';
```

`db_connect.php` reads this file and connects to database `library` on `localhost`.

### 4. Writable directories

```bash
mkdir -p images/books upload
chown -R www-data:www-data images/books upload
chmod 755 images/books upload
```

Book covers are stored as `images/books/{BOOKID}.jpeg`.

### 5. First admin user

Passwords must be bcrypt hashes (`password_hash()` in PHP). Example:

```bash
php -r "echo password_hash('your-password', PASSWORD_DEFAULT), PHP_EOL;"
```

```sql
INSERT INTO users (username, password) VALUES ('librarian', '<hash-from-above>');
```

## Project layout

```
├── index.php              # Main dashboard (after login)
├── login.php / auth.php   # Authentication
├── library_log.php        # Active checkouts
├── library_archive.php    # Returned items
├── book_list.php          # Catalog
├── new_entry.php          # Checkout flow
├── book_id_tool*.php      # Lookup / renew / return
├── db_connect.php         # DB connection
├── config.local.php       # Local credentials (gitignored)
├── bootstrap.php          # Shared CSS/JS includes
├── css/
├── images/
│   └── books/             # Cover images (gitignored)
└── docs/
    └── SERVER-SETUP.md    # Lightsail + Cloudflare deployment notes
```

## Gitignored paths

See [.gitignore](.gitignore):

- `config.local.php` — database credentials
- `images/books/` — uploaded cover images
- `phpmyadmin/` — optional local symlink
- `.well-known/`

## Deployment

Production runs on **AWS Lightsail (LAMP)** behind a **Cloudflare Tunnel** at `library.htor.org`.

Server-specific setup (paths, tunnel, MariaDB import) is in **[docs/SERVER-SETUP.md](docs/SERVER-SETUP.md)**.

Nightly backups to Google Drive (rclone): **[docs/BACKUPS.md](docs/BACKUPS.md)**.

## Support

- **Library / access questions:** Balvihar coordinators or [balviharhtor@gmail.com](mailto:balviharhtor@gmail.com)
- **Technical issues:** [im@dhanwanth.com](mailto:im@dhanwanth.com)
- **In-app help:** [help.php](help.php) (includes training video)

## License

Internal use for HTOR Balvihar Library. Contact maintainers before redistributing.
