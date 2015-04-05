<?php

use Entity\ClassData;
use Entity\InterfaceData;

class IndexGenerator
{
    /**
     * Array of plugin classes
     * @var array
     */
    public $plugins;

    /**
     * Verbosity
     *
     * @var bool
     */
    private $verbose;

    /**
     *
     *
     * @var PathResolver
     */
    protected $path;

    /**
     * Object with Composer-specific functions
     *
     * @var Utils\ComposerUtils
     */
    protected $composer;

    /**
     * Object for work with class-information
     *
     * @var Utils\ClassUtils
     */
    protected $class;

    public function __construct(Utils\PathResolver $path, Utils\Composer $composer, Utils\ClassUtils $class, $verbose = false)
    {
        $this->path             = $path;
        $this->composer         = $composer;
        $this->classUtils       = $class;
        $this->plugins          = array();
        $this->verbose          = $verbose;
    }

    public function getComposerUtils(){
        return $this->composer;
    }

    public function getClassUtils(){
        return $this->classUtils;
    }

    public function getNamespaceUtils(){
        return $this->namespaceUtils;
    }

    public function generateIndex(\Entity\Index $index)
    {
        // @TODO coreIndex should be set before generation started
        $this->populateClassMapIndex($index);
        $index->setVendorLibs($this->getComposerUtils()->getVendorLibs());
        //$this->execHook("init", false, $this->getComposerUtils()->getLoader());

        $this->generateProjectIndex($index);

        /* @TODO I have no idea about what is happening below. Should look through it */
        //@TODO add classMap populating
//        foreach ($coreIndex['class_list'] as $coreClass) {
//        }
//        $classFuncConstList = array_merge($coreIndex['class_list'], $coreIndex['function_list']);
//        sort($classFuncConstList);
//        sort($coreIndex['class_list']);
//        //ksort($this->class_fqcn);
//
//        // @TODO should be $index here
//        $out['class_list']            = $this->classes;
//        $out['class_fqcn']            = $this->class_fqcn;
//        $out['class_func_const_list'] = $classFuncConstList;
//        $fqcns = array_merge($coreIndex['class_list'], array_keys($this->fqcn_file));
//
//        sort($fqcns);
//        $classFuncMenuEntries = $this->createMenuEntries($this->class_fqcn, $this->coreIndex['function_list']);
//        $out['class_func_menu_entries'] = $classFuncMenuEntries;
//        $this->execHook("postCreateIndex", false, $out, $this);
        return $index;
    }

    public function generateProjectIndex(\Entity\Index $index){
        $classMap = $index->getClassMap();
        foreach($classMap as $fqcn => $file) {
            $this->processFile($index, $fqcn, $file);
        }
    }

    public function processFile(\Entity\Index $index, $fqcn, $file){
        if($this->verbose) {
            echo "processing $file\n";
        }

        $fqcn = $this->getClassUtils()->getParser()->parseFQCN($fqcn);
        if(!empty($fqcn->getNamespace())) {
            $index->addNamespace($fqcn->getNamespace());
        }
        $index->addClassFQCN($fqcn);

        $nodes = $this->getClassUtils()->getParser()->parseFile($fqcn, $file);
        if(!is_array($nodes)){
            $nodes = [$nodes];
        }
        foreach($nodes as $node){
            if($node instanceof ClassData){
                $index->addClass($node, $fqcn->toString());

                $this->populateExtendsIndex($index, $node->fqcn, $node->parentClasses);
                $this->populateImplementsIndex($index, $node->fqcn, $node->interfaces);

                $this->execHook("postProcess", false, $fqcn->toString(), $file, $node->toArray());
            }
            elseif($node instanceof InterfaceData){
                $index->addClass($node, $fqcn->toString());
                $index->addInterface($node, $fqcn->toString());
                $this->populateImplementsIndex($index, $node->fqcn, $node->interfaces);
            }

        }

    }

    public function addPlugin($pluginFile)
    {
        if(!is_file($pluginFile)) {
            echo "not a valid file \n";
            return;
        }
        include $pluginFile;
        $className = basename($pluginFile, ".php");
        $plugin = new $className;
        if($plugin->isValidForProject()){
            $this->plugins[] = $plugin;
        }
    }

