<?php

use suplascripts\database\migrations\Migration;
use suplascripts\models\thermostat\ThermostatProfile;

class FixProfileConditions extends Migration
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
                        if (is_numeric($activeOn['timeRange']['timeStart'])) {
                            $activeOn['timeRange']['timeStart'] =
                                $this->timeSpecToDateTimeAtomInEuropeWarsawTimezone($activeOn['timeRange']['timeStart']);
                        }
                        if (is_numeric($activeOn['timeRange']['timeEnd'])) {
                            $activeOn['timeRange']['timeEnd'] =
                                $this->timeSpecToDateTimeAtomInEuropeWarsawTimezone($activeOn['timeRange']['timeEnd']);
                        }
                    }
                    $activeOns[] = $activeOn;
                }
                $profile->activeOn = $activeOns;
                $profile->save();
            }
        }
    }

    // at the time of migration, we assumed that all time specs were in Europe/Warsaw timezone
    private function timeSpecToDateTimeAtomInEuropeWarsawTimezone($timeSpec): string
    {
        $datetime = new \DateTime('2017-01-01 00:00:00', new \DateTimeZone('Europe/Warsaw'));
        $datetime->setTime($timeSpec / 60, $timeSpec % 60);
        return $datetime->format(\DateTime::ATOM);
    }
}
