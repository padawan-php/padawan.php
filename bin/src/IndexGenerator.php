<?php
/**
 *=============================================================================
 * AUTHOR:  Mun Mun Das <m2mdas at gmail.com>
 * FILE: IndexGenerator.php
 * Last Modified: October 04, 2013
 * License: MIT license  {{{
 *     Permission is hereby granted, free of charge, to any person obtaining
 *     a copy of this software and associated documentation files (the
 *     "Software"), to deal in the Software without restriction, including
 *     without limitation the rights to use, copy, modify, merge, publish,
 *     distribute, sublicense, and/or sell copies of the Software, and to
 *     permit persons to whom the Software is furnished to do so, subject to
 *     the following conditions:
 *
 *     The above copyright notice and this permission notice shall be included
 *     in all copies or substantial portions of the Software.
 *
 *     THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 *     OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 *     MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 *     IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 *     CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 *     TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 *     SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * }}}
 *=============================================================================
 */

class IndexGenerator
{
    /**
     *
     * @var array
     */
    private $file_fqcn;

    /**
     * list of parsed classes
     *
     * @var array
     */
    private $parsedClasses;

    /**
     *
     *
     * @var array
     */
    private $fqcn_file;

    /**
     * @var array
     */
    private $classes;

    /**
     * @var array
     */
    private $class_fqcn;

    /**
     * List of valid files
     * @var array
     */
    private $validFiles;

    /**
     *
     *
     * @var array
     */
    private $invalidClasses;

    /**
     * Array of processed classes
     *
     * @var string
     */
    private $processedClasses;

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
    public $path;

    /**
     * Object with Composer-specific functions
     *
     * @var Utils\ComposerUtils
     */
    public $composer;

    /**
     * Object for work with class-information
     *
     * @var Utils\ClassUtils
     */
    public $classUtils;

    public function __construct(Utils\PathResolver $path, Utils\Composer $composer, Utils\ClassUtils $class, $verbose = false)
    {
        $this->path             = $path;
        $this->composer         = $composer;
        $this->classUtils       = $class;
        $this->file_fqcn        = array();
        $this->fqcn_file        = array();
        $this->class_fqcn       = array();
        $this->classes          = array();
        $this->validFiles       = array();
        $this->invalidClasses   = array();
        $this->processedClasses = array();
        $this->parsedClasses    = array();
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

    public function generateIndex()
    {
        // @TODO coreIndex should be set before generation started
        $time = microtime(true); // Gets microseconds
        $classMap = $this->getComposerUtils()->getClassMap();
        $cwd = $this->path->canonical(getcwd()) . "/";
        $classMap = $this->getComposerUtils()->canonicalClassMap($cwd, $classMap);
        $out = array();
        $out['namespaces'] = array();
        $out['interface'] = array();
        $out['fqcn_file'] = $classMap;
        $out['file_fqcn'] = array_flip($classMap);
        $out['extends']  = array();
        $out['implements'] = array();
        $out['vendor_libs'] = $this->getComposerUtils()->listVendorLibraries();
        //$this->file_fqcn = $classMap;
        $this->file_fqcn = $out['file_fqcn'];
        $this->fqcn_file = $out['fqcn_file'];

        $this->execHook("init", false, $this->getComposerUtils()->getLoader());

        $regex = '/(.*)(?=\\\\(\w+)$)|(.*)/';
        $count = 0;
        foreach($classMap as $fqcn => $file) {
            if(
                preg_match('/DateSelect/', $file) //zend
                //|| preg_match('/DateTime/', $file) //zend
                || preg_match('/DateTimeSelect/', $file) //zend
                || preg_match('/MonthSelect/', $file) //zend
                //|| preg_match('/PropelDataCollector/', $file) //zend
            ){
                continue;
            }

            if(
                !array_key_exists('PHPUnit_Framework_TestCase', $this->fqcn_file) &&
                (preg_match('/Test/',$file)
                || preg_match('/TestCase/'         , $file)
                || preg_match('/0/'                , $fqcn)
                || preg_match('/Fixtures/'         , $file)
                || preg_match('/Test/'             , $file)
                //|| preg_match('/Command/'             , $file)
                || preg_match('/DataFixtures/'     , $file)
                )
            ){
                continue;
            }

            if($this->verbose) {
                echo "processing $file\n";
            }
            echo $fqcn . "\n";

            if(!$this->getClassUtils()->validate($file)) {
                echo "Invalid\n";
                $this->invalidClasses[] = $file;
                continue;
            }
            $classData = array();
            $ret = preg_match($regex, $fqcn, $matches);
            if(!$ret) {
                continue;
            }
            $className = count($matches) == 3? $matches[2] :$matches[3];
            $namespace = count($matches) == 3? $matches[1] : "";
            if(!empty($namespace)) {
                $out['namespaces'][] = $namespace;
            }

            $classData = $this->processClass($fqcn);
            $out['classes'][$fqcn] = $classData;

            if(!empty($classData['parentclasses'])) {
                $parentClasses = $classData['parentclasses'];
                foreach ($parentClasses as $parentClass) {
                    if(empty($parentClass)) {
                        continue;
                    }
                    $out['extends'][$parentClass][] = $fqcn;
                }
            }

            if(!empty($classData['interfaces'])) {
                $interfaces = $classData['interfaces'];
                foreach ($interfaces as $interface) {
                    if(empty($interface)) {
                        continue;
                    }
                    $out['implements'][$interface][] = $fqcn;
                }
            }
            $this->execHook("postProcess", false, $fqcn, $file, $classData);

            $count++;
        }
        foreach ($this->coreIndex['class_list'] as $coreClass) {
            $this->classes[] = $coreClass;
            $this->class_fqcn[$coreClass] = $coreClass;
        }
        $classFuncConstList =  array_merge($this->classes, $this->coreIndex['function_list']);
        sort($classFuncConstList);
        sort($this->classes);
        ksort($this->class_fqcn);

        $out['class_list']            = $this->classes;
        $out['class_fqcn']            = $this->class_fqcn;
        $out['class_func_const_list'] = $classFuncConstList;
        $fqcns = array_merge($this->classes, array_keys($this->fqcn_file));

        sort($fqcns);
        $classFuncMenuEntries = $this->createMenuEntries($this->class_fqcn, $this->coreIndex['function_list']);
        $out['class_func_menu_entries'] = $classFuncMenuEntries;
        $this->execHook("postCreateIndex", false, $out, $this);
        $this->writePluginIndexes();
        return $out;
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
    /**
     * Gets the value of invalidClasses
     *
     * @return array
     */
    public function getInvalidClasses()
    {
        return $this->invalidClasses;
    }

    /**
     * Sets the value of invalidClasses
     *
     * @param string $invalidClasses invalid class list
     *
     * @return
     */
    public function setInvalidClasses($invalidClasses)
    {
        $this->invalidClasses = $invalidClasses;
        return $this;
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

