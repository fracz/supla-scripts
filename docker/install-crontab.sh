#!/usr/bin/env bash

cd "$(dirname "$0")"

(crontab -l | grep -q supla-scripts && echo "supla-scripts crontab already installed") || ((crontab -l; echo ""; cat ./crontab) | crontab && echo "supla-scripts crontab has been installed successfully")
