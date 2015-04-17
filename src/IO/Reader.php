<?php

namespace IO;

class Reader extends BasicIO {
    public function read($rootDir){
        return $this->prepareIndex(
            $this->readFromFile($this->getIndexFileName($rootDir))
        );
    }
    protected function prepareIndex($indexStr){
        return unserialize($indexStr);
    }
    protected function readFromFile($filename){
        return $this->getPath()->read($filename);
    }
}
