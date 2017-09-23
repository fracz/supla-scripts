<?php

namespace suplascripts\models;

/**
 * @property int $id
 * @property string $label
 * @property bool $active
 * @property \DateTime $lastConnectionDate
 */
class Client extends Model
{
    const TABLE_NAME = 'clients';
    const LABEL = 'label';
    const ACTIVE = 'active';
    const LAST_CONNECTION_DATE = 'lastConnectionDate';
    const USER_ID = 'userId';

    protected $dates = [self::LAST_CONNECTION_DATE];
    protected $fillable = [self::LABEL, self::ACTIVE];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (!$this->lastConnectionDate) {
            $this->updateLastConnectionDate();
        }
    }

    public function updateLastConnectionDate()
    {
        $this->lastConnectionDate = new \DateTime();
    }
}
