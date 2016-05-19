<?php

namespace Padawan\Domain\Project;

use Padawan\Domain\Project\Node\ClassData;
use Padawan\Domain\Project\Node\FunctionData;
use Padawan\Domain\Project\Node\InterfaceData;
use Padawan\Domain\Project\File;

interface Index
{
    public function addFile(File $file);

    /**
     * @return FQCN[]
     */
    public function getFQCNs();

    public function findFileByPath($path);

    /**
     * @return FQCN
     */
    public function findFQCNByFile($file);

    /**
     * @return ClassData
     */
    public function findClassByFQCN(FQCN $fqcn);

    /**
     * @return InterfaceData
     */
    public function findInterfaceByFQCN(FQCN $fqcn);

    /**
     * @return FunctionData
     */
    public function findFunctionByName($functionName);

    /**
     * @return ClassData[]
     */
    public function findClassChildren(FQCN $class);

    /**
     * @return ClassData[]
     */
    public function findInterfaceChildrenClasses(FQCN $interface);

    /**
     * @return ClassData[]
     */
    public function getClasses();

    /**
     * @return InterfaceData[]
     */
    public function getInterfaces();

    /**
     * @return FunctionData[]
     */
    public function getFunctions();

    public function getImplements();

    public function getExtends();
}
