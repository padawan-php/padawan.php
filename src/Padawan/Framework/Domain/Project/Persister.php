<?php

namespace Padawan\Framework\Domain\Project;


use Padawan\Framework\Utils\PathResolver;
use Padawan\Domain\Project;
use \__PHP_Incomplete_Class;
use Amp\File;

/**
 * Class Persister
 */
class Persister
{
    const PADAWAN_DIR = ".padawan";
    const INDEX_FILE = "project";

    public function __construct(PathResolver $path)
    {
        $this->path = $path;
    }

    public function save(Project $project)
    {
        $this->checkForPadawanDir($project->getRootFolder());
        return File\put(
            $this->getProjectIndexFilePath($project->getRootFolder()),
            $this->serialize($project)
        );
    }

    public function load($rootDir)
    {
        try {
            $project = $this->unserialize(
                $this->readFromFile($this->getProjectIndexFilePath($rootDir))
            );
            if ($project instanceof __PHP_Incomplete_Class
                || $project->getIndex() instanceof __PHP_Incomplete_Class
            ) {
                return;
            }
            return $project;
        } catch (\Exception $e) {
            return;
        }
    }

    private function unserialize($rawProject)
    {
        return unserialize($rawProject);
    }

    private function getProjectIndexFilePath($rootDir)
    {
        return $this->path->join([
            $rootDir,
            self::PADAWAN_DIR,
            self::INDEX_FILE
        ]);
    }

    private function serialize(Project $project)
    {
        return serialize($project);
    }

    private function readFromFile($filename)
    {
        return $this->path->read($filename);
    }

    private function checkForPadawanDir($dir)
    {
        $padawanDir = $this->path->join([$dir, self::PADAWAN_DIR]);
        if ($this->path->isDir($padawanDir)) {
            return;
        }
        if ($this->path->exists($padawanDir)) {
            $this->path->remove($padawanDir);
        }
        $this->path->create($padawanDir, true);
    }

    /**
     *
     * @var PathResolver
     */
    private $path;
}