    /**
     * calls plugin hooks
     * @param string $hookName name of the plugin hook
     * @param bool $return expect return date
     */
    public function execHook($hookName, $return)
    {
        $args = func_get_args();
        $out = array();
        array_shift($args);
        $return = array_shift($args);
        $extraArgs = $args;
        foreach ($this->plugins as $plugin) {
            if(method_exists($plugin, $hookName)) {
                $pluginArgs = $extraArgs;

                if($hookName == "preUpdateIndex") {
                    $pluginIndex    = $extraArgs[0];
                    $indexForPlugin = $pluginIndex[strtolower(get_class($plugin))];
                    $pluginArgs     = array($indexForPlugin);
                }

                $ret = call_user_func_array(array($plugin, $hookName), $pluginArgs);
                if($return) {
                    $out[strtolower(get_class($plugin))] = $ret;
                }
            }
        }
        if($return) {
            return $out;
        }
    }

    protected function populateClassMapIndex(\Entity\Index $index){
        $cwd = $this->path->canonical(getcwd() . "/");
        $classMap = $this->getComposerUtils()->getCanonicalClassMap($cwd);
        $index->setClassMap($classMap);
        return $index;
    }
    protected function populateExtendsIndex(\Entity\Index $index, \Entity\FQCN $fqcn, array $parentClasses = []){
        if(empty($parentClasses))
            return;
        foreach ($parentClasses as $parentClass) {
            if(empty($parentClass)) {
                continue;
            }
            $index->addExtend($fqcn->toString(), $parentClass);
        }
    }

    protected function populateImplementsIndex(\Entity\Index $index, \Entity\FQCN $fqcn, array $interfaces = []){
        if(empty($interfaces))
            return;
        foreach ($interfaces as $interface) {
            if(empty($interface)) {
                return;
            }
            $index->addImplement($fqcn->toString(), $interface);
        }
    }

    private function createMenuEntries($class_fqcn, $functions)
    {
        $dict = array();
        asort($functions);
        foreach ($functions as $func) {
            if($func == "Constants for PDO_4D" || $func == "Examples with PDO_4D") {
                continue;
            }
            $signature = $this->coreIndex['functions'][$func]['signature'];
            $dict[] = array(
                'word' => $func,
                'kind' => 'f',
                'menu' => $signature,
                'info' => $signature,
            );
        }
        foreach ($class_fqcn as $keyword => $fqcns) {
            if(is_array($fqcns)) {
                $i = 0;
                foreach ($fqcns as $fqcn) {
                    $dict[] = array(
                        'word' => $keyword . "-" . ($i + 1),
                        'kind' => 'c',
                        'menu' => $fqcn,
                        'info' => $fqcn,
                    );
                    $i++;
                }
            } elseif(is_string($fqcns)) {
                $dict[] = array(
                    'word' => $keyword,
                    'kind' => 'c',
                    'menu' => $fqcns,
                    'info' => $fqcns,
                );
            }
        }
        return $dict;
    }


    private function getUpdatedExtraData($fqcn, &$prevData, &$classData, &$classCache, $extraDataList, $extraClassDataKey, $extraClassCacheKey)
    {
        $extraDataDiff = array(
            'added' => array(),
            'removed' => array()
        );
        $parentCountValues = array_count_values(
            array_merge($prevData[$extraClassDataKey], $classData[$extraClassDataKey])
        );

        foreach($parentCountValues as $value => $count) {
            if($count == 1) {
                if(in_array($value, $prevData[$extraClassDataKey])) { //removed
                    $extraDataDiff['removed'][] = $value;
                } else {
                    $extraDataDiff['added'][] = $value;
                }
            }
        }

        foreach($extraDataDiff['removed'] as $removed) {
            $removedExtendData = $extraDataList[$removed];
            array_splice($removedExtendData, array_search($fqcn, $removedExtendData), 1);
            $classCache[$extraClassCacheKey][$removed] = $removedExtendData;
        }

        foreach($extraDataDiff['added'] as $added) {
            $classCache[$extraClassCacheKey][$added][] = $fqcn;
        }
        return $extraDataDiff;
    }
}

