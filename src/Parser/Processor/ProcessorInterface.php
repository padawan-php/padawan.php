<?php

namespace Parser\Processor;

use Domain\Core\Node\Uses;

interface ProcessorInterface {
    public function setFileInfo(Uses $fqcn, $file);
    public function getResultScope();
}
