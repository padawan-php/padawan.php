<?php

namespace Parser\Processor;

use Entity\Node\Uses;

interface ProcessorInterface {
    public function setFileInfo(Uses $fqcn, $file);
    public function getResultNodes();
    public function clearResultNodes();
}
