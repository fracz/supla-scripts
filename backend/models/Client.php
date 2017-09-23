<?php

namespace suplascripts\models;

/**
 * @property int $id
 * @property string $label
 * @property bool $active
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
}
