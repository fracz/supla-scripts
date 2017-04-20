# Supla Scripts

Utilites that leverage Supla REST Api in order to provide some nice URLs.

# Installation

Clone this repo.

Copy `config.php.sample` into `config.php` and adjust settings there.

Set the `HTTP_BASIC_USER` and `HTTP_BASIC_PASSWORD` if you want to protect
execution of the scripts with additional credentials.

# Features

Instructions below assume you have cloned and configured the scripts and that
the project is available on `https://your-server.com/supla-scripts/`.

## Toggle channel state

Open the `https://your-server.com/supla-scripts/toggle/123`, where `123` is
the id of the channel assigned to your account and that should be toggled.

The script will output `ON` if the channel has been turned on and `OFF` otherwise.

## Read temperature

Open the `https://your-server.com/supla-scripts/temperature/123`, where `123` is
the id of the channel assigned to your account and that is a thermometer.

The script will output the temperature in Celcius degrees, e.g. `21.65°C`.

# Create widgest on Android

Install the [HTTP Request Widget](https://play.google.com/store/apps/details?id=com.idlegandalf.httprequestwidget)
application.

Add new widget and supply an addres for the script to utilize its feature.

You can use the *parse response* option in order to display the result
(e.g. `ON` or `OFF` or temperature).
