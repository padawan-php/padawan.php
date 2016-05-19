<?php

namespace Padawan\Framework\Domain\Project;


use Padawan\Domain\Project;
use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\ClassRepository as ClassRepositoryInterface;

/**
 * Class ClassRepository
 * @author
 */
class ClassRepository implements ClassRepositoryInterface
{
    public function findByName(Project $project, FQCN $name)
    {
        $index = $project->getIndex();
        $class = $index->findClassByFQCN($name);
        if (empty($class)) {
            $class = $index->findInterfaceByFQCN($name);
        }
        return $class;
    }

    public function findAllByNamePart(Project $project, $name = "")
    {
        if (empty($name)) {
            return $project->getIndex()->getClasses();
        }
        $classes = [];
        foreach ($project->getIndex()->getClasses() as $class) {
            if (strpos($class->fqcn->toString(), $name) !== false) {
                $classes[] = $class;
            }
        }
        return $classes;
    }
}
