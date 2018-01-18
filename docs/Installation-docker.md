# supla-scripts - Docker installation

1. Install [Docker CE](https://docs.docker.com/engine/installation/) and [docker-compose](https://docs.docker.com/compose/install/).
1. Clone this repository:
   ```
   git clone https://github.com/fracz/supla-scripts.git
   ```
1. Run the following command to generate reasonable configuration files:
   ```
   supla-scripts/docker/suplascripts.sh
   ```
   and verify that the settings in the `supla-scripts/docker/.env` file match your needs.

1. Run!
   ```
   supla-scripts/docker/suplascripts.sh start
   ```

# Updating to a new version

```
cd supla-scripts
git pull
docker/suplascripts.sh restart
```

# Proxy mode
This is similar to [SUPLA-Docker proxy mode](https://github.com/SUPLA/supla-docker#launching-in-proxy-mode).
All you need to do to connect to already launched [webproxy](https://github.com/evertramos/docker-compose-letsencrypt-nginx-proxy-companion#how-to-use-it)
is changing the
```
COMPOSE_FILE=docker-compose.base.yml:docker-compose.standalone.yml
```
into
```
COMPOSE_FILE=docker-compose.base.yml:docker-compose.proxy.yml
```
