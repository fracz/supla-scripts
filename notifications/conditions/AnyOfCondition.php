<?php
namespace SuplaScripts\notifications\conditions;

use SuplaScripts\ConfiguredSuplaApiClient;

class AnyOfCondition implements NotificationCondition
{
    /** @var NotificationCondition[] */
    private $conditions;

    /** @param NotificationCondition[] $conditions */
    public function __construct(array $conditions)
    {
        $this->conditions = $conditions;
    }

    /** @return bool */
    public function shouldShowNotification(ConfiguredSuplaApiClient $client)
    {
        foreach ($this->conditions as $condition) {
            if ($condition->shouldShowNotification($client)) {
                return true;
            }
        }
        return false;
    }
}
