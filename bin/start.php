#!/usr/bin/env php
<?php

$dir = dirname(__DIR__);
require_once $dir . "/app/config/bin.php";
$isForceStart = false;

foreach($argv AS $argument){
    if($argument === "--force" || $argument === "-f"){
        $isForceStart = true;
    }
}
if(file_exists($pidfile) ){
    if(!$isForceStart){
        die("[Error] PID file exists. Maybe you have running server?\n");
    }
    unlink($pidfile);
}

exec(sprintf("%s > %s 2>&1 & echo $! >> %s", $cmd, $logFile, $pidfile));
