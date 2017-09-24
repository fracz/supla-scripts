<?php

namespace suplascripts\app\authorization;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Middleware\HttpBasicAuthentication;
use Slim\Middleware\JwtAuthentication;
use suplascripts\models\HasApp;
use suplascripts\models\User;

class JwtAndBasicAuthorizationMiddleware
{
    use HasApp;

    /** @var HttpBasicAuthentication */
    private $httpBasicAuthentication;
    /** @var JwtAuthentication */
    private $jwtAuthentication;

    public function __construct()
    {
        $options = [
            'path' => '/api',
            'logger' => $this->getApp()->logger,
            'secure' => false, // do not force SSL!
            'passthrough' => [
                '/api/info',
                '/api/time',
                '/api/tokens/new',
                '/api/tokens/client',
                '/api/thermostats/preview',
                '/api/users' // TODO chyba za dużo
            ],
        ];

        $container = $this->getApp()->getContainer();

        $this->jwtAuthentication = new JwtAuthentication(array_merge($options, [
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

    public function authenticateWithJwt(RequestInterface $request)
    {
        $this->jwtAuthentication->setRules([]);
        return ($this->jwtAuthentication)($request, new Response(), function () {
        });
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        /** @var Response $response */
        $response = ($this->jwtAuthentication)($request, $response, $next);
        if ($response->getStatusCode() === 401) {
            $response = ($this->httpBasicAuthentication)($request, $response, $next);
        }
        return $response;
    }
}
