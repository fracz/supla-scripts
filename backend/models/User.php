<?php

namespace suplascripts\models;

use Assert\Assert;
use Assert\Assertion;
use Illuminate\Database\Eloquent\Relations\HasMany;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\thermostat\Thermostat;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $apiCredentials
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
        $user = new self([]);
        list($username, $attributes) = $user->validate($attributes);
        $user->username = trim($username);
        $user->setApiCredentials($attributes[self::API_CREDENTIALS]);

        $user->setPassword($attributes[self::PASSWORD]);
        $user->save();
        return $user;
    }

    public function thermostats(): HasMany
    {
        return $this->hasMany(Thermostat::class, Thermostat::USER_ID);
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
        Assertion::notEmptyKey($attributes, self::PASSWORD);
        Assertion::notEmptyKey($attributes, self::API_CREDENTIALS);
        $username = $attributes[self::USERNAME];
        self::validateUsername($username);
        self::validatePlainPassword($attributes[self::PASSWORD]);
        if (!$this->id) {
            self::validateUsernameUnique($username);
        }
        return [$username, $attributes];
    }

    public function setApiCredentials(array $apiCredentials)
    {
        $apiCredentials['server'] = preg_replace('#^https?://#', '', $apiCredentials['server']);
        $this->apiCredentials = json_encode($apiCredentials);
        (new SuplaApi($this))->getDevices();
    }

    public function getApiCredentials(): array
    {
        return json_decode($this->apiCredentials, true);
    }

    public function trackLastLogin()
    {
        $this->lastLoginDate = new \DateTime();
        $this->save();
    }

    public function logs(): HasMany
    {
        return $this->hasMany(LogEntry::class, LogEntry::USER_ID);
    }

    public function log(string $module, $data)
    {
        $this->logs()->create([
            LogEntry::MODULE => $module,
            LogEntry::DATA => $data,
        ])->save();
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
}
