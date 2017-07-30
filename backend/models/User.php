<?php

namespace suplascripts\models;

use Assert\Assert;
use Assert\Assertion;
use Assert\InvalidArgumentException;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property \DateTime $lastLoginDate
 */
class User extends Model
{
    const TABLE_NAME = 'users';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const API_CREDENTIALS = 'apiCredentials';
    const LAST_LOGIN_DATE = 'lastLoginDate';

    protected $dates = [self::LAST_LOGIN_DATE];

    protected $fillable = [];
    protected $hidden = [self::PASSWORD, self::API_CREDENTIALS];
    protected $encrypted = [self::API_CREDENTIALS];

    public static function findByUsername(string $username): ?User
    {
        $user = self::where(self::USERNAME, $username)->first();
        if ($user && $user->deleted) {
            $user = null;
        }
        return $user;
    }

    public static function create(array $attributes = [])
    {
        $user = new self($attributes);
        $user->passwordExpiresOn = new \DateTime();
        list($username, $attributes) = $user->validate($attributes);
        $user->username = trim($username);
        if (isset($attributes[self::PASSWORD])) {
            $user->setPassword($attributes[self::PASSWORD]);
        }
        $user->save();
        return $user;
    }

    public function setPassword($plainPassword)
    {
        self::validatePlainPassword($plainPassword);
        $this->password = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function isPasswordValid($plainPassword): bool
    {
        return password_verify($plainPassword, $this->password);
    }

    public function validate(array $attributes = null): array
    {
        if (!$attributes) {
            $attributes = $this->getAttributes();
        }
        Assertion::notEmptyKey($attributes, self::USERNAME);
        $username = $attributes[self::USERNAME];
        self::validateUsername($username);
        if (!$this->id) {
            self::validateUsernameUnique($username);
        }
        return [$username, $attributes];
    }

    public function trackLastLogin()
    {
        $this->lastLoginDate = new \DateTime();
        $this->save();
    }

    public static function validateUsername(string $username): void
    {
        Assert::that($username)
            ->minLength(3, 'Too short username (min 3 characters).')
            ->regex('#^[a-z0-9_]+$#i', 'Username can contain only letters, digits and an underscore (_).');
    }

    public static function validateUsernameUnique(string $username): void
    {
        Assertion::null(self::where(self::USERNAME, $username)->first(), 'Username is taken.', 'username');
    }

    public static function validatePlainPassword(string $plainPassword): void
    {
        Assert::that($plainPassword)
            ->minLength(3, 'Too short password (min 3 characters).');
    }

    private static function isPlainPasswordValid(string $plainPassword): bool
    {
        try {
            self::validatePlainPassword($plainPassword);
            return true;
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }
}
