#!/usr/bin/env php
<?php

$dir = dirname(__DIR__);
require_once $dir . "/app/config/bin.php";

if(!file_exists($pidfile)){
    die("[Error] Server is not running\n");
}

$pid = file_get_contents($pidfile);

exec("kill -9 ". $pid);
unlink($pidfile);
exec('echo "Stopped" >> ' . $logFile);
