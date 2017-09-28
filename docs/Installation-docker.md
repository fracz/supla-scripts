# supla-scripts - Docker installation

1. Install [Docker CE](https://docs.docker.com/engine/installation/).
1. Install [docker-compose](https://docs.docker.com/compose/install/).
1. Download and extract the [latest supla-scripts release archive](https://github.com/fracz/supla-scripts/releases/latest).
   ```
   mkdir ~/supla-scripts && tar -zxvf supla-scripts-2.0.0.tar.gz -C ~/supla-scripts 
   ```
1. Enter the directory you have extracted the application to.
1. `cp var/config/docker-config.env.sample var/config/docker-config.env`
1. Open the `var/config/docker-config.env` and 
   1. Set the `DATABASE_PASSWORD` to something strong.
   1. Set the `PORT_HTTP` and `PORT_HTTPS` to ports on which the application should work.
1. `cp var/config/config.sample.json var/config/config.json`
1. Open the `var/config/config.json` and
   1. Set the `db/password` to the same value as in docker configuration.
   1. Change `jwt/key` to something strong.
1. Put your `server.crt` and `server.key` certificates in `var/ssl` or generate self signed certificates with `var/ssl/generate-self-signed-certs.sh`
1. `docker/suplascripts.sh start`
1. Verify that the supla-scripts has started on configured ports.

# Updating to a new version

1. Download and extract the [latest supla-scripts release archive](https://github.com/fracz/supla-scripts/releases/latest) 
   to the same directory as before.
   ```
   tar -zxvf supla-scripts-2.0.0.tar.gz -C ~/supla-scripts 
   ```
2. `docker/suplascripts.sh restart`
