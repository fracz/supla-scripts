<?php

namespace suplascripts\models;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\CryptoException;
use Defuse\Crypto\Key;
use suplascripts\app\commands\GenerateEncryptionKeyCommand;

/**
 * Idea: https://github.com/delatbabel/elocryptfive/blob/5ff5b7a7f38a881596f60f064a9883ecc3942c37/src/Elocrypt.php
 * Trait enhanced from original in order to work without Laravel and with defuse/php-encryption.
 */
trait ColumnEncryptor
{
    private static $PREFIX = '__SUPLA_SCRIPTS__:';
    private static $key;

    public static function getCryptoKey(): Key
    {
        if (!self::$key) {
            $keyAscii = file_get_contents(GenerateEncryptionKeyCommand::KEY_PATH);
            if (!$keyAscii) {
                throw new \RuntimeException('Instance not configured!');
            }
            self::$key = Key::loadFromAsciiSafeString($keyAscii);
        }
        return self::$key;
    }

    private function shouldEncrypt($key)
    {
        return in_array($key, $this->encrypted ?? []);
    }

    private function isEncrypted($value)
    {
        return $value === null || strpos((string)$value, self::$PREFIX) === 0;
    }

    private function encryptedAttribute($value)
    {
        return self::$PREFIX . Crypto::encrypt($value, self::getCryptoKey());
    }

    private function decryptedAttribute($value)
    {
        return Crypto::decrypt(str_replace(self::$PREFIX, '', $value), self::getCryptoKey());
    }

    private function doEncryptAttribute($key)
    {
        if ($this->shouldEncrypt($key) && !$this->isEncrypted($this->attributes[$key])) {
            try {
                $this->attributes[$key] = $this->encryptedAttribute($this->attributes[$key]);
            } catch (CryptoException $e) {
            }
        }
    }

    private function doDecryptAttribute($key, $value)
    {
        if ($this->shouldEncrypt($key) && $this->isEncrypted($value)) {
            try {
                return $this->decryptedAttribute($value);
            } catch (CryptoException $e) {
            }
        }
        return $value;
    }

    private function doDecryptAttributes($attributes)
    {
        foreach ($attributes as $key => $value) {
            $attributes[$key] = $this->doDecryptAttribute($key, $value);
        }
        return $attributes;
    }

    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);
        $this->doEncryptAttribute($key);
    }

    protected function getAttributeFromArray($key)
    {
        return $this->doDecryptAttribute($key, parent::getAttributeFromArray($key));
    }

    protected function getArrayableAttributes()
    {
        return $this->doDecryptAttributes(parent::getArrayableAttributes());
    }

    public function getAttributes()
    {
        return $this->doDecryptAttributes(parent::getAttributes());
    }
}
