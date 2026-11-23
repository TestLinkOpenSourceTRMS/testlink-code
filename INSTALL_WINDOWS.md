# TestLink 1.9.20 — Windows / XAMPP Installation Guide

## Software Requirements

| Component | Minimum | Recommended | Notes |
|-----------|---------|-------------|-------|
| **PHP** | 7.2.x | **8.0.x – 8.5.x** | Tested with PHP 8.5.0 on Windows (this fork) |
| **MySQL** | 5.7.x | **8.0.x** | `log_bin_trust_function_creators = ON` required |
| **MariaDB** *(alternative)* | 10.1.x | 10.6.x+ | Same `log_bin_trust_function_creators` requirement |
| **Apache** | 2.4.x | 2.4.x | Bundled with XAMPP |
| **XAMPP** | 8.0+ | **8.2+ / 8.5+** | Bundles compatible PHP + MySQL + Apache |

> **Note:** MySQL 5.7 reached End of Life in October 2023.  
> For new installations on Windows, use **MySQL 8.0** (bundled in XAMPP 8.x).

---

## Required PHP Extensions

These are typically enabled by default in XAMPP — verify in `php.ini`:

```
extension=mysqli
extension=gd
extension=ldap       ; optional — only if LDAP auth is needed
extension=xmlrpc     ; required for XMLRPC API
extension=xml
extension=mbstring
extension=zip
extension=curl
extension=openssl
```

---

## Critical `php.ini` Settings

Open `C:\xampp\php\php.ini` and set:

```ini
; Needed for test plans with more than ~100 test cases
max_input_vars = 5000

; Prevent out-of-memory errors on large XML imports
memory_limit = 256M

; Allow file uploads
file_uploads = On
upload_max_filesize = 16M
post_max_size = 16M
```

---

## MySQL / MariaDB Setting

In `C:\xampp\mysql\bin\my.ini` (or `my.cnf`), add under `[mysqld]`:

```ini
log_bin_trust_function_creators = 1
```

Restart MySQL after the change.

---

## Directory Setup (Windows — outside the web root)

TestLink requires two writable directories that **must not be reachable from the browser**.  
Create them at:

| Purpose | Path |
|---------|------|
| Logs | `C:\testlink\logs\` |
| File uploads | `C:\testlink\upload_area\` |

### One-time PowerShell command

```powershell
New-Item -ItemType Directory -Force -Path "C:\testlink\logs"
New-Item -ItemType Directory -Force -Path "C:\testlink\upload_area"
```

These paths are already configured in `custom_config.inc.php` — **do not change** that file unless you move the directories.

---

## Installation Steps

1. Copy the TestLink files to `C:\xampp\htdocs\testlink\`.
2. Verify `custom_config.inc.php` exists (already provided in this repo).
3. Verify `C:\testlink\logs\` and `C:\testlink\upload_area\` exist and are writable by the Apache process (SYSTEM / NetworkService on XAMPP).
4. Start Apache and MySQL from the XAMPP Control Panel.
5. Open your browser and navigate to `http://localhost/testlink/`.
6. Follow the web-based installer — choose **New Installation**.
7. Provide MySQL credentials (default XAMPP: user `root`, password empty or `root`).
8. After installation completes, **delete or rename** the `install/` directory for security.

---

## phpMyAdmin Configuration Storage Setup

XAMPP's phpMyAdmin requires a `pma` control user and a `phpmyadmin` database for its
advanced features. Without this, phpMyAdmin shows warnings but still works for basic tasks.

### Step 1 — Create the pma user in MySQL

Open `http://localhost/phpmyadmin/` → **SQL** tab → run:

```sql
DROP USER IF EXISTS 'pma'@'localhost';
CREATE USER 'pma'@'localhost' IDENTIFIED BY 'pmapass';
GRANT ALL PRIVILEGES ON `phpmyadmin`.* TO 'pma'@'localhost';
FLUSH PRIVILEGES;
```

### Step 2 — Create the phpmyadmin database and tables

In phpMyAdmin → top menu → **Import** → **Choose File** → select:

```
C:\xampp\phpMyAdmin\sql\create_tables.sql
```

Click **Go**.

### Step 3 — Verify config.inc.php matches

Open `C:\xampp\phpMyAdmin\config.inc.php` and confirm these lines are present:

```php
$cfg['Servers'][$i]['controluser'] = 'pma';
$cfg['Servers'][$i]['controlpass'] = 'pmapass';
$cfg['Servers'][$i]['pmadb']       = 'phpmyadmin';
```

The password in `controlpass` **must exactly match** what was set for the `pma` MySQL user.

### Step 4 — Restart Apache

Restart Apache from the XAMPP Control Panel and reload phpMyAdmin.
The configuration storage warning should be gone.

> **Root cause of the error `Access denied for user 'pma'@'localhost' (using password: YES)`:**  
> The `pma` MySQL user had a different password (or did not exist) compared to what
> `controlpass` specifies in `config.inc.php`. Always recreate the user and update the
> config in the same step so they stay in sync.

---

## Troubleshooting

### "Checking if /var/testlink/logs/ directory exists — Failed!"

TestLink defaulted to its Unix example path. This is fixed by `custom_config.inc.php` which overrides `$tlCfg->log_path` and `$g_repositoryPath` to Windows paths. Make sure:

- `custom_config.inc.php` is present in the TestLink root (`c:\xampp\htdocs\testlink\`).
- The directories `C:\testlink\logs\` and `C:\testlink\upload_area\` exist.
- The Apache process has **write permission** to those directories.

### "1045 - Access denied for user 'testlinkUser'@'localhost'"

The password in `config_db.inc.php` doesn't match the MySQL user. Run in phpMyAdmin SQL tab:

```sql
ALTER USER 'testlinkUser'@'localhost' IDENTIFIED BY 'your_password_here';
FLUSH PRIVILEGES;
```

Replace `your_password_here` with the value of `DB_PASS` in `config_db.inc.php`.

### "templates_c is not writable"

Grant the Apache user write access:

```powershell
icacls "C:\xampp\htdocs\testlink\gui\templates_c" /grant "Everyone:(OI)(CI)F"
```

---

## Security Checklist After Installation

- [ ] Delete or move the `install/` directory.
- [ ] Confirm `C:\testlink\logs\` is **not** accessible from the browser.
- [ ] Confirm `C:\testlink\upload_area\` is **not** accessible from the browser.
- [ ] Set a strong password for the TestLink `admin` account.
- [ ] Restrict MySQL `root` access (set a password in XAMPP MySQL).
