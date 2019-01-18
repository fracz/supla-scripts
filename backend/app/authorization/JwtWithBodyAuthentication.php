<?php
namespace suplascripts\app\authorization;

use Psr\Http\Message\RequestInterface;
use Slim\Middleware\JwtAuthentication;

class JwtWithBodyAuthentication extends JwtAuthentication {
    public function fetchToken(RequestInterface $request) {
        $token = parent::fetchToken($request);
        if (!$token && $request->getMethod() == 'PATCH') {
            $contents = $request->getBody()->getContents();
            $contents = json_decode($contents, true);
            if ($contents && isset($contents['__token'])) {
                return $contents['__token'];
            }
        }
        return $token;
    }
}
