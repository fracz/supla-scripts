<?php
namespace SuplaScripts\utils\conditions;

use SuplaScripts\ConfiguredSuplaApiClient;

interface StatusCondition
{
    /** @return bool */
    public function isFulfilled(ConfiguredSuplaApiClient $client);
}
