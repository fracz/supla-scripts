<?php
namespace SuplaScripts\utils\conditions;

use SuplaScripts\ConfiguredSuplaApiClient;

class AnyOfCondition implements StatusCondition
{
    /** @var StatusCondition[] */
    private $conditions;

    /** @param StatusCondition[] $conditions */
    public function __construct(array $conditions)
    {
        $this->conditions = $conditions;
    }

    /** @return bool */
    public function isFulfilled(ConfiguredSuplaApiClient $client)
    {
        foreach ($this->conditions as $condition) {
            if ($condition->isFulfilled($client)) {
                return true;
            }
        }
        return false;
    }
}
