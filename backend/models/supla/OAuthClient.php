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

use Assert\Assertion;
use suplascripts\models\HasApp;
use suplascripts\models\User;

class OAuthClient {
    use HasApp;

    const REQUIRED_SCOPES = ['account_r', 'iodevices_r', 'channels_ea', 'channels_r'];

    public function refreshAccessToken(User $user) {
        $apiCredentials = $user->getApiCredentials();
        Assertion::keyExists($apiCredentials, 'refresh_token', 'We cannot refresh access token because there is no refresh_token.');
        $refreshToken = $apiCredentials['refresh_token'];
        try {
            $this->getApp()->logger->toOauthLog()->notice('Refreshing token.', ['userId' => $user->id, 'username' => $user->username, 'refreshToken' => $refreshToken]);
            $newCredentials = $this->issueNewAccessTokens([
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);
            $user->setApiCredentials($newCredentials);
            $user->save();
        } catch (\Exception $e) {
            $user->refresh();
            $apiCredentials = $user->getApiCredentials();
            $currentRefreshToken = $apiCredentials['refresh_token'];
            if ($currentRefreshToken != $refreshToken) {
                $this->getApp()->logger->toOauthLog()->notice(
                    'Token assumed updated.',
                    ['userId' => $user->id, 'username' => $user->username, 'oldToken' => $refreshToken, 'newToken' => $currentRefreshToken]
                );
            } else {
                throw $e;
            }
        }
    }

    public function issueNewAccessTokens(array $data): array {
        try {
            $address = $this->getApp()->getSetting('oauth')['cloudUrl'];
            $handle = curl_init($address . '/oauth/v2/token');
            $data = array_merge([
                'client_id' => $this->getApp()->getSetting('oauth')['clientId'],
                'client_secret' => $this->getApp()->getSetting('oauth')['secret'],
            ], $data);
            curl_setopt($handle, CURLOPT_POST, true);
            curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
         //   curl_setopt($handle, CURLOPT_COOKIE, 'XDEBUG_SESSION=PHPUNIT'); // uncomment to enable XDEBUG debugging in dev
            $response = curl_exec($handle);
            $responseStatus = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            Assertion::eq(200, $responseStatus, 'Could not issue access token. Response status: ' . $responseStatus . ' ' . $response);
            $response = json_decode($response, true);
            Assertion::keyExists($response, 'access_token');
            Assertion::keyExists($response, 'refresh_token');
            Assertion::keyExists($response, 'scope');
            $scopes = explode(' ', $response['scope']);
            $missingScopes = array_diff(array_merge(self::REQUIRED_SCOPES, ['offline_access']), $scopes);
            Assertion::count($missingScopes, 0, 'Your token is missing some scopes: ' . implode(', ', $missingScopes));
            $this->getApp()->metrics->increment('oauth.token.success');
            $this->getApp()->logger->toOauthLog()->notice('Token issued.', ['requestData' => $data, 'response' => $response]);
            return $response;
        } catch (\Exception $e) {
            $this->getApp()->metrics->increment('oauth.token.failure');
            $this->getApp()->logger->toOauthLog()->error('OAuth error.', [
                'requestData' => $data,
                'responseStatus' => $responseStatus,
                'response' => $response,
            ]);
            throw $e;
        }
    }

    public function getSuplaAddress(string $token) {
        $address = base64_decode(explode('.', $token)[1] ?? '');
        Assertion::string($address, 'Could not decode SUPLA address from the given token.');
        return $address;
    }
}
