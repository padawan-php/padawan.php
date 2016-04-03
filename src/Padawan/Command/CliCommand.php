<?php

namespace Padawan\Command;

use Symfony\Component\Console\Command\Command;
use Padawan\Framework\Application\CLI;

abstract class CliCommand extends Command
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
     * @return \Padawan\Framework\Application\Cli
     */
    public function getApplication()
    {
        return parent::getApplication();
    }
}
