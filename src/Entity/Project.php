<?php

namespace Entity;

class Project{
    private $index;
    private $rootFolder;

    public function __construct(Index $index, $rootFolder = ""){
        $this->index        = $index;
        $this->rootFolder   = $rootFolder;
    }
    public function getRootFolder(){
        return $this->rootFolder;
    }
    public function getRootDir(){
        return $this->getRootFolder();
    }

    /**
     * Returns project's index
     * @return Index
     */
    public function getIndex(){
        return $this->index;
    }
    public function setIndex(Index $index){
        $this->index = $index;
    }
}
