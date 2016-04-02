<?php

namespace Padawan\Framework\Application\CLI;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Padawan\Framework\Application\CLI;

abstract class Command extends SymfonyCommand
{
    public function getContainer()
    {
        return $this->getApplication()->getContainer();
    }

    public function get($name)
    {
        return $this->getContainer()->get($name);
    }

    /**
     * @return CLI
     */
    public function getApplication()
    {
        return parent::getApplication();
    }
}
