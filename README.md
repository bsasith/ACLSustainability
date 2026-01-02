# Simple PHP + MySQL Login (No Register)

This is a minimal username/password login using PHP (mysqli) + MySQL with sessions.
There is **no register page**. Create users directly in the database (instructions below).

## 1) Requirements
- PHP 7.4+ (works on PHP 8.x)
- MySQL / MariaDB
- Web server (Apache/Nginx) or XAMPP/WAMP/Laragon

## 2) Setup Database
Run the SQL in `sql/schema.sql` in your MySQL client.

## 3) Configure DB
Edit `config.php` and set:
- DB_HOST, DB_NAME, DB_USER, DB_PASS

## 4) Run
Put this folder in your server web root and open:
- `login.php`

Successful login redirects to:
- `dashboard.php`

## Notes
- Passwords are stored using `password_hash()` (bcrypt).
- If you want to log out, open `logout.php`.
