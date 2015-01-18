<?php

namespace DI;

class Container {
    private $map = [];

    public function __construct(){
        $this->loadBasicServices();
        $this->loadParser();
        $this->map['Composer'] = new \Utils\Composer($this->get("Path"));
        $this->map['ClassUtils'] = new \Utils\ClassUtils(
            $this->get("Path"),
            $this->get("ClassParser")
        );
        $this->map["IndexGenerator"] = new \IndexGenerator(
            $this->get("Path"),
            $this->get("Composer"),
            $this->get("ClassUtils")
        );
        $this->map["IndexWriter"] = new \Utils\IndexWriter(
            $this->get("IndexGenerator"),
            $this->get("Path")
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
    private function loadBasicServices(){
        $this->map = [
            'Index' => new \DTO\Index(),
            'PathUtils' => new \Phine\Path\Path(),
            'PhpParser' => new \PhpParser\Parser(new \PhpParser\Lexer),
            'Traverser' => new \PhpParser\NodeTraverser(),
            'Visitor' => new \Parser\Visitor\Visitor()
        ];
        $this->map['Path'] = new \Utils\PathResolver($this->get("PathUtils"));
    }
    private function loadParser(){
        $this->map['ClassParser'] = new \Parser\ClassParser(
            $this->get("PhpParser"),
            $this->get("Path")
        );
        $this->map['ClassParser']->setTraverser($this->get('Traverser'));
        $this->map['ClassParser']->setVisitor($this->get('Visitor'));
    }
}
