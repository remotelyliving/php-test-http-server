<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(-1);

foreach (array(__DIR__ . '/../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php') as $file) {
    if (file_exists($file)) {
        define('COMPOSER_INSTALL', $file);

        break;
    }
}

unset($file);

require COMPOSER_INSTALL;

use RemotelyLiving\PHPTestHttpServer\ResponseWrapper;
use RemotelyLiving\PHPTestHttpServer\Server;

ob_start();

if (!isset($_SERVER[Server::STUB_RESPONSE_HEADER]) && !isset($_GET[Server::STUB_RESPONSE_HEADER])) {
    http_response_code(200);
    echo 'default response';
    exit();
}

/** @var ResponseWrapper $response */
$response = ResponseWrapper::fromBase64($_SERVER[Server::STUB_RESPONSE_HEADER] ?? $_GET[Server::STUB_RESPONSE_HEADER]);
http_response_code($response->getStatusCode());
$statusLine = sprintf(
    'HTTP/%s %s %s',
    $response->getProtocolVersion(),
    $response->getStatusCode(),
    $response->getReasonPhrase()
);
header($statusLine, true, $response->getStatusCode());

foreach($response->getHeaders() as $headerName => $header) {
    header("{$headerName}: {$header}");
}

echo $response->getBody();
ob_end_flush();