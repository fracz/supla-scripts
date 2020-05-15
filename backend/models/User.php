<?php

namespace suplascripts\models;

use Assert\Assert;
use Assert\Assertion;
use Illuminate\Database\Eloquent\Relations\HasMany;
use suplascripts\models\notification\Notification;
use suplascripts\models\scene\Scene;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\thermostat\Thermostat;

/**
 * @property int $id
 * @property string $shortUniqueId
 * @property string $username
 * @property string $password
 * @property string $apiCredentials
 * @property \DateTime $lastLoginDate
 * @property \DateTime $tokenExpirationTime
 * @property string $timezone
 * @property Scene[] $scenes
 * @property Client[] $clients
 * @property Notification[] $notifications
 */
class User extends Model {

    const TABLE_NAME = 'users';
    const USERNAME = 'username';
    const SHORT_UNIQUE_ID = 'shortUniqueId';
    const PASSWORD = 'password';
    const API_CREDENTIALS = 'apiCredentials';
    const PUSHOVER_CREDENTIALS = 'pushoverCredentials';
    const LAST_LOGIN_DATE = 'lastLoginDate';
    const LAST_VOICE_COMMAND = 'lastVoiceCommand';
    const TIMEZONE = 'timezone';
    const TOKEN_EXPIRATION_TIME = 'tokenExpirationTime';

    protected $dates = [self::LAST_LOGIN_DATE];

    protected $fillable = [];
    protected $hidden = [self::PASSWORD, self::API_CREDENTIALS, self::PUSHOVER_CREDENTIALS, self::LAST_VOICE_COMMAND];
    protected $encrypted = [self::API_CREDENTIALS, self::PUSHOVER_CREDENTIALS];

    /** @return User|null */
    public static function findByUsername(string $username) {
        return self::where(self::USERNAME, $username)->first();
    }

    public static function create(array $attributes = []) {
        $user = new self([]);
        $attributes = $user->validate($attributes);
        $user->username = trim($attributes[self::USERNAME] ?? '') ?: null;
        $user->shortUniqueId = $attributes[self::SHORT_UNIQUE_ID] ?? null;
        $user->timezone = $attributes[self::TIMEZONE] ?? date_default_timezone_get();
        $user->setApiCredentials($attributes[self::API_CREDENTIALS]);
        $user->setPassword($attributes[self::PASSWORD] ?? null);
        $user->save();
        return $user;
    }

    public function thermostats(): HasMany {
        return $this->hasMany(Thermostat::class, Thermostat::USER_ID);
    }

    public function scenes(): HasMany {
        return $this->hasMany(Scene::class, Scene::USER_ID);
    }

    public function notifications(): HasMany {
        return $this->hasMany(Notification::class, Notification::USER_ID);
    }

    public function clients(): HasMany {
        return $this->hasMany(Client::class, Client::USER_ID);
    }

    public function setPassword($plainPassword) {
        if ($plainPassword) {
            self::validatePlainPassword($plainPassword);
            $this->password = password_hash($plainPassword, PASSWORD_DEFAULT);
        }
    }

    public function isPasswordValid($plainPassword): bool {
        return password_verify($plainPassword, $this->password);
    }

    public function validate(array $attributes = null): array {
        if (!$attributes) {
            $attributes = $this->getAttributes();
        }
        Assertion::notEmptyKey($attributes, self::API_CREDENTIALS);
        if (!isset($attributes[self::SHORT_UNIQUE_ID])) {
            Assertion::notEmptyKey($attributes, self::USERNAME);
            $username = $attributes[self::USERNAME];
            self::validateUsername($username);
            Assertion::notEmptyKey($attributes, self::PASSWORD);
            self::validatePlainPassword($attributes[self::PASSWORD]);
            if (!$this->id) {
                self::validateUsernameUnique($username);
            }
        }
        return $attributes;
    }

    public function setApiCredentials(array $apiCredentials) {
        if (isset($apiCredentials['server'])) {
            $apiCredentials['server'] = preg_replace('#^https?://#', '', $apiCredentials['server']);
        }
        $this->apiCredentials = json_encode($apiCredentials);
        SuplaApi::getInstance($this)->getDevices();
        if (isset($apiCredentials['expires_in']) && $apiCredentials['expires_in'] > 300) {
            $expirationTime = new \DateTime();
            $expirationTime->setTimestamp(time() + $apiCredentials['expires_in']);
            $expirationTime->setTimezone(new \DateTimeZone('UTC'));
            $this->tokenExpirationTime = $expirationTime;
        } else {
            $this->tokenExpirationTime = null;
        }
    }

    public function setPushoverCredentials(array $pushoverCredentials) {
        if ($pushoverCredentials) {
            Assertion::keyExists($pushoverCredentials, 'user');
            Assertion::keyExists($pushoverCredentials, 'token');
            Assertion::count($pushoverCredentials, 2);
        }
        $pushover = new \Pushover();
        $pushover->setToken($pushoverCredentials['token']);
        $pushover->setUser($pushoverCredentials['user']);
        $pushover->setTitle('SUPLA Scripts');
        $pushover->setMessage('Konfiguracja udana');
        $pushover->send();
        $this->pushoverCredentials = $pushoverCredentials ? json_encode($pushoverCredentials) : null;
    }

    public function getApiCredentials(): array {
        return json_decode($this->apiCredentials, true);
    }

    public function trackLastLogin() {
        $this->lastLoginDate = new \DateTime();
        $this->save();
    }

    public function logs(): HasMany {
        return $this->hasMany(LogEntry::class, LogEntry::USER_ID);
    }

    public function log(string $module, $data, $entityId = null) {
        $this->logs()->create([
            LogEntry::MODULE => $module,
            LogEntry::DATA => $data,
            LogEntry::ENTITY_ID => $entityId,
        ])->save();
    }

    public static function validateUsername(string $username) {
        Assert::that($username)
            ->minLength(3, 'Too short username (min 3 characters).')
            ->regex('#^[a-z0-9_]+$#i', 'Username can contain only letters, digits and an underscore (_).');
    }

    public static function validateUsernameUnique(string $username) {
        Assertion::null(self::where(self::USERNAME, $username)->first(), 'Username is taken.', 'username');
    }

    public static function validatePlainPassword(string $plainPassword) {
        Assert::that($plainPassword)
            ->minLength(3, 'Too short password (min 3 characters).');
    }

    public function setTimezone(\DateTimeZone $timezone) {
        $this->timezone = $timezone->getName();
    }

    public function getTimezone(): \DateTimeZone {
        return new \DateTimeZone($this->timezone);
    }

    public function currentDateTimeInUserTimezone(): \DateTime {
        return new \DateTime('now', $this->getTimezone());
    }
}
