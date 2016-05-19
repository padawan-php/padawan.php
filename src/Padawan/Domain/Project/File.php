<?php

namespace Padawan\Domain\Project;


use Padawan\Domain\Scope\FileScope;

/**
 * Class File
 */
class File
{
    /** @var FileScope */
    private $_scope;
    private $_path;
    private $hash;
    private $name;

    public function __construct($path)
    {
        $this->_path = $path;
    }

    public function updateScope(FileScope $scope, $hash)
    {
        $this->_scope = $scope;
        $this->hash = $hash;
    }

    /**
     * @return FileScope
     */
    public function scope()
    {
        return $this->_scope;
    }

    public function path()
    {
        return $this->_path;
    }

    public function isChanged($hash)
    {
        return $this->hash !== $hash;
    }
}
