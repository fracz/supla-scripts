<?php
namespace suplascripts\models\automate;

use suplascripts\app\Application;
use suplascripts\models\scene\FeedbackTwigExtension;
use suplascripts\models\User;

class AutomateSender {
    /** @var array|null */
    private $credentials;

    public function __construct($automateCredentials) {
        if ($automateCredentials instanceof User) {
            $automateCredentials = $automateCredentials->getAutomateCredentials();
        }
        $this->credentials = $automateCredentials;
    }

    public function sendCommand(string $type, $payload) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://llamalab.com/automate/cloud/message');
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, FeedbackTwigExtension::URL_FETCH_TIMEOUT);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, FeedbackTwigExtension::URL_FETCH_TIMEOUT);
        if (Application::getInstance()->getSetting('ignoreSslErrors', false)) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        $data = $this->credentials;
        $data['payload'] = json_encode(['command' => $type, 'data' => $payload]);
        $data = json_encode($data);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_exec($ch);
        curl_close($ch);
    }
}
