#!/usr/bin/env php
<?php

require(__DIR__ . "/vendor/autoload.php");

set_time_limit(0);
ini_set('memory_limit','1000M');
ini_set('display_errors', 'stderr');
if(php_sapi_name() == 'cli') {
    if(count($argv) < 2) {
        echo "error: not enough arguments";
    } elseif ($argv[1] == 'generate') {
        array_shift($argv);
        array_shift($argv);

        $verbose = false;
        if(count($argv) > 0 && $argv[0] == '-verbose') {
            array_shift($argv);
            $verbose = true;
        }

        $phpCompletePsr  = new IndexGenerator($verbose);

        $plugins = explode("-u", implode("", $argv));
        foreach ($plugins as $pluginFile) {
            if(empty($pluginFile)) {
                continue;
            }
            $phpCompletePsr->addPlugin(trim($pluginFile));
        }

        $index  = $phpCompletePsr->generateIndex();

        $jsonIndex = json_encode($index);
        $lastJsonError = json_last_error();
        if($lastJsonError != JSON_ERROR_NONE) {
            printJsonError($lastJsonError);
            exit;
        }

        $phpCompletePsr->writeToFile($phpCompletePsr->getIndexFileName(), $jsonIndex);
        $phpCompletePsr->writeToFile($phpCompletePsr->getReportFileName(), implode("\n", $phpCompletePsr->getInvalidClasses()));
    } else if($argv[1] == 'update') {
        array_shift($argv);
        array_shift($argv);
        $file = array_shift($argv);
        $cacheFileName = array_shift($argv);
        $verbose = false;

        $p = new IndexGenerator($verbose);
        $plugins = explode("-u", implode("", $argv));
        foreach ($plugins as $pluginFile) {
            if(empty($pluginFile)) {
                continue;
            }
            $p->addPlugin($pluginFile);
        }

        $p->writeUpdatedClassInfo($file, $cacheFileName);
        //echo "Time Elapsed: ".(microtime(true) - $time)."s\n";
        //echo "highest memory ".  memory_get_peak_usage();
    } else {
        echo "not a valid argument";
        exit;
    }
} else {
    exit;
}

function printJsonError($errorCode)
{
    switch (json_last_error()) {
    case JSON_ERROR_NONE:
        echo ' - No errors';
        break;
    case JSON_ERROR_DEPTH:
        echo ' - Maximum stack depth exceeded';
        break;
    case JSON_ERROR_STATE_MISMATCH:
        echo ' - Underflow or the modes mismatch';
        break;
    case JSON_ERROR_CTRL_CHAR:
        echo ' - Unexpected control character found';
        break;
    case JSON_ERROR_SYNTAX:
        echo ' - Syntax error, malformed JSON';
        break;
    case JSON_ERROR_UTF8:
        echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
    default:
        echo ' - Unknown error';
        break;
    }
    echo "\n";
}

