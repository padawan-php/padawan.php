<?php

namespace IO;

class Writer extends BasicIO{
    public function write($project){
        $this->writeToFile(
            $this->getIndexFileName($project->getRootDir()),
            $this->prepareIndex($project)
        );
    }
    public function writeReport($invalidClasses){
        $this->writeToFile(
            $this->getReportFileName(),
            implode("\n", $invalidClasses)
        );
    }
    protected function prepareIndex($index){
        $str = serialize($index);
        return $str;
    }
    protected function writeToFile($fileName, $data)
    {
        $this->getPath()->write($fileName, $data);
    }
}
