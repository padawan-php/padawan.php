<?php

namespace Padawan\Framework\Domain\Project;

use Padawan\Domain\Project\File;
use Padawan\Domain\Project\FQCN;
use Padawan\Domain\Project\Index;
use Padawan\Domain\Project\Node\ClassData;
use Padawan\Domain\Project\Node\FunctionData;
use Padawan\Domain\Project\Node\InterfaceData;


class InMemoryIndex implements Index
{
    private $files              = [];
    private $fqcns              = [];
    private $classes            = [];
    private $interfaces         = [];
    private $extends            = [];
    private $implements         = [];
    private $functions          = [];

    /** @var Index $coreIndex */
    private static $coreIndex;

    public function getFQCNs()
    {
        return $this->fqcns;
    }

    public function getImplements() {
        return $this->implements;
    }
    public function getExtends() {
        return $this->extends;
    }

    public function addFile(File $file)
    {
        $scope = $file->scope();
        if (empty($scope)) {
            throw new \Exception("Scope in file is empty");
        }
        foreach ($scope->getClasses() as $classData) {
            $this->addFQCN($classData->fqcn);
            $this->addClass($classData);
        }
        foreach ($scope->getInterfaces() as $interfaceData) {
            $this->addFQCN($interfaceData->fqcn);
            $this->addInterface($interfaceData);
        }
        foreach ($scope->getFunctions() as $functionData) {
            $this->addFunction($functionData);
        }
        $this->files[$file->path()] = $file;
    }

    public function findFileByPath($path)
    {
        if (!array_key_exists($path, $this->files)) {
            return null;
        }
        return $this->files[$path];
    }

    /**
     * @return FQCN
     */
    public function findFQCNByFile($file)
    {
        if (!array_key_exists($file, $this->flippedClassMap)) {
            return null;
        }
        $fqcnStr = $this->flippedClassMap[$file];
        if (empty($fqcnStr)) {
            return null;
        }
        if (!array_key_exists($fqcnStr, $this->fqcns)) {
            return null;
        }
        return $this->fqcns[$fqcnStr];
    }

    /**
     * @return ClassData
     */
    public function findClassByFQCN(FQCN $fqcn) {
        $str = $fqcn->toString();
        if (array_key_exists($str, $this->classes)) {
            return $this->classes[$str];
        }
        if ($this->hasCoreIndex()) {
            return self::$coreIndex->findClassByFQCN($fqcn);
        }
    }

    /**
     * @return InterfaceData
     */
    public function findInterfaceByFQCN(FQCN $fqcn) {
        $str = $fqcn->toString();
        if (array_key_exists($str, $this->interfaces)) {
            return $this->interfaces[$str];
        }
        if ($this->hasCoreIndex()) {
            return self::$coreIndex->findInterfaceByFQCN($fqcn);
        }
    }

    /**
     * @return FunctionData
     */
    public function findFunctionByName($functionName)
    {
        if (array_key_exists($functionName, $this->functions)) {
            return $this->functions[$functionName];
        }
        if ($this->hasCoreIndex()) {
            return self::$coreIndex->findFunctionByName($functionName);
        }
    }

    /**
     * @return ClassData[]
     */
    public function findClassChildren(FQCN $class) {
        if (!array_key_exists($class->toString(), $this->extends)
            || !is_array($this->extends[$class->toString()])
        ) {
            $this->extends[$class->toString()] = [];
        }
        return $this->extends[$class->toString()];
    }

    /**
     * @return ClassData[]
     */
    public function findInterfaceChildrenClasses(FQCN $interface) {
        if (!array_key_exists($interface->toString(), $this->implements)
            || !is_array($this->implements[$interface->toString()])
        ) {
            $this->implements[$interface->toString()] = [];
        }
        return $this->implements[$interface->toString()];
    }

    /**
     * @return ClassData[]
     */
    public function getClasses()
    {
        $classes = $this->classes;
        if ($this->hasCoreIndex()) {
            $classes = array_merge($classes, self::$coreIndex->getClasses());
        }
        return $classes;
    }

    /**
     * @return InterfaceData[]
     */
    public function getInterfaces()
    {
        $interfaces = $this->interfaces;
        if ($this->hasCoreIndex()) {
            $interfaces = array_merge($interfaces, self::$coreIndex->getInterfaces());
        }
        return $interfaces;
    }

    /**
     * @return FunctionData[]
     */
    public function getFunctions()
    {
        $functions = $this->functions;
        if ($this->hasCoreIndex()) {
            $functions = array_merge($functions, self::$coreIndex->getFunctions());
        }
        return $functions;
    }

    public function addClass(ClassData $class) {
        $this->classes[$class->fqcn->toString()] = $class;
        if ($class->getParent() instanceof FQCN) {
            $this->addExtend($class, $class->getParent());
        }
        foreach ($class->getInterfaces() as $interface) {
            if ($interface instanceof FQCN) {
                $this->addImplement($class, $interface);
            }
        }
        foreach ($this->findClassChildren($class->fqcn) AS $child) {
            $child->setParent($class);
        }
    }

    public function addInterface(InterfaceData $interface) {
        $this->interfaces[$interface->fqcn->toString()] = $interface;
        foreach ($this->findInterfaceChildrenClasses($interface->fqcn) as $child) {
            $this->addImplement($child, $interface->fqcn);
        }
        foreach ($interface->getInterfaces() as $parent) {
            if ($parent instanceof FQCN) {
                $this->addImplement($interface, $parent);
            }
        }
    }

    public function addFunction(FunctionData $function)
    {
        $this->functions[$function->name] = $function;
    }

    public function addFQCN(FQCN $fqcn) {
        $this->fqcns[$fqcn->toString()] = $fqcn;
    }

    protected function addExtend(ClassData $class, FQCN $parent) {
        $this->findClassChildren($parent);
        $this->extends[$parent->toString()][$class->fqcn->toString()] = $class;
        $parentClass = $this->findClassByFQCN($parent);
        if ($parentClass instanceof ClassData) {
            $class->setParent($parentClass);
        }
    }

    protected function addImplement($class, FQCN $fqcn) {
        $this->findInterfaceChildrenClasses($fqcn);
        $this->implements[$fqcn->toString()][$class->fqcn->toString()] = $class;
        $interface = $this->findInterfaceByFQCN($fqcn);
        if ($interface instanceof InterfaceData) {
            $class->addInterface($interface);
        }
    }

    private function hasCoreIndex()
    {
        return $this !== self::$coreIndex && !empty(self::$coreIndex);
    }
}
