<?php

namespace Padawan\Framework\IO;

use Padawan\Domain\Core\Index;
use \__PHP_Incomplete_Class;

class Reader extends BasicIO {
    public function read($rootDir) {
        try {
            $project = $this->prepare(
                $this->readFromFile($this->getIndexFileName($rootDir))
            );
            if ($project instanceof __PHP_Incomplete_Class) {
                return;
            }
            return $project;
        } catch (\Exception $e) {
            return;
        }
    }
    protected function prepare($rawProject) {
        $project = unserialize($rawProject);
        return $project;
    }
    protected function readFromFile($filename) {
        return $this->getPath()->read($filename);
    }
}
