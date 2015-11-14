<?php

namespace Command;

use Domain\Core\Project;
use Domain\Core\Index;
use Framework\Generator\IndexGenerator;
use Framework\IO\Writer;

class GenerateCommand extends AbstractCommand
{
    public function run(array $arguments = [])
    {
        $generator = $this->get(IndexGenerator::class);
        $rootDir = getcwd();
        if (array_key_exists("rootDir", $arguments)) {
            $rootDir = $arguments["rootDir"];
        }
        $project = new Project(
            $this->get(Index::class),
            $rootDir
        );

        $generator->generateIndex($project);
        $indexWriter = $this->get(Writer::class);

        $indexWriter->write($project);
        return [
            "status" => "ok"
        ];
    }
}
