<?php

namespace Command;

class UpdateCommand extends AbstractCommand{
    public function run(array $arguments = []){
        array_shift($arguments);
        array_shift($arguments);
        $file = array_shift($arguments);
        $cacheFileName = array_shift($arguments);
        $verbose = false;

        $writer = $this->get("IndexWriter");
        $this->addPlugins($arguments);

        $writer->writeUpdatedClassInfo($file, $cacheFileName);
    }
}
