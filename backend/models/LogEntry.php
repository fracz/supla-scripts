<?php

namespace suplascripts\models;

/**
 * @property string $module
 * @property string $data
 */
class LogEntry extends Model
{
    const TABLE_NAME = 'logs';
    const MODULE = 'module';
    const DATA = 'data';
    const USER_ID = 'userId';

    protected $table = self::TABLE_NAME;
    protected $fillable = [self::MODULE, self::DATA];
}
