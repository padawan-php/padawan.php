<?php

namespace Padawan\Parser\Walker;

use Padawan\Domain\Project\Node\Uses;
use Padawan\Domain\Project\Index;

interface WalkerInterface
{
    public function updateFileInfo(Uses $uses, $file);
    public function getResultScope();
    public function setIndex(Index $index);
}
