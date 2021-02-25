<?php

require_once 'vendor/autoload.php';

use Nyholm\Psr7\Factory\Psr17Factory;
use RemotelyLiving\PHPTestHttpServer\Server;

$server = new Server();
$server->start();

$psr17Factory = new Psr17Factory();
$expected_response = $psr17Factory->createResponse(503, 'farted')->withBody($psr17Factory->createStream('{[1]}'));
$request = $psr17Factory->createRequest('GET', $server->getServerUri());
$psr18Client = new \Buzz\Client\Curl($psr17Factory);
$request = Server::addStubResponse($request, $expected_response);
$actual_response = $psr18Client->sendRequest($request);

