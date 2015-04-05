#!/usr/bin/env php
<?php

require(dirname(__DIR__) . "/vendor/autoload.php");

set_time_limit(0);
ini_set('memory_limit','1000M');
ini_set('display_errors', 'stderr');

/** @var $command \Command\CommandInterface */

$arguments = [];

if(php_sapi_name() == 'cli') {
    if(count($argv) < 2) {
        $command = new \Command\ErrorCommand;
    } elseif ($argv[1] == 'generate') {
        $command = new \Command\GenerateCommand;
    } else if($argv[1] == 'update') {
        $command = new \Command\UpdateCommand;
    } else {
        $command = new \Command\ErrorCommand;
    }
} else {
    $command = new \Command\ErrorCommand;
}
$command->run($arguments);
