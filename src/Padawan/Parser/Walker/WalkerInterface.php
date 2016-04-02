<?php

namespace Padawan\Parser\Walker;

use Padawan\Domain\Core\Node\Uses;
use Padawan\Domain\Core\Index;

interface WalkerInterface
{
    public function updateFileInfo(Uses $uses, $file);
    public function getResultScope();
    public function setIndex(Index $index);
}
