<?php

namespace suplascripts\models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 * @property Carbon $createdAt
 * @property Carbon $modifiedAt
 * @method static Builder where(array $condition)
 * @method static Builder whereIn(string $column, array $condition)
 * @method static Builder whereBetween(string $column, array $between)
 * @method static Builder select(array $columns)
 * @method static Builder groupBy(string $column)
 * @method static string raw(string $column)
 */
abstract class Model extends \Illuminate\Database\Eloquent\Model
{
    use ColumnEncryptor;

    const ID = 'id';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    public $incrementing = false; // tells eloquent that the id is not integer (sic!), http://stackoverflow.com/a/34715309/878514

    protected $encrypted = [];

    public function newInstance($attributes = [], $exists = false)
    {
        $newInstance = parent::newInstance($attributes, $exists);
        if (!$exists) {
            $this->assignId($newInstance);
        }
        return $newInstance;
    }

    public function save(array $options = [])
    {
        if (!$this->exists) {
            $this->assignId();
        }
        return parent::save($options);
    }

    protected function assignId($instance = null)
    {
        if (!$instance) {
            $instance = $this;
        }
        if (!$instance->id) {
            $instance->id = Uuid::getFactory()->uuid4();
        }
    }

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('c'); // C is the ATOM format (with timezone offset)
    }
}
