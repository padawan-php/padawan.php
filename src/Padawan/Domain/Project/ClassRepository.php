<?php

namespace Padawan\Domain\Project;


use Padawan\Domain\Project;

/**
 * Interface ClassRepository
 */
interface ClassRepository
{
    public function findByName(Project $project, FQCN $name);
    public function findAllByNamePart(Project $project, $name);
}
