<?php

namespace Padawan\Framework;

use DI\Container;
use DI\ContainerBuilder;
use React\EventLoop\Factory;
use Symfony\Component\Console\Application as BaseApplication;

define("PADAWAN_VERSION", "0.3");
define("STUBS_DIR", dirname(dirname(dirname(__DIR__))) . '/stubs');

/**
 * Class Application
 */
abstract class Application extends BaseApplication
{
    public static $eventLoop;
    public function __construct($name = "Padawan", $loop = null)
    {
        parent::__construct($name, PADAWAN_VERSION);
        $this->createContainer();
        $this->setAutoExit(false);
        $this->loadCommands();
        if ($loop) {
            self::$eventLoop = $loop;
        } else {
            self::$eventLoop = Factory::create();
        }
    }

    public function getContainer()
    {
        return $this->container;
    }

    abstract protected function loadCommands();

    private function createContainer()
    {
        $builder = new ContainerBuilder;
        $builder->setDefinitionCache(new \Doctrine\Common\Cache\ArrayCache);
        $builder->addDefinitions(__DIR__ . '/DI/config.php');
        $this->container = $builder->build();
    }

    /** @var Container */
    protected $container;
}
