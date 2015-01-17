<?php

namespace DI;

class Container {
    private $map = [];

    public function __construct(){
        $this->map = [
            'Index' => new \DTO\Index(),
            'PathUtils' => new \Phine\Path\Path(),
            'ClassValidator' => new \Validator\ClassValidator(),
            'ClassParser' => new \Parser\ClassParser()
        ];
        $this->map['Path'] = new \Utils\PathResolver($this->get("PathUtils"));
        $this->map['Composer'] = new \Utils\Composer($this->get("Path"));
        $this->map['ClassUtils'] = new \Utils\ClassUtils(
            $this->get("Path"),
            $this->get("ClassParser"),
            $this->get("ClassValidator")
        );
        $this->map["IndexGenerator"] = new \IndexGenerator(
            $this->get("Path"),
            $this->get("Composer"),
            $this->get("ClassUtils")
        );
        $this->map["IndexWriter"] = new \Utils\IndexWriter(
            $this->get("IndexGenerator")
        );
    }
    public function get($service){
        if(array_key_exists($service, $this->map)){
            return $this->map[$service];
        }
        else {
            throw new \Exception("Unknown service \"{$service}\"");
        }
    }
    public function set($serviceName, $service, $overwrite = false){
        if(!array_key_exists($serviceName, $this->map) || $overwrite){
            $this->map[$serviceName] = $service;
        }
        else {
            throw new \Exception("Service \"{$serviceName}\" already exists");
        }
        return $this;
    }
}
