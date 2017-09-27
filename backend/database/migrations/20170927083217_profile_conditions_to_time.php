<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\thermostat\ThermostatProfile;

class ProfileConditionsToTime extends Migration
{
    public function change()
    {
        $profiles = ThermostatProfile::all();
        foreach ($profiles as $profile) {
            /** @var ThermostatProfile $profile */
            if ($profile->activeOn) {
                $activeOns = [];
                foreach ($profile->activeOn as $activeOn) {
                    if (isset($activeOn['timeRange'])) {
                        if (is_string($activeOn['timeRange']['timeStart'])) {
                            $activeOn['timeRange']['timeStart'] =
                                $this->dateTimeToTime($activeOn['timeRange']['timeStart']);
                        }
                        if (is_string($activeOn['timeRange']['timeEnd'])) {
                            $activeOn['timeRange']['timeEnd'] =
                                $this->dateTimeToTime($activeOn['timeRange']['timeEnd']);
                        }
                    }
                    $activeOns[] = $activeOn;
                }
                $profile->activeOn = $activeOns;
                $profile->save();
            }
        }
    }

    private function dateTimeToTime($dateTimeString)
    {
        $datetime = new \DateTime($dateTimeString);
        return $datetime->format('H:i');
    }
}
