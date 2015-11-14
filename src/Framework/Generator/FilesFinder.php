<?php

namespace Framework\Generator;

use Framework\Utils\PathResolver;
use Domain\Core\Project;
use Domain\Generator\FilesFinder as FilesFinderInterface;

class FilesFinder implements FilesFinderInterface
{
    public function __construct(PathResolver $path)
    {
        $this->path = $path;
    }

    public function findProjectFiles(Project $project)
    {
        return $this->filterFiles(
            $project,
            $this->path->getDirFilesRecursive(
                $project->getRootDir()
            )
        );
    }

    public function findChangedProjectFiles(Project $project)
    {
        throw new \Exception("Not implemented yet");
    }

    protected function filterFiles(Project $project, $files)
    {
        $projectFiles = [];
        foreach ($files as $file) {
            if (!preg_match('/\.php$/', $file)) {
                continue;
            }
            $projectFiles[] = $this->path->relative($project->getRootDir(), $file);
        }
        return $projectFiles;
    }
    /** @var PathResolver */
    private $path;
}
