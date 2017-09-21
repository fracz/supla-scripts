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
   tar -zxvf supla-scripts-2.0.0.tar.gz -C /var/www/supla-scripts
   ```
1. Extract it, e.g. `mkdir ~/supla-scripts && tar -zxvf supla-scripts-2.0.0.tar.gz -C ~/supla-scripts` 
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
1. TODO vhost
1. TODO crontab
