<?php

namespace Padawan\Framework\Project;


use Padawan\Framework\Utils\PathResolver;
use Padawan\Domain\Core\Project;
use \__PHP_Incomplete_Class;
use Amp\File;

/**
 * Class Persister
 */
class Persister
{
    const INDEX_FILE = ".padawan/project";

    public function __construct(PathResolver $path)
    {
        $this->path = $path;
    }

    public function save(Project $project)
    {
        return File\put($this->getProjectIndexFilePath($project->getRootFolder()), $this->serialize($project));
    }

    public function load($rootDir)
    {
        try {
            $project = $this->unserialize(
                $this->readFromFile($this->getProjectIndexFilePath($rootDir))
            );
            if ($project instanceof __PHP_Incomplete_Class) {
                return;
            }
            return $project;
        } catch (\Exception $e) {
            return;
        }
    }
    protected function unserialize($rawProject) {
        return unserialize($rawProject);
    }

    private function getProjectIndexFilePath($rootDir)
    {
        return $this->path->join([
            $rootDir,
            self::INDEX_FILE
        ]);
    }

    private function serialize(Project $project)
    {
        return serialize($project);
    }

    private function readFromFile($filename) {
        return $this->path->read($filename);
    }

    /**
     *
     * @var PathResolver
     */
    private $path;
}
