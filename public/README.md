# Public Directory

This is the web root directory for the Boutique Store Management System.

## Configuration

### Apache Configuration

For Apache servers, this directory should be configured as the `DocumentRoot` in the virtual host.

Example virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName boutique-store.local
    DocumentRoot /path/to/boutique-store-management/public
    
    <Directory /path/to/boutique-store-management/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/boutique-error.log
    CustomLog ${APACHE_LOG_DIR}/boutique-access.log combined
</VirtualHost>
```

### Nginx Configuration

For Nginx servers, use this configuration:

```nginx
server {
    listen 80;
    server_name boutique-store.local;
    
    root /path/to/boutique-store-management/public;
    index index.php;
    
    # URL rewriting for clean URLs
    location / {
        try_files $uri $uri/ /index.php?/$uri;
    }
    
    # PHP-FPM configuration
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    location ~ ~$ {
        deny all;
    }
}
```

### IIS Configuration

For IIS servers, ensure URL Rewrite module is installed and configured with appropriate rules pointing to `index.php`.

## File Structure

```
public/
├── index.php           # Main application entry point
├── .htaccess           # Apache URL rewriting rules
└── README.md           # This file
```

## Directory Permissions

The web server process must have read access to all files in this directory:

```bash
chmod -R 755 /path/to/boutique-store-management/public
```

## Access

Once configured, access the application at:

- **Frontend:** `http://localhost/`
- **Dashboard:** `http://localhost/dashboard.html`
- **Login:** `http://localhost/login.html`
- **API:** `http://localhost/api/...`

## Troubleshooting

### 404 Errors

If you receive 404 errors for all requests:

1. Verify `.htaccess` is enabled: `AllowOverride All` in Apache config
2. Ensure `mod_rewrite` is enabled: `a2enmod rewrite && service apache2 restart`
3. Check that `index.php` exists in this directory
4. Verify file permissions: `chmod 755 index.php`

### Blank Page / No Output

If the application shows a blank page:

1. Check PHP error logs
2. Verify `APP_DEBUG=true` in `.env`
3. Check file permissions on `/storage/logs` directory
4. Verify database connection in `.env`

### Database Connection Error

If you see database connection errors:

1. Verify MySQL is running
2. Check database credentials in `.env`
3. Ensure database exists: `CREATE DATABASE IF NOT EXISTS boutique_store_db;`
4. Run migrations: `php bin/migrate`

## Security Notes

- **Never** expose this directory directly to the internet without HTTPS
- **Always** keep `.env` file outside the web root or protected
- **Enable** firewall rules to restrict admin paths
- **Use** strong authentication credentials
- **Regularly** update dependencies: `composer update`

## Logs

Application logs are stored in `/storage/logs/`:

- `app.log` - Main application log
- `errors-YYYY-MM-DD.log` - PHP errors
- `exceptions-YYYY-MM-DD.log` - Uncaught exceptions
- `queries-YYYY-MM-DD.log` - Database queries (debug mode only)

View recent logs:

```bash
tail -f storage/logs/app.log
```


