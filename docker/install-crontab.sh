#!/usr/bin/env bash

(crontab -l | grep -q supla-scripts && echo "supla-scripts crontab already installed") || ((crontab -l; echo ""; cat ./crontab) | crontab && echo "supla-scripts crontab has been installed successfully")
