<?php

namespace suplascripts\app\authorization;

use Monolog\Logger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Tuupola\Middleware\HttpBasicAuthentication;
use suplascripts\app\UserAndUrlAwareLogger;
use suplascripts\models\HasApp;
use suplascripts\models\User;

class JwtAndBasicAuthorizationMiddleware {

    use HasApp;

    /** @var HttpBasicAuthentication */
    private $httpBasicAuthentication;
    /** @var JwtWithBodyAuthentication */
    private $jwtAuthentication;

    public function __construct() {
        $options = [
            'path' => '/api',
            'logger' => new UserAndUrlAwareLogger(Logger::ERROR),
            'secure' => false, // do not force SSL!
            'ignore' => [
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
            'before' => function ($request, $arguments) use ($container) {
                $container['currentToken'] = (object) $arguments['decoded'];
            }
        ]));

        $this->httpBasicAuthentication = new HttpBasicAuthentication(array_merge($options, [
            'authenticator' => new UserAuthenticator(),
            'before' => function ($request, $arguments) use ($container) {
                $container['currentUser'] = User::findByUsername($arguments['user']);
                return $request;
            }
        ]));
    }

    public function authenticateWithJwt(RequestInterface $request) {
        $this->jwtAuthentication->withRules([]);
        $authenticated = false;
        $response = ($this->jwtAuthentication)($request, new Response(), function ($request, $response) use (&$authenticated) {
            $authenticated = true;
            return $response;
        });
        return $authenticated ? null : $response;
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
