<?php

$rootDir = dirname(dirname(__DIR__));
$logFile = $rootDir . '/app/logs/app.log';
$pidfile = $rootDir . '/server.pid';
$cmd = "php " . $rootDir . "/server.php";
$port = 15155;
