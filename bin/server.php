#!/usr/bin/env php
<?php

use React\Http\Response;
use React\Http\Request;

define("ROOT", dirname(__DIR__));

require ROOT . "/app/config/bin.php";
require ROOT . "/vendor/autoload.php";

$noFsIO = false;
$port = 15155;
$host = 'localhost';

foreach($argv AS $arg){
    if($arg === '--no-io'){
        $noFsIO = true;
    }
    elseif(strpos($arg, '=') !== false){
        list($name, $value) = explode('=', $arg);
        if($name === '--port'){
            $port = $value;
        }
        elseif($name === '--host'){
            $host = $value;
        }
    }
}

$app = new App($noFsIO);
$handler = function ($request, Response $response) use ($app){
    $start = microtime(1);
    printf("%s %s\n", $request->getMethod(), $request->getPath());
    if($request->getMethod() !== 'POST'){
        $app->setResponseHeaders($response);
        $response->end('');
        return;
    }
    $headers = $request->getHeaders();
    $body = new \stdClass;
    $body->data = "";
    $body->receivedLength = 0;
    $body->dataLength = $headers['Content-Length'];
    $request->on("data", function($data) use (
        $request, $response, $app, $body, $start
    ){
        $body->data .= $data;
        $body->receivedLength += strlen($data);
        if($body->receivedLength >= $body->dataLength){
            $response->end($app->handle($request, $response, $body->data));
            printf("Response time: %s\n", microtime(1) - $start);
        }
    });
};

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket);

$http->on('request', $handler);

$socket->listen($port, $host);

echo "Started http server on {$host}:{$port}\n";

$loop->run();
