#!/usr/bin/env bash

cd "$(dirname "$0")"

cat ./../scripts/logo.txt
echo ""

if [ ! -f .env ]; then
  cd ./../var/config
  if [ ! -f docker-config.env ]; then
    cp docker-config.env.sample docker-config.env
    DB_PASSWORD="$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)"
    sed -i "s+CHANGE_ME_BEFORE_FIRST_LAUNCH+$DB_PASSWORD+g" docker-config.env
    if [ "$(expr substr $(dpkg --print-architecture) 1 3)" == "arm" ]; then
      sed -i -E "s/COMPOSE_FILE=(.+)/COMPOSE_FILE=\1:docker-compose.arm32v7.yml/" docker-config.env
    fi
    if [ ! -f config.json ]; then
      cp config.sample.json config.json
      sed -i "s+CHANGE_ME_BEFORE_FIRST_LAUNCH+$DB_PASSWORD+g" config.json
      SECRET="$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)"
      sed -i "s+CHANGE_SECRET_BEFORE_FIRST_LAUNCH+$SECRET+g" config.json
    fi
  fi
  cd ./../../docker
  ln -s ./../var/config/docker-config.env .env >/dev/null 2>&1
  echo -e "${YELLOW}Sample configuration file has been generated for you.${NC}"
  echo -e "${YELLOW}Please check if the .env file matches your needs and run this command again.${NC}"
  exit
fi

source .env >/dev/null 2>&1

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

if [ "$1" = "start" ]; then
  echo -e "${GREEN}Starting SUPLA Scripts containers${NC}"
  docker-compose up --build -d && \
  echo -e "${GREEN}SUPLA Scripts containers have been started${NC}"

elif [ "$1" = "stop" ]; then
  echo -e "${GREEN}Stopping SUPLA Scripts containers${NC}"
  docker-compose -f docker-compose.base.yml stop && \
  echo -e "${GREEN}SUPLA Scripts containers has been stopped.${NC}"

elif [ "$1" = "restart" ]; then
  "./$(basename "$0")" stop
  sleep 1
  "./$(basename "$0")" start

else
  echo -e "${RED}Usage: $0 start|stop|restart${NC}"

fi
