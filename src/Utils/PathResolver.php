<?php

namespace Utils;

use \Phine\Path\Path;

class PathResolver {
    private $path;

    public function __construct(Path $path){
        $this->path = $path;
    }
    public function canonical($path){
        return $this->path->canonical($path);
    }
    public function join($elements){
        return $this->path->join($elements);
    }
    public function read($filePath){
        if($this->exists($filePath)){
            return file_get_contents($filePath);
        }
        if($this->create($filePath))
            return "";
        else
            throw new \Exception("Unable to create file");
    }
    public function create($filePath, $isDir=false){
        $dirPath = $filePath;
        if(!$isDir){
            $dirPath = dirname($dirPath);
        }
        if(!empty($dirPath) && !$this->exists($dirPath) && !$this->isDir($dirPath)){
            mkdir($dirPath);
        }
        else if(!$this->isDir($dirPath)){
            throw new \Exception("Not a directory");
        }
        if(!$isDir){
            if(!$this->exists($filePath)){
                return touch($filePath);
            }
        }
    }
    public function getWorkingDirectory(){
        return $this->canonical(getcwd());
    }
    public function getAbsolutePath($path, $cwd=null){
        if(!$cwd)
            $cwd = $this->getWorkingDirectory();
        return $this->join([$cwd,$path]);
    }
    public function remove($path){
        $this->path->remove($path);
    }
    public function exists($filePath){
        return file_exists($filePath);
    }
    public function write($filePath, $content=""){
        if(!$this->exists($filePath)){
            $this->create($filePath);
        }
        if(!$this->isFile($filePath)){
            throw new \Exception("Unable to write to non-file");
        }
        return file_put_contents($filePath, $content);
    }
    public function isDir($path){
        return is_dir($path);
    }
    public function isFile($path){
        return is_file($path);
    }
}
