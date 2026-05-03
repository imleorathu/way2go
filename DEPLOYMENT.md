# Way2Go Deployment

Way2Go is a PHP and MySQL web application. It cannot run on static hosts such as GitHub Pages because those hosts do not execute PHP or provide MySQL.

## Required Hosting

- PHP 8.1 or newer
- MySQL or MariaDB
- PDO MySQL extension enabled
- Apache or another PHP-capable web server

## Shared Hosting / cPanel Steps

1. Upload the project files to your site folder, for example `public_html` or `public_html/way2go`.
2. Create a MySQL database and database user in cPanel.
3. Set these environment variables if your host supports them:

```text
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
APP_BASE_URL=
```

Use `APP_BASE_URL=/way2go` only when the app is inside a subfolder such as `https://example.com/way2go`.

4. If your host does not support environment variables, edit `app/config.php` and replace the default database values.
5. Open `/setup.php` in the browser once to create the database tables and sample data.
6. Login with:

```text
admin@way2go.test
admin123
```

## Common Problems

- If CSS/images do not load, set `APP_BASE_URL` to the folder path where the app is deployed.
- If you see a database connection error, update `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS`.
- If uploaded package photos are missing, re-upload them from the admin package screen. Uploaded files are runtime content and are intentionally not stored in Git.
