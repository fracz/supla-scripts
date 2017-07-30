<?php

namespace suplascripts\middlewares;

use Firebase\JWT\JWT;
use suplascripts\app\Application;

class JwtCheckerMiddleware
{
    /**
     * @param  \Psr\Http\Message\ServerRequestInterface $request
     * @param  \Psr\Http\Message\ResponseInterface $response
     * @param  callable $next Next middleware
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {
        $app = Application::getInstance();
        $header = current($request->getHeader('Authorization'));
        $currentToken = null;
        if ($header) {
            preg_match('#^Bearer (.+)$#', $header, $match);
            if ($match) {
                $token = $match[1];
                try {
                    $currentToken = JWT::decode($token, $app->getSetting('jwt')['key'], ['HS256']);
                } catch (\Exception $e) {
                    error_log($e);
                    $app->logger->warning("Could not decode JWT Token! " . $e->getMessage());
                }
            }
        }
        $app->getContainer()['currentToken'] = $currentToken;
        return $next($request, $response);
    }
}
