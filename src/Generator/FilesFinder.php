<?php

namespace Generator;

use Utils\PathResolver;
use Entity\Project;

class FilesFinder
{
    public function __construct(PathResolver $path)
    {
        $this->path = $path;
    }

    public function getProjectFiles(Project $project)
    {
        return $this->filterFiles(
            $project,
            $this->path->getDirFilesRecursive(
                $project->getRootDir()
            )
        );
    }

    public function getChangedProjectFiles(Project $project)
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
