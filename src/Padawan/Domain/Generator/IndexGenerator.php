<?php

namespace Padawan\Domain\Generator;

use Padawan\Domain\Scope\FileScope;
use Padawan\Domain\Project\Index;
use Padawan\Domain\Project;

interface IndexGenerator
{
    public function generateIndex(Project $project);

    public function generateProjectIndex(Project $project);

    public function processFile(Index $index, $file, $rewrite = false, $createCache = true);

    public function createScopeForFile($file, Index $index, $createCache = true);

    public function processFileScope(Index $index, FileScope $scope);
}
