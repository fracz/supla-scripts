# supla-scripts - Docker installation

1. Install [Docker CE](https://docs.docker.com/engine/installation/).
1. Install [docker-compose](https://docs.docker.com/compose/install/).
1. Download and extract the [latest supla-scripts release archive](https://github.com/fracz/supla-scripts/releases/latest).
1. Extract it, e.g. `mkdir ~/supla-scripts && tar -zxvf supla-scripts-2.0.0.tar.gz -C ~/supla-scripts` 
1. Enter the directory you have extracted the application to.
1. `cp var/config/docker-config.env.sample var/config/docker-config.env`
1. Open the `var/config/docker-config.env` and 
   1. Set the `DATABASE_PASSWORD` to something strong.
   1. Set the `UID` to your current user's UID acquired with `id -u`.
   1. Set the `PORT_HTTP` and `PORT_HTTPS` to ports on which the application should work.
1. `cp var/config/config.sample.json var/config/config.json`
1. Open the `var/config/config.json` and
   1. Set the `db/password` to the same value as in docker configuration.
   1. Change `jwt/key` to something strong.
1. `ln -s ../var/config/docker-config.env docker/.env`
1. Put your `server.crt` and `server.key` certificates in `var/ssl` or generate self signed certificates with `var/ssl/generate-self-signed-certs.sh`
1. `cd docker && docker-compose up --build -d`
1. `docker exec -u www-data suplascripts php website/supla-scripts init`
1. Verify that the supla-scripts has started on configured ports.
1. `docker/install-crontab.sh`
