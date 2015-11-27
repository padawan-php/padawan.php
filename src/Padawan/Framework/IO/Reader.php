<?php

namespace Padawan\Framework\IO;

use Padawan\Domain\Core\Index;

class Reader extends BasicIO {
    public function read($rootDir) {
        return $this->prepare(
            $this->readFromFile($this->getIndexFileName($rootDir))
        );
    }
    protected function prepare($rawProject) {
        $project = unserialize($rawProject);
        return $project;
    }
    protected function readFromFile($filename) {
        return $this->getPath()->read($filename);
    }
}
