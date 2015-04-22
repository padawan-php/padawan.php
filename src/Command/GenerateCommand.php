<?php

namespace Command;

use Entity\Project;

class GenerateCommand extends AbstractCommand{
    public function run(array $arguments = []){
        $time = microtime(true);
        $verbose = $this->isVerbose($arguments);
        $generator = $this->get("Generator\IndexGenerator");
        $rootDir = getcwd();
        // @TODO this should be somehow configured
        exec("composer dumpautoload -o");
        if(array_key_exists("rootDir", $arguments)){
            $rootDir = $arguments["rootDir"];
        }
        $project = new Project(
            $this->get("Entity\Index"),
            $rootDir
        );

        $index = $generator->generateIndex($project);
        $indexWriter = $this->get('IO\Writer');

        $indexWriter->write($project);
        return [
            "status" => "ok"
        ];
    }
    protected function printJsonError($errorCode)
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
}
