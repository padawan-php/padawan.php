#!/usr/bin/env php
<?php

require "app/config/bin.php";
require "vendor/autoload.php";
$app = new App;
$i = 0;
$handler = function ($request, $response) use (&$i, $app){
    ++$i;
    $start = microtime(1);
    printf("%s %s\n", $request->getMethod(), $request->getPath());
    $request->on("data", function($data) use ($request, $response, $app, $start, &$i){
        $response->end($app->handle($request, $response, $data));
        echo (microtime(1) - $start) . "\n";
        if($i % 30 === 0){
            $app->after();
        }
    });
};

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new React\Http\Server($socket);

$http->on('request', $handler);

$socket->listen($port);

echo "Started http server on {$port}\n";

$loop->run();
