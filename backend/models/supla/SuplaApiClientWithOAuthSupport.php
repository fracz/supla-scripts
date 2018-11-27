<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace suplascripts\models\supla;

use Supla\ApiClient\SuplaApiClient;
use suplascripts\app\Application;

class SuplaApiClientWithOAuthSupport extends SuplaApiClient {
    private $serverParams;

    public function __construct($server_params, $auto_logout = true, $debug = false, $sslVerify = true) {
        $this->serverParams = $server_params;
        if ($this->isOAuth()) {
            $server_params['server'] = base64_decode(explode('.', $server_params['access_token'])[1] ?? '');
            $server_params['server'] = preg_replace('#^https?://#', '', $server_params['server']);
            $server_params = array_merge(['clientId' => null, 'secret' => null, 'username' => null, 'password' => null], $server_params);
        }
        parent::__construct($server_params, $auto_logout, $debug, $sslVerify);
    }

    private function isOAuth(): bool {
        return array_key_exists('access_token', $this->serverParams);
    }

    public function logout() {
        if (!$this->isOAuth()) {
            parent::logout();
        }
    }

    protected function accessTokenExists() {
        return $this->isOAuth() || parent::accessTokenExists();
    }

    protected function getAccessToken() {
        if ($this->isOAuth()) {
            return $this->serverParams['access_token'];
        } else {
            return parent::getAccessToken();
        }
    }

    public function remoteRequest($data, $path, $method = 'POST', $bearer = false) {
        $result = parent::remoteRequest($data, $path, $method, $bearer);
        if (!$result && $this->getLastError() == 'HTTP: 401' && array_key_exists('refresh_token', $this->serverParams)) {
            $user = Application::getInstance()->getCurrentUser();
            (new OAuthClient())->refreshAccessToken($user);
            $this->serverParams = $user->getApiCredentials();
            $result = parent::remoteRequest($data, $path, $method, $bearer);
        }
        return $result;
    }
}
