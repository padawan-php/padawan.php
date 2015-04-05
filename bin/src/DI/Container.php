<?php

namespace DI;

class Container {
    private $map = [];

    public function __construct(){
        $this->loadBasicServices();
        $this->loadParser();
        $this->map['Composer'] = new \Utils\Composer($this->get("Path"));
        $this->map["ClassUtils"] = new \Utils\ClassUtils(
            $this->get("Path"),
            $this->get("Parser")
        );
        $this->map["IndexGenerator"] = new \IndexGenerator(
            $this->get("Path"),
            $this->get("Composer"),
            $this->get("ClassUtils")
        );
        $this->map["IndexWriter"] = new \Utils\IndexWriter(
            $this->get("Path")
        );
    }
    public function get($service){
        if(array_key_exists($service, $this->map)){
            return $this->map[$service];
        }
        else {
            throw new ServiceNotFoundException("Unknown service \"{$service}\"");
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
            'Index' => new \Entity\Index(),
            'PathUtils' => new \Phine\Path\Path(),
            'PhpParser' => new \PhpParser\Parser(new \PhpParser\Lexer),
            'Traverser' => new \PhpParser\NodeTraverser(),
            'Visitor' => new \Parser\Visitor\Visitor()
        ];
        $this->map['Path'] = new \Utils\PathResolver($this->get("PathUtils"));
    }
    private function loadParser(){
        $this->set("UseParser", new \Parser\UseParser);
        $this->set("MethodParser", new \Parser\MethodParser(
            $this->get("UseParser")
        ));
        $this->set("PropertyParser", new \Parser\PropertyParser(
            $this->get("UseParser")
        ));
        $this->set("CommentParser", new \Parser\CommentParser(
            $this->get("UseParser")
        ));
        $this->map["ClassParser"] = new \Parser\ClassParser(
            $this->get("CommentParser"),
            $this->get("MethodParser"),
            $this->get("PropertyParser"),
            $this->get("UseParser")
        );
        $this->map["InterfaceParser"] = new \Parser\InterfaceParser(
            $this->get("CommentParser"),
            $this->get("MethodParser"),
            $this->get("PropertyParser")
        );

        $this->map['Parser'] = new \Parser\Parser(
            $this->get("PhpParser"),
            $this->get("ClassParser"),
            $this->get("InterfaceParser"),
            $this->get("UseParser"),
            $this->get("Path")
        );
        $this->map['Parser']->setTraverser($this->get('Traverser'));
        $this->map['Parser']->setVisitor($this->get('Visitor'));
    }
}
