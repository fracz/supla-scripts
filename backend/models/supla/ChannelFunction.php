<?php

namespace suplascripts\models\supla;

use MyCLabs\Enum\Enum;

/**
 * @method static ChannelFunction UNSUPPORTED()
 * @method static ChannelFunction NONE()
 * @method static ChannelFunction SCENE()
 * @method static ChannelFunction CONTROLLINGTHEGATEWAYLOCK()
 * @method static ChannelFunction CONTROLLINGTHEGATE()
 * @method static ChannelFunction CONTROLLINGTHEGARAGEDOOR()
 * @method static ChannelFunction THERMOMETER()
 * @method static ChannelFunction HUMIDITY()
 * @method static ChannelFunction HUMIDITYANDTEMPERATURE()
 * @method static ChannelFunction OPENINGSENSOR_GATEWAY()
 * @method static ChannelFunction OPENINGSENSOR_GATE()
 * @method static ChannelFunction OPENINGSENSOR_GARAGEDOOR()
 * @method static ChannelFunction NOLIQUIDSENSOR()
 * @method static ChannelFunction CONTROLLINGTHEDOORLOCK()
 * @method static ChannelFunction OPENINGSENSOR_DOOR()
 * @method static ChannelFunction CONTROLLINGTHEROLLERSHUTTER()
 * @method static ChannelFunction CONTROLLINGTHEROOFWINDOW()
 * @method static ChannelFunction OPENINGSENSOR_ROLLERSHUTTER()
 * @method static ChannelFunction OPENINGSENSOR_ROOFWINDOW()
 * @method static ChannelFunction POWERSWITCH()
 * @method static ChannelFunction LIGHTSWITCH()
 * @method static ChannelFunction DIMMER()
 * @method static ChannelFunction RGBLIGHTING()
 * @method static ChannelFunction DIMMERANDRGBLIGHTING()
 * @method static ChannelFunction DEPTHSENSOR()
 * @method static ChannelFunction DISTANCESENSOR()
 * @method static ChannelFunction OPENINGSENSOR_WINDOW()
 * @method static ChannelFunction MAILSENSOR()
 * @method static ChannelFunction WINDSENSOR()
 * @method static ChannelFunction PRESSURESENSOR()
 * @method static ChannelFunction RAINSENSOR()
 * @method static ChannelFunction WEIGHTSENSOR()
 * @method static ChannelFunction WEATHER_STATION()
 * @method static ChannelFunction STAIRCASETIMER()
 * @method static ChannelFunction ELECTRICITYMETER()
 * @method static ChannelFunction IC_ELECTRICITYMETER()
 * @method static ChannelFunction IC_GASMETER()
 * @method static ChannelFunction IC_HEATMETER()
 * @method static ChannelFunction IC_WATERMETER()
 * @method static ChannelFunction THERMOSTAT()
 * @method static ChannelFunction THERMOSTATHEATPOLHOMEPLUS()
 * @method static ChannelFunction VALVEOPENCLOSE()
 * @method static ChannelFunction VALVEPERCENTAGE()
 * @method static ChannelFunction GENERAL_PURPOSE_MEASUREMENT()
 * @method static ChannelFunction ACTION_TRIGGER()
 * @method static ChannelFunction DIGIGLASS_VERTICAL()
 * @method static ChannelFunction DIGIGLASS_HORIZONTAL()
 */
