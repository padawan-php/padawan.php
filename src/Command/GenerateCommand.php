<?php

namespace Command;

use Entity\Project;

class GenerateCommand extends AbstractCommand
{
    public function run(array $arguments = [])
    {
        $generator = $this->get("Generator\IndexGenerator");
        $rootDir = getcwd();
        if (array_key_exists("rootDir", $arguments)) {
            $rootDir = $arguments["rootDir"];
        }
        $project = new Project(
            $this->get("Entity\Index"),
            $rootDir
        );

        $generator->generateIndex($project);
        $indexWriter = $this->get('IO\Writer');

        $indexWriter->write($project);
        return [
            "status" => "ok"
        ];
    }
}
