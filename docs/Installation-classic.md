# supla-scripts - classic installation

1. You need to have PHP7.0+ with Apache and MySQL installed.
    1. You already have this if you have successfully run the [SUPLA](https://github.com/SUPLA/supla-cloud) on the server.
1. Create database for supla-scripts
    1. Enter the MySQL command prompt as `root`. 
       > Note: if you are on RaspberryPI based on SUPLA image, the root password
         is probably `raspberry`. I strongly encourage to 
         [change it to something else](https://www.cyberciti.biz/faq/mysql-change-root-password/)
         after successful installation of supla-scripts.    
     1. Create new schema:
        ```
        CREATE SCHEMA suplascripts;
        ```
    1. Generate a strong password for example with [http://passwordsgenerator.net](http://passwordsgenerator.net/)
    1. Create a new database user and assign it to the created database:
       ```
       CREATE USER 'suplascripts'@'localhost' IDENTIFIED BY 'THE_strong_PASSWORD';
       GRANT ALL ON suplascripts.* TO 'suplascripts'@'localhost';
       FLUSH PRIVILEGES;
       ```
    1. Exit the MySQL command prompt with `\q`.
1. Download and extract the [latest supla-scripts release archive](https://github.com/fracz/supla-scripts/releases/latest)
   to `/var/www/supla-scripts`.
   ```
   mkdir /var/www/supla-scripts
   tar -zxf supla-scripts-2.0.0.tar.gz -C /var/www/supla-scripts
   ```
1. Enter this directory and create a configuration file
   ```
   cd /var/www/supla-scripts
   cp var/config/config.sample.json var/config/config.json
   ```
1. Open the `var/config/config.json` and
   1. Set the `db/host` to `localhost`.
   1. Set the `db/password` to the password you have generated for MySQL.
   1. Change `jwt/key` to something strong, different than MySQL password 
      (generate a different value with any generator).
1. Initialize the application
   ```
   php /var/www/supla-scripts/supla-scripts init
   ```
1. As root (or with `sudo` privileges), create a virtual host configuration
   1. `cp var/config/supla-scripts.vhost.sample.conf /etc/apache2/sites-available/supla-scripts.conf`
   1. Adjust the `/etc/apache2/sites-available/supla-scripts.conf` to match your needs
       1. Change the port the scripts should run on (the `*:1234` means that it will run on port `1234`).
       1. Uncomment and set the `ServerName` if the server has a domain name for the app.
       1. Change `ServerAdmin` to something meaningful.
       1. Fix paths to the SSL certificates (the default configuration uses
          the same certificates as SUPLA-Cloud).
   1. Enable the virtual host and enable mod_rewrite
       ```
       a2ensite supla-scripts
       a2enmod rewrite
       service apache2 restart
       ```
1. Change the owner of the `/var/www/supla-scripts` directory to `www-data`
    ```
    chown -R www-data:www-data /var/www/supla-scripts
    ```
1. Check if the application is available on your server address and configured port.
1. Install watchdog crontab by executing:
   ```
   /var/www/supla-scripts/var/config/install-crontab.sh
   ```

# Updating to a new version

1. Download and extract the [latest supla-scripts release archive](https://github.com/fracz/supla-scripts/releases/latest) 
   to the same directory as before.
   ```
   tar -zxf supla-scripts-2.0.0.tar.gz -C /var/www/supla-scripts 
   ```
2. `php /var/www/supla-scripts/supla-scripts init`
3. Change the owner of the `/var/www/supla-scripts` directory to `www-data`
    ```
    chown -R www-data:www-data /var/www/supla-scripts
    ```
