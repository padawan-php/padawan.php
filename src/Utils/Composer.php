<?php

namespace Utils;

class Composer {
    private $vendorPath;
    private $loader;
    private $path;

    public function __construct(PathResolver $path, $vendorPath='vendor'){
        $this->vendorPath = $vendorPath;
        $this->path = $path;
    }
    public function getComposerPath(){
        return $this->path->join([$this->vendorPath, 'composer']);
    }
    public function getCanonicalClassMap($cwd){
        return $this->canonicalizeClassMap($cwd, $this->getClassMap());
    }
    public function getClassMap(){
        return require $this->path
            ->join([$this->getComposerPath(), 'autoload_classmap.php']);
    }
    public function canonicalizeClassMap($cwd, $classMap){
        foreach($classMap as $key => $item){
            $item = $this->path->canonical($item);
            $classMap[$key] = str_replace($cwd, '', $item);
        }
        return $classMap;
    }
    public function getVendorLibs($rootDir)
    {
        $vendorLibs = array();
        $autoloadNamespaces = require $this->path->join([
            $rootDir,
            $this->getComposerPath(),
            'autoload_namespaces.php'
        ]);
        foreach ($autoloadNamespaces as $namespace => $directory) {
            if($namespace == "") {
                continue;
            }
            $vendorLibs[$namespace] = $this->path->canonical($directory[0]);
        }
        return $vendorLibs;
    }
}
