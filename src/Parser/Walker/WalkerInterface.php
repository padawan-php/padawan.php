<?php

namespace Parser\Walker;

use Domain\Core\Node\Uses;
use Domain\Core\Index;

interface WalkerInterface
{
    public function updateFileInfo(Uses $uses, $file);
    public function getResultScope();
    public function setIndex(Index $index);
}
