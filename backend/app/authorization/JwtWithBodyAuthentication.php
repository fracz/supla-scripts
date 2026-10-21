<?php
namespace suplascripts\app\authorization;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tuupola\Middleware\JwtAuthentication;

class JwtWithBodyAuthentication {
    private $jwtAuthentication;
    private $cookieName;

    public function __construct(array $options) {
        $this->cookieName = $options['cookie'] ?? 'token';
        $this->jwtAuthentication = new JwtAuthentication($options);
    }

    public function withRules(array $rules) {
        $this->jwtAuthentication = $this->jwtAuthentication->withRules($rules);
        return $this;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next) {
        $hasHeaderToken = preg_match('/Bearer\s+(.*)$/i', $request->getHeaderLine('Authorization'));
        $hasCookieToken = isset($request->getCookieParams()[$this->cookieName]);
        if ($request->getMethod() === 'PATCH' && !$hasHeaderToken && !$hasCookieToken) {
            $contents = json_decode($request->getBody()->getContents(), true);
            if (isset($contents['__token'])) {
                $request = $request->withHeader('Authorization', 'Bearer ' . $contents['__token']);
            }
        }

        return ($this->jwtAuthentication)($request, $response, $next);
    }
}
