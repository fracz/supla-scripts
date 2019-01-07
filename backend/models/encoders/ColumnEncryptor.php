<?php

namespace suplascripts\models\encoders;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use suplascripts\app\commands\GenerateEncryptionKeyCommand;
use suplascripts\models\Model;

/**
 * Idea: https://github.com/delatbabel/elocryptfive/blob/5ff5b7a7f38a881596f60f064a9883ecc3942c37/src/Elocrypt.php
 * Trait enhanced from original in order to work without Laravel and with defuse/php-encryption.
 */
class ColumnEncryptor implements ColumnEncoder {

    private static $PREFIX = '__SUPLA_SCRIPTS__:';
    private static $key;

    public static function getCryptoKey(): Key {
        if (!self::$key) {
            $keyAscii = file_get_contents(GenerateEncryptionKeyCommand::KEY_PATH);
            if (!$keyAscii) {
                throw new \RuntimeException('Instance not configured!');
            }
            self::$key = Key::loadFromAsciiSafeString($keyAscii);
        }
        return self::$key;
    }

    public function shouldEncode(Model $model, $key): bool {
        return in_array($key, $model->getEncrypted());
    }

    public function isEncoded($value): bool {
        return $value === null || strpos((string)$value, self::$PREFIX) === 0;
    }

    public function encode($value) {
        return $value !== null ? self::$PREFIX . Crypto::encrypt($value, self::getCryptoKey()) : null;
    }

    public function decode($value) {
        return $value === null ? null : Crypto::decrypt(str_replace(self::$PREFIX, '', $value), self::getCryptoKey());
    }
}
