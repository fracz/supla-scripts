<?php

namespace suplascripts\models\supla;

use Supla\ApiClient\SuplaApiClient;
use suplascripts\controllers\exceptions\ApiException;

class SuplaApiException extends ApiException {

    public function __construct(SuplaApiClient $client, string $message = null) {
        parent::__construct('Error when communicating with Supla API: ' . $client->getLastError() . $message, 503);
    }
}
