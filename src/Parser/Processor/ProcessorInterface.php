<?php

namespace Parser\Processor;

use Entity\FQCN;

interface ProcessorInterface {
    public function setFileInfo(FQCN $fqcn, $file);
    public function getResultNodes();
    public function clearResultNodes();
}
