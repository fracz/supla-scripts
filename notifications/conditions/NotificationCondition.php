<?php
namespace SuplaScripts\notifications\conditions;

use SuplaScripts\ConfiguredSuplaApiClient;

interface NotificationCondition
{
    /** @return bool */
    public function shouldShowNotification(ConfiguredSuplaApiClient $client);
}
