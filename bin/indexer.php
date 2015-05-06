#!/usr/bin/env php
<?php

require(dirname(__DIR__) . "/vendor/autoload.php");

set_time_limit(0);
ini_set('memory_limit','2048M');
ini_set('display_errors', 'stderr');

/** @var $command \Command\CommandInterface */

$arguments = [];
$router = new \Router;

$command = $router->getCommand($argv[1]);

$command->run($arguments);
