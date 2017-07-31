<?php

namespace suplascripts\models\encoders;

trait ColumnEncoders
{
    /** @var ColumnEncoder[] */
    private $columnEncoders;

    protected function initializeEncoders()
    {
        $this->columnEncoders = [
            new ColumnJsonEncoder(),
            new ColumnEncryptor(),
        ];
    }

    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);
        $this->encodeAttribute($key);
    }

    protected function getAttributeFromArray($key)
    {
        return $this->decodeAttribute($key, parent::getAttributeFromArray($key));
    }

    protected function getArrayableAttributes()
    {
        return $this->decodeAttributes(parent::getArrayableAttributes());
    }

    public function getAttributes()
    {
        return $this->decodeAttributes(parent::getAttributes());
    }

    private function encodeAttribute($key)
    {
        foreach ($this->columnEncoders as $columnEncoder) {
            if ($columnEncoder->shouldEncode($this, $key) && !$columnEncoder->isEncoded($this->attributes[$key])) {
                $this->attributes[$key] = $columnEncoder->encode($this->attributes[$key]);
            }
        }
    }

    private function decodeAttribute($key, $value)
    {
        foreach ($this->columnEncoders as $columnEncoder) {
            if ($columnEncoder->shouldEncode($this, $key) && $columnEncoder->isEncoded($value)) {
                return $columnEncoder->decode($value);
            }
        }
        return $value;
    }

    private function decodeAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $attributes[$key] = $this->decodeAttribute($key, $value);
        }
        return $attributes;
    }
}