final class ChannelFunction extends Enum {
    const UNSUPPORTED = -1;
    const NONE = 0;
    const SCENE = 2000;
    const CONTROLLINGTHEGATEWAYLOCK = 10;
    const CONTROLLINGTHEGATE = 20;
    const CONTROLLINGTHEGARAGEDOOR = 30;
    const THERMOMETER = 40;
    const HUMIDITY = 42;
    const HUMIDITYANDTEMPERATURE = 45;
    const OPENINGSENSOR_GATEWAY = 50;
    const OPENINGSENSOR_GATE = 60;
    const OPENINGSENSOR_GARAGEDOOR = 70;
    const NOLIQUIDSENSOR = 80;
    const CONTROLLINGTHEDOORLOCK = 90;
    const OPENINGSENSOR_DOOR = 100;
    const CONTROLLINGTHEROLLERSHUTTER = 110;
    const CONTROLLINGTHEROOFWINDOW = 115;
    const OPENINGSENSOR_ROLLERSHUTTER = 120;
    const OPENINGSENSOR_ROOFWINDOW = 125;
    const POWERSWITCH = 130;
    const LIGHTSWITCH = 140;
    const DIMMER = 180;
    const RGBLIGHTING = 190;
    const DIMMERANDRGBLIGHTING = 200;
    const DEPTHSENSOR = 210;
    const DISTANCESENSOR = 220;
    const OPENINGSENSOR_WINDOW = 230;
    const MAILSENSOR = 240;
    const WINDSENSOR = 250;
    const PRESSURESENSOR = 260;
    const RAINSENSOR = 270;
    const WEIGHTSENSOR = 280;
    const WEATHER_STATION = 290;
    const STAIRCASETIMER = 300;
    const ELECTRICITYMETER = 310;
    const IC_ELECTRICITYMETER = 315;
    const IC_GASMETER = 320;
    const IC_WATERMETER = 330;
    const IC_HEATMETER = 340;
    const THERMOSTAT = 400;
    const THERMOSTATHEATPOLHOMEPLUS = 410;
    const HVAC_THERMOSTAT = 420;
    const HVAC_THERMOSTAT_HEAT_COOL = 422;
    const HVAC_DRYER = 423;
    const HVAC_FAN = 424;
    const HVAC_THERMOSTAT_DIFFERENTIAL = 425;
    const HVAC_DOMESTIC_HOT_WATER = 426;
    const VALVEOPENCLOSE = 500;
    const VALVEPERCENTAGE = 510;
    const GENERAL_PURPOSE_MEASUREMENT = 520;
    const GENERAL_PURPOSE_METER = 530;
    const ACTION_TRIGGER = 700;
    const DIGIGLASS_HORIZONTAL = 800;
    const DIGIGLASS_VERTICAL = 810;
    const CONTROLLINGTHEFACADEBLIND = 900;
    const TERRACE_AWNING = 910;
    const PROJECTOR_SCREEN = 920;
    const CURTAIN = 930;
    const VERTICAL_BLIND = 940;
    const ROLLER_GARAGE_DOOR = 950;
    const PUMPSWITCH = 960;
    const HEATORCOLDSOURCESWITCH = 970;

    public static function getFunctionNamesToRegisterInStateWebhook() {
        return array_map(function ($function) {
            return (new ChannelFunction($function))->getKey();
        }, self::getFunctionIdsToRegisterInStateWebhook());
    }

    public static function getFunctionIdsToRegisterInStateWebhook() {
        return [
            self::LIGHTSWITCH,
            self::POWERSWITCH,
            self::STAIRCASETIMER,
            self::THERMOMETER,
            self::OPENINGSENSOR_DOOR,
            self::OPENINGSENSOR_GATEWAY,
            self::HUMIDITY,
            self::HUMIDITYANDTEMPERATURE,
            self::OPENINGSENSOR_GATE,
            self::OPENINGSENSOR_GARAGEDOOR,
            self::OPENINGSENSOR_WINDOW,
            self::OPENINGSENSOR_ROOFWINDOW,
            self::MAILSENSOR,
            self::ELECTRICITYMETER,
            self::IC_GASMETER,
            self::IC_WATERMETER,
            self::IC_ELECTRICITYMETER,
            self::NOLIQUIDSENSOR,
            self::WINDSENSOR,
            self::RAINSENSOR,
            self::WEIGHTSENSOR,
            self::DISTANCESENSOR,
            self::DEPTHSENSOR,
            self::PRESSURESENSOR,
            self::OPENINGSENSOR_ROLLERSHUTTER,
            self::ACTION_TRIGGER,
            self::CONTROLLINGTHEROLLERSHUTTER,
            self::HVAC_THERMOSTAT,
            self::HVAC_DOMESTIC_HOT_WATER,
            self::HVAC_FAN,
            self::HVAC_DRYER,
            self::HVAC_THERMOSTAT_DIFFERENTIAL,
            self::HVAC_THERMOSTAT_HEAT_COOL,
        ];
    }

    public static function getFunctionNamesToStoreStateLogs() {
        return array_map(function ($function) {
            return (new ChannelFunction($function))->getKey();
        }, self::getFunctionIdsToStoreStateLogs());
    }

    public static function getFunctionIdsToStoreStateLogs() {
        return array_diff(self::getFunctionIdsToRegisterInStateWebhook(), [
            self::THERMOMETER,
            self::HUMIDITY,
            self::HUMIDITYANDTEMPERATURE,
            self::ELECTRICITYMETER,
            self::IC_GASMETER,
            self::IC_WATERMETER,
            self::IC_ELECTRICITYMETER,
            self::NOLIQUIDSENSOR,
            self::WINDSENSOR,
            self::RAINSENSOR,
            self::WEIGHTSENSOR,
            self::DISTANCESENSOR,
            self::DEPTHSENSOR,
            self::PRESSURESENSOR,
            self::CONTROLLINGTHEROLLERSHUTTER,
        ]);
    }
}
