# Monshop

Online shop website.

# Prerequisites
Apache
PHP 5.5
MongoDb
Composer

# Deployment for Ubuntu 15.04
This will install all prerequisites for Ubuntu.
```
chmod +x conf.sh
sudo ./conf.sh
```

Start mongodb.
```
sudo mkdir -p /data/db
sudo mongod
```

Install php dependencies with composer and opulate the database with data.
In app/api/v1/ run:
```
composer install
php5 populate.php
```

Enable .htaccess files.
Edit the following file
```
/etc/apache2/apache2.conf
```
Look for
```
<Directory /var/www/>
    Options Indexes FollowSymLinks
    AllowOverride None
    Require all granted
</Directory>
```
and change None to All.

Make sure mod_rewrite is enabled.
```
sudo a2enmod rewrite
```
Restart apache after.
```
sudo service apache2 restart
```

Copy everything to /var/www/html/ directory.
```
sudo rm -r /var/www/html/*
sudo cp * -r /var/www/html/
```

As a final step run.
```
sudo chmod -R 777 /var/www/html/
```

This is not recommended for live deployment, look up the documentation for proper permissions for /var/ww/html/.
