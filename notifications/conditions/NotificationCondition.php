<?php
namespace SuplaScripts\notifications\conditions;

use SuplaScripts\ConfiguredSuplaApiClient;

interface NotificationCondition
{
    /** @return bool|array */
    public function getNotificationToSend(ConfiguredSuplaApiClient $client);
}
