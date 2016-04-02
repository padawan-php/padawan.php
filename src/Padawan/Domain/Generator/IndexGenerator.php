<?php

namespace Padawan\Domain\Generator;

use Padawan\Domain\Core\Completion\Scope\FileScope;
use Padawan\Domain\Core\Index;
use Padawan\Domain\Core\Project;

interface IndexGenerator
{
    public function generateIndex(Project $project);

    public function generateProjectIndex(Project $project);

    public function processFile(Index $index, $file, $rewrite = false, $createCache = true);

    public function createScopeForFile($file, Index $index, $createCache = true);

    public function processFileScope(Index $index, FileScope $scope);
}
