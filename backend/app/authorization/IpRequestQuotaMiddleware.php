<?php

namespace suplascripts\app\authorization;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use suplascripts\app\UserAndUrlAwareLogger;
use suplascripts\models\HasApp;

class IpRequestQuotaMiddleware {
    use HasApp;

    const TABLE_NAME = 'api_quota';
    const IP = 'ip';
    const TIMESTAMP = 'minute_timestamp';
    const COUNT = 'counter';

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
        $maxRequestsPerMinute = $this->getApp()->getSetting('requestQuota', [])['perIpPerMinute'] ?? 60;
        if (!$maxRequestsPerMinute) {
            return;
        }
        $sqlUpdate = <<<QUERY
INSERT INTO `api_quota`(`ip`, `counter`, `minute_timestamp`) VALUES (INET_ATON(:ip), 1,  FLOOR(UNIX_TIMESTAMP() / 60))
ON DUPLICATE KEY 
UPDATE counter = IF(counter >= :maxRequests, counter, counter + 1);
QUERY;
        $rowCount = $this->getApp()->db->getConnection()->affectingStatement($sqlUpdate, ['ip' => $ip, 'maxRequests' => $maxRequestsPerMinute]);
        if ($rowCount) {
            return $next($request, $response);
        } else {
            $this->getApp()->metrics->increment('ip_quota_hit');
            (new UserAndUrlAwareLogger())->toQuotaLog()->error('IP quota exceeded', [
                'ip' => $ip,
                'url' => $_SERVER['REQUEST_URI'],
                'authorization' => $request->getHeader('Authorization'),
            ]);
            return $response->withStatus(429)
                ->withHeader('Content-Type', 'application/json')
                ->withJson([
                    'error' => 'Dear SUPLER, you have sent too many requests. If you miss some feature, ask on the forum. ' .
                        'Don\'t overuse this instance because lots of other people want to use it, too. If you desperately need so many requests, ' .
                        'install your own instance of SUPLA Scripts and disable the requests quota. It\'s easy.'
                ]);
        }
    }
}
