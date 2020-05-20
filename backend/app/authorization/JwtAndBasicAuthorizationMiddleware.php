<?php

namespace suplascripts\app\authorization;

use Monolog\Logger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Middleware\HttpBasicAuthentication;
use Slim\Middleware\JwtAuthentication;
use suplascripts\app\UserAndUrlAwareLogger;
use suplascripts\models\HasApp;
use suplascripts\models\User;

class JwtAndBasicAuthorizationMiddleware {

    use HasApp;

    /** @var HttpBasicAuthentication */
    private $httpBasicAuthentication;
    /** @var JwtAuthentication */
    private $jwtAuthentication;

    public function __construct() {
        $options = [
            'path' => '/api',
            'logger' => new UserAndUrlAwareLogger(Logger::ERROR),
            'secure' => false, // do not force SSL!
            'passthrough' => [
                '/api/info',
                '/api/oauth',
                '/api/scenes/public',
                '/api/tokens/new',
                '/api/tokens/personal',
                '/api/tokens/client',
                '/api/thermostats/preview',
                '/api/users/register',
                '/api/state-webhook',
            ],
        ];

        $container = $this->getApp()->getContainer();

        $this->jwtAuthentication = new JwtWithBodyAuthentication(array_merge($options, [
            'secret' => $this->getApp()->getSetting('jwt')['key'],
            'algorithm' => ['HS256'],
            'callback' => function ($request, $response, $arguments) use ($container) {
                $container['currentToken'] = $arguments['decoded'];
            }
        ]));

        $this->httpBasicAuthentication = new HttpBasicAuthentication(array_merge($options, [
            'authenticator' => new UserAuthenticator(),
            'callback' => function ($request, $response, $arguments) use ($container) {
                $container['currentUser'] = User::findByUsername($arguments['user']);
            }
        ]));
    }

    public function authenticateWithJwt(RequestInterface $request) {
        $this->jwtAuthentication->setRules([]);
        return ($this->jwtAuthentication)($request, new Response(), function () {
        });
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next) {
        /** @var Response $response */
        $response = ($this->jwtAuthentication)($request, $response, $next);
        if ($response->getStatusCode() === 401) {
            $response = ($this->httpBasicAuthentication)($request, $response, $next);
        }
        return $response;
    }
}
