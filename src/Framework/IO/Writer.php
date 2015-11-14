<?php

namespace Framework\IO;

use Domain\Core\Project;

class Writer extends BasicIO
{
    public function write(Project $project)
    {
        $this->writeToFile(
            $this->getIndexFileName($project->getRootDir()),
            $this->prepare($project)
        );
    }
    protected function prepare(Project $project)
    {
        $index = $project->getIndex();
        $r = new \ReflectionObject($index);
        $r->setStaticPropertyValue("coreIndex", null);
        $str = serialize($project);
        return $str;
    }
    protected function writeToFile($fileName, $data)
    {
        $this->getPath()->write($fileName, $data);
    }
}
