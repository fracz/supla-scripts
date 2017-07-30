<?php
if (php_sapi_name() != "cli" && HTTP_BASIC_USER && (!isset($_SERVER['PHP_AUTH_USER']) || $_SERVER['PHP_AUTH_USER'] !== HTTP_BASIC_USER || $_SERVER['PHP_AUTH_PW'] !== HTTP_BASIC_PASSWORD)) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Authentication required.';
    exit;
}
