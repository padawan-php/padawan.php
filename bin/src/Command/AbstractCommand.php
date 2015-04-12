<?php

namespace Command;

use DI\Container;

abstract class AbstractCommand implements CommandInterface{
    protected $container;
    public function __construct(){
        $this->container = new Container();
        $a = new Container;
    }
    public function get($serviceName){
        return $this->container->get($serviceName);
    }
    protected function isVerbose($arguments){
        $verbose = false;
        if(count($arguments) > 0 && $arguments[0] == '-verbose') {
            array_shift($arguments);
            $verbose = true;
        }
        return $verbose;
    }
    protected function addPlugins($arguments){
        $generator = $this->get("IndexGenerator");
        $plugins = explode("-u", implode("", $arguments));
        foreach ($plugins as $pluginFile) {
            if(empty($pluginFile)) {
                continue;
            }
            $generator->addPlugin(trim($pluginFile));
        }
    }
}
