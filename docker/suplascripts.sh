#!/usr/bin/env bash

cd "$(dirname "$0")"

ln -s ./../var/config/docker-config.env .env >/dev/null 2>&1

if [ ! -f .env ]; then
  echo "Could not read the docker-config.env configuration file."
  exit
fi

export UID=$(id -u) >/dev/null 2>&1
export GID=$(id -g) >/dev/null 2>&1

source .env >/dev/null 2>&1

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# remove \r at the end of the env, if exists
CONTAINER_NAME="$(echo -e "${COMPOSE_PROJECT_NAME}" | sed -e 's/\r$//')"

if [ "$1" = "start" ]; then
  echo -e "${GREEN}Starting SUPLA scripts containers${NC}"

  if [ "x${PORT_HTTP}${DOMAIN_NAME}" == "x" ] || ( [ "x$PORT_HTTP" != "x" ] && [ "x$DOMAIN_NAME" != "x" ] ); then
    echo -e "${RED}Could not determine in which mode to start."
    echo -e "Define either PORT_HTTP or DOMAIN_NAME in .env file to run in STANDALONE or PROXY mode, respectively.${NC}"
    exit
  fi

  if [ -z "$PORT_HTTP" ]; then
    echo -e "${YELLOW}Starting in PROXY mode${NC}"
    docker-compose -f docker-compose.base.yml -f docker-compose.letsencrypt.yml up --build -d

  else
    echo -e "${YELLOW}Starting in STANDALONE mode${NC}"
    docker-compose -f docker-compose.base.yml -f docker-compose.standalone.yml up --build -d

  fi

elif [ "$1" = "stop" ]; then
  echo -e "${GREEN}Stopping SUPLA Scripts containers${NC}"
  docker-compose -f docker-compose.base.yml stop && echo -e "${GREEN}SUPLA Scripts containers has been stopped.${NC}"

elif [ "$1" = "restart" ]; then
  "./$(basename "$0")" stop
  sleep 1
  "./$(basename "$0")" start

else
  echo -e "${RED}Usage: $0 start|stop|restart${NC}"

fi
