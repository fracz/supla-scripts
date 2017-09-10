#!/usr/bin/env bash

(crontab -l; echo ""; cat ./crontab) | crontab
