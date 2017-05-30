<?php
namespace SuplaScripts\notifications\triggers;

use SuplaScripts\ConfiguredSuplaApiClient;

interface NotificationTrigger
{
    /** @return bool|array */
    public function getNotification(ConfiguredSuplaApiClient $client);
}
