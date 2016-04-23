<?php

namespace Padawan\Framework\Application;

use DI\Container;
use Padawan\Framework\Application;
use Padawan\Command\GenerateCommand;
use Padawan\Command\PluginCommand;

class Cli extends Application
{
    public function __construct()
    {
        parent::__construct("Padawan CLI");
    }

    /**
     * @return Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    protected function loadCommands()
    {
        $this->add(new GenerateCommand);
        $this->add(new PluginCommand);
    }
}
