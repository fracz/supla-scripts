<?php

namespace suplascripts\models;

use Assert\Assert;
use Assert\Assertion;
use Illuminate\Database\Eloquent\Relations\HasMany;
use suplascripts\models\scene\Scene;
use suplascripts\models\supla\SuplaApi;
use suplascripts\models\thermostat\Thermostat;
use suplascripts\models\voice\VoiceCommand;

/**
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $apiCredentials
 * @property \DateTime $lastLoginDate
 * @property string $timezone
 */
class User extends Model {

    const TABLE_NAME = 'users';
    const USERNAME = 'username';
    const PASSWORD = 'password';
    const API_CREDENTIALS = 'apiCredentials';
    const LAST_LOGIN_DATE = 'lastLoginDate';
    const LAST_VOICE_COMMAND = 'lastVoiceCommand';
    const TIMEZONE = 'timezone';

    protected $dates = [self::LAST_LOGIN_DATE];

    protected $fillable = [];
    protected $hidden = [self::PASSWORD, self::API_CREDENTIALS, self::LAST_VOICE_COMMAND];
    protected $encrypted = [self::API_CREDENTIALS];

    /** @return User|null */
    public static function findByUsername(string $username) {
        $user = self::where(self::USERNAME, $username)->first();
        if ($user && $user->deleted) {
            $user = null;
        }
        return $user;
    }

    public static function create(array $attributes = []) {
        $user = new self([]);
        list($username, $attributes) = $user->validate($attributes);
        $user->username = trim($username);
        $user->timezone = $attributes[self::TIMEZONE] ?? date_default_timezone_get();
        $user->setApiCredentials($attributes[self::API_CREDENTIALS]);
        $user->setPassword($attributes[self::PASSWORD]);
        $user->save();
        return $user;
    }

    public function thermostats(): HasMany {
        return $this->hasMany(Thermostat::class, Thermostat::USER_ID);
    }

    public function voiceCommands(): HasMany {
        return $this->hasMany(VoiceCommand::class, VoiceCommand::USER_ID);
    }

    public function scenes(): HasMany {
        return $this->hasMany(Scene::class, Scene::USER_ID);
    }

    public function setPassword($plainPassword) {
        self::validatePlainPassword($plainPassword);
        $this->password = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function isPasswordValid($plainPassword): bool {
        return password_verify($plainPassword, $this->password);
    }

    public function validate(array $attributes = null): array {
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

    public function setApiCredentials(array $apiCredentials) {
        $apiCredentials['server'] = preg_replace('#^https?://#', '', $apiCredentials['server']);
        $this->apiCredentials = json_encode($apiCredentials);
        SuplaApi::getInstance($this)->getDevices();
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
