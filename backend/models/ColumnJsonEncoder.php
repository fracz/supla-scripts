<?php

namespace suplascripts\models;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\CryptoException;
use Defuse\Crypto\Key;
use suplascripts\app\commands\GenerateEncryptionKeyCommand;

trait ColumnJsonEncoder
{
    private function shouldEncode($key)
    {
        return in_array($key, $this->jsonEncoded ?? []);
    }

    private function isEncoded($value)
    {
        return $value && is_string($value);
    }

    private function encodedAttribute($value)
    {
        return json_encode($value);
    }

    private function decodedAttribute($value)
    {
        return json_decode($value, true);
    }

    private function doEncodeAttribute($key)
    {
        if ($this->shouldEncode($key) && !$this->isEncoded($this->attributes[$key])) {
            try {
                $this->attributes[$key] = $this->encodedAttribute($this->attributes[$key]);
            } catch (CryptoException $e) {
            }
        }
    }

    private function doDecodeAttribute($key, $value)
    {
        if ($this->shouldEncode($key) && $this->isEncoded($value)) {
            try {
                return $this->decodedAttribute($value);
            } catch (CryptoException $e) {
            }
        }
        return $value;
    }

    private function doDecodeAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            $attributes[$key] = $this->doDecodeAttribute($key, $value);
        }
        return $attributes;
    }

    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);
        $this->doEncodeAttribute($key);
    }

    protected function getAttributeFromArray($key)
    {
        return $this->doDecodeAttribute($key, parent::getAttributeFromArray($key));
    }

    protected function getArrayableAttributes()
    {
        return $this->doDecodeAttributes(parent::getArrayableAttributes());
    }

    public function getAttributes()
    {
        return $this->doDecodeAttributes(parent::getAttributes());
    }
}
