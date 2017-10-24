<?php

namespace suplascripts\models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Ramsey\Uuid\Uuid;
use suplascripts\models\encoders\ColumnEncoders;
use Symfony\Component\Yaml\Exception\RuntimeException;

/**
 * @property string $id
 * @property Carbon $createdAt
 * @property Carbon $modifiedAt
 * @method static Model|Builder find(mixed $condition)
 * @method static Builder where(array|string $condition)
 * @method static Builder whereIn(string $column, array $condition)
 * @method static Builder whereBetween(string $column, array $between)
 * @method static Builder select(array $columns)
 * @method static Builder groupBy(string $column)
 * @method static string raw(string $column)
 */
abstract class Model extends \Illuminate\Database\Eloquent\Model {

    use ColumnEncoders;

    const ID = 'id';
    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    public $incrementing = false; // tells eloquent that the id is not integer (sic!), http://stackoverflow.com/a/34715309/878514

    protected $encrypted = [];
    protected $jsonEncoded = [];

    /**
     * Model constructor.
     */
    public function __construct(array $attributes = []) {
        $this->initializeEncoders();
        parent::__construct($attributes);
    }


    public function newInstance($attributes = [], $exists = false) {
        $newInstance = parent::newInstance($attributes, $exists);
        if (!$exists) {
            $this->assignId($newInstance);
        }
        return $newInstance;
    }

    public function save(array $options = []) {
        if (!$this->exists) {
            $this->assignId();
        }
        return parent::save($options);
    }

    protected function assignId($instance = null) {
        if (!$instance) {
            $instance = $this;
        }
        if (!$instance->id) {
            $instance->id = Uuid::getFactory()->uuid4();
        }
    }

    public function getEncrypted(): array {
        return $this->encrypted;
    }

    public function getJsonEncoded() {
        return $this->jsonEncoded;
    }

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('c'); // C is the ATOM format (with timezone offset)
    }

    protected function asDateTime($value) {
        if ($value instanceof \DateTime) {
            $value->setTimezone(new \DateTimeZone('UTC'));
            return $value;
        } elseif (is_string($value)) {
            return new \DateTime($value, new \DateTimeZone('UTC'));
        }
        throw new RuntimeException('Unrecognized date format: ' . $value);
    }
}
