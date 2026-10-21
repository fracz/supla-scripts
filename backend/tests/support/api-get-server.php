<?php

// One-request loopback server: report the received URI and return a sample Cloud profile.
$server = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
if (!$server) {
    throw new RuntimeException($error, $errno);
}
echo stream_socket_get_name($server, false) . "\n";
flush();
$connection = stream_socket_accept($server, 10);
if (!$connection) {
    exit(1);
}
stream_set_timeout($connection, 5);
$requestLine = fgets($connection);
$authorization = null;
while (($line = fgets($connection)) !== false && trim($line) !== '') {
    if (stripos($line, 'Authorization:') === 0) {
        $authorization = trim(substr($line, strlen('Authorization:')));
    }
}
$uri = explode(' ', $requestLine)[1];
$body = json_encode([
    'email' => 'oauth@example.test',
    'shortUniqueId' => 'test-user',
    'uri' => $uri,
    'authorization' => $authorization,
]);
// A strict route, like Cloud: an extra slash must not silently pass this test.
$status = strpos(explode('?', $uri)[0], '/current/') !== false ? '301 Moved Permanently' : '200 OK';
fwrite($connection, "HTTP/1.1 $status\r\nContent-Type: application/json\r\nConnection: close\r\n"
    . 'Content-Length: ' . strlen($body) . "\r\n\r\n" . $body);
fclose($connection);
fclose($server);
