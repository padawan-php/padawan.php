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

set_time_limit(0);
ini_set('memory_limit','1000M');
ini_set('display_errors', 'stderr');
if(php_sapi_name() == 'cli') {
    if(count($argv) < 2) {
        echo "error: not enough arguments";
    } elseif ($argv[1] == 'generate') {
        array_shift($argv);
        array_shift($argv);

        $verbose = false;
        if(count($argv) > 0 && $argv[0] == '-verbose') {
            array_shift($argv);
            $verbose = true;
        }

        //$time = microtime(true); 
        $phpCompletePsr  = new IndexGenerator($verbose);

        $plugins = explode("-u", implode("", $argv));
        foreach ($plugins as $pluginFile) {
            if(empty($pluginFile)) {
                continue;
            }
            $phpCompletePsr->addPlugin(trim($pluginFile));
        }

        $index  = $phpCompletePsr->generateIndex();

        $jsonIndex = json_encode($index);
        $lastJsonError = json_last_error();
        if($lastJsonError != JSON_ERROR_NONE) {
            printJsonError($lastJsonError);
            exit;
        }

        $phpCompletePsr->writeToFile($phpCompletePsr->getIndexFileName(), $jsonIndex);
        $phpCompletePsr->writeToFile($phpCompletePsr->getReportFileName(), implode("\n", $phpCompletePsr->getInvalidClasses()));
    } else if($argv[1] == 'update') {
        array_shift($argv);
        array_shift($argv);
        $file = array_shift($argv);
        $cacheFileName = array_shift($argv);
        $verbose = false;

        $p = new IndexGenerator($verbose);
        $plugins = explode("-u", implode("", $argv));
        foreach ($plugins as $pluginFile) {
            if(empty($pluginFile)) {
                continue;
            }
            $p->addPlugin($pluginFile);
        }

        $p->writeUpdatedClassInfo($file, $cacheFileName);
        //echo "Time Elapsed: ".(microtime(true) - $time)."s\n";
        //echo "highest memory ".  memory_get_peak_usage();
    } else {
        echo "not a valid argument";
        exit;
    }
} else {
    exit;
}

function printJsonError($errorCode)
{
    switch (json_last_error()) {
    case JSON_ERROR_NONE:
        echo ' - No errors';
        break;
    case JSON_ERROR_DEPTH:
        echo ' - Maximum stack depth exceeded';
        break;
    case JSON_ERROR_STATE_MISMATCH:
        echo ' - Underflow or the modes mismatch';
        break;
    case JSON_ERROR_CTRL_CHAR:
        echo ' - Unexpected control character found';
        break;
    case JSON_ERROR_SYNTAX:
        echo ' - Syntax error, malformed JSON';
        break;
    case JSON_ERROR_UTF8:
        echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
    default:
        echo ' - Unknown error';
        break;
    }
    echo "\n";
}

class IndexGenerator
{
    /**
     *
     * @var array
     */
    private $file_fqcn;

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
     * index file name
     *
     * @var string
     */
    private $indexFileName;

    /**
     * report filename
     *
     * @var string
     */
    private $reportFileName;

    /**
     * list of parsed classes
     *
     * @var array 
     */
    private $parsedClasses;

    /**
     * php core doc index
     * @var array
     */
    private $coreIndex;

    /**
     * php core doc index file
     * @var string
     */
    private $coreIndexFile;
    /**
     * 
     *
     * @var string
     */
    private $pluginIndexFile;

    private $loader;

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

    public function __construct($verbose) 
    {
        $this->file_fqcn        = array();
        $this->fqcn_file        = array();
        $this->class_fqcn       = array();
        $this->classes          = array();
        $this->validFiles       = array();
        $this->invalidClasses   = array();
        $this->processedClasses = array();
        $this->indexFileName    = './.phpcomplete_extended/phpcomplete_index';
        $this->reportFileName   = './.phpcomplete_extended/report.txt';
        $this->coreIndexFile    = './.phpcomplete_extended/core_index';
        $this->pluginIndexFile  = './.phpcomplete_extended/plugin_index';
        $this->parsedClasses    = array();
        $this->plugins = array();
        $this->loader = require 'vendor/autoload.php';
        $this->verbose = $verbose;
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

    public function writeUpdatedClassInfo($fileName, $cacheFileName) 
    {
        $time = microtime(true);
        $this->processCoreIndexFile();
        $fileName        = $this->normalizePath($fileName);
        $classCache      = json_decode(file_get_contents($this->indexFileName), true);
        $extends         = $classCache['extends'];
        $implements      = $classCache['implements'];
        $this->fqcn_file = $classCache['fqcn_file'];
        $this->file_fqcn = $classCache['file_fqcn'];
        $this->class_fqcn = $classCache['class_fqcn'];
        $fileData        = array();
        if(!is_file($this->pluginIndexFile)) {
            $pluginIndex = array();
        } else{
            $pluginIndex     = json_decode(file_get_contents($this->pluginIndexFile), true);
        }
        
        $this->execHook("init", false, $this->loader);
        $this->execHook("preUpdateIndex", false, $pluginIndex);

        $fqcn = $this->validateClass($fileName);
        if(empty($fqcn)) {
            return;
        }

        if(array_key_exists($fileName, $classCache['file_fqcn'])) {
            $prevData = $classCache['classes'][$fqcn];
        } else {
            $prevData = array(
                'parentclasses' => array(),
                'interfaces' =>  array()
            );
        }

        $classData                    = $this->processClass($fqcn);
        $classCache['classes'][$fqcn] = $classData;
        $classCache['class_fqcn'] = $this->class_fqcn;
        $classCache['class_func_menu_entries'] = $this->createMenuEntries($this->class_fqcn, $this->coreIndex['function_list']);

        $fileData['classdata']['file'] = $fileName;
        $fileData['classdata']['fqcn'] = $fqcn;
        $fileData['classdata']['data'] = $classData;

        $fileData['extends']    = $this->getUpdatedExtraData($fqcn, $prevData, $classData, $classCache, $extends, 'parentclasses', 'extends');
        $fileData['interfaces'] = $this->getUpdatedExtraData($fqcn, $prevData, $classData, $classCache, $implements, 'interfaces', 'implements');

        $classCache['file_fqcn'][$fileName] = $fqcn;
        $classCache['fqcn_file'][$fqcn]     = $fileName;

        file_put_contents('.phpcomplete_extended/'. $cacheFileName, json_encode($fileData));
        file_put_contents('.phpcomplete_extended/phpcomplete_index', json_encode($classCache));
        $this->execHook("postUpdateIndex", false, $classData, $classCache, $this);
        $this->writePluginIndexes();

        return array($classCache, $fileData);
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

    private function listVendorLibraries()
    {
        $vendorLibs = array();

        $autoloadNamespaces = require 'vendor/composer/autoload_namespaces.php';
        foreach ($autoloadNamespaces as $namespace => $directory) {
            if($namespace == "") {
                continue;
            }
            $vendorLibs[$namespace] = $this->normalizePath($directory[0]);
        }
        return $vendorLibs;
    }

    private function normalizePath($path)
    {
        if($path == "") {
            return "";
        }

        $cwd = str_replace('\\','/',getcwd())."/";
        if(strpos($cwd, ':') == 1) {
            $drive = strtolower(substr($cwd, 0, 2));
            $cwd = substr_replace($cwd, $drive, 0, 2);
        }
        $path = str_replace("\\", '/', $path);
        if(strpos($path, ':') == 1) {
            $drive = strtolower(substr($path, 0, 2));
            $path = substr_replace($path, $drive, 0, 2);
        }
        $path = str_replace($cwd, '', $path);
        return $path;
    }

    public function generateIndex() 
    {
        $this->processCoreIndexFile();
        $time = microtime(true); // Gets microseconds
        //TODO: pasre constructor for doctype
        $classMap = require 'vendor/composer/autoload_classmap.php';
        $cwd = str_replace('\\','/',getcwd())."/";
        if(strpos($cwd, ':') == 1) {
            $drive = strtolower(substr($cwd, 0, 2));
            $cwd = substr_replace($cwd, $drive, 0, 2);
        }
        array_walk($classMap, function(&$item, $key) use ($cwd){
            $item = str_replace("\\", '/', $item);
            if(strpos($item, ':') == 1) {
                $drive = strtolower(substr($item, 0, 2));
                $item = substr_replace($item, $drive, 0, 2);
            }
            $item = str_replace($cwd, '', $item);
        });
        $out = array();
        $out['namespaces'] = array();
        $out['interface'] = array();
        $out['fqcn_file'] = $classMap;
        $out['file_fqcn'] = array_flip($classMap);
        $out['extends']  = array();
        $out['implements'] = array();
        $out['vendor_libs'] = $this->listVendorLibraries();
        //$this->file_fqcn = $classMap;
        $this->file_fqcn = $out['file_fqcn'];
        $this->fqcn_file = $out['fqcn_file'];

        $this->execHook("init", false, $this->loader);

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

            if(!$this->validateClass($file)) {
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

    public function writePluginIndexes()
    {
        $indexes = $this->execHook('getIndex', true);
        if(empty($indexes)) {
            return;
        }
        $this->writeToFile($this->pluginIndexFile, json_encode($indexes));
    }

    public function processClass($fqcn)
    {
        if(array_key_exists($fqcn, $this->processedClasses)) {
            return $this->processedClasses[$fqcn];
        }
        $out = array();
        $classData = $this->getClassInfo($fqcn);

        $baseProperties = $classData;
        $parentClass = $classData['parentclass'];
        $parentProperties = array();
        if(!empty($parentClass)) {
            $parentProperties = $this->processClass($parentClass);
        }
        foreach($classData['interfaces'] as $interface) {
            $interfaceProperties = $this->processClass($interface);
            $baseProperties = $this->mergeClassProperties($baseProperties, $interfaceProperties);
        }
        $merge = $this->mergeClassProperties($baseProperties, $parentProperties);

        $this->processedClasses[$fqcn] = $merge;

        return $merge;
    }

    private function mergeClassProperties($baseProperties, $parentProperties) 
    {
        if(empty($parentProperties)) {
            return $baseProperties;
        }
        if(empty($baseProperties)) {
            return $parentProperties;
        }

        $mergedProperties = $this->getEmptyMergeProperty();

        foreach($baseProperties['methods']['modifier'] as $modifier => $methods) {
            $mergedProperties['methods']['modifier'][$modifier] = array_unique(array_merge($baseProperties['methods']['modifier'][$modifier], $parentProperties['methods']['modifier'][$modifier]));
        }
        $basePropertiesMethods = array_keys($baseProperties['methods']['all']);
        $parentPropertiesMethods = array_keys($parentProperties['methods']['all']);
        $combinedMethods = array_merge($basePropertiesMethods, $parentPropertiesMethods);
        $methodSummary = array_count_values($combinedMethods);
        foreach($methodSummary as $method => $count) {
            if($count == 1) {
                if(in_array($method, $basePropertiesMethods)) {
                    $mergedProperties['methods']['all'][$method] = $baseProperties['methods']['all'][$method];
                }
                if(in_array($method, $parentPropertiesMethods)) {
                    $mergedProperties['methods']['all'][$method] = $parentProperties['methods']['all'][$method];
                }
            } else if($count == 2) {
                $basePropertiesMethod = $baseProperties['methods']['all'][$method];
                $parentPropertiesMethod = $parentProperties['methods']['all'][$method];
                $mergedProperties['methods']['all'][$method] = $parentPropertiesMethod;
                if(is_array($parentPropertiesMethod) && !$parentPropertiesMethod['inheritdoc']) {
                    $mergedProperties['methods']['all'][$method] = $parentPropertiesMethod;
                }
                if(is_array($parentPropertiesMethod) && !$basePropertiesMethod['inheritdoc']) {
                    $mergedProperties['methods']['all'][$method] = $basePropertiesMethod;
                }
            }
        }
        if(!array_key_exists('alias', $parentProperties['namespaces'])) {
            $parentProperties['namespaces']['alias'] = array();
        }
        if(!array_key_exists('alias', $baseProperties['namespaces'])) {
            $parentProperties['namespaces']['alias'] = array();
        }
        $mergedProperties['namespaces']['uses'] = array_merge( $parentProperties['namespaces']['uses'] , $baseProperties['namespaces']['uses']);
        $mergedProperties['namespaces']['alias'] = array_merge( $parentProperties['namespaces']['alias'] , $baseProperties['namespaces']['alias']);

        if(array_key_exists('file', $baseProperties['namespaces'])) {
            $mergedProperties['namespaces']['file'] = $baseProperties['namespaces']['file'];
        }

        foreach($baseProperties['properties']['modifier'] as $modifier => $properties) {
            $mergedProperties['properties']['modifier'][$modifier] = array_unique(array_merge($baseProperties['properties']['modifier'][$modifier], $parentProperties['properties']['modifier'][$modifier]));
        }
        $mergedProperties['properties']['all'] = array_merge($baseProperties['properties']['all'], $parentProperties['properties']['all']);
        $mergedProperties['parentclass'] = isset($baseProperties['parentclass'])? $baseProperties['parentclass']: "";

        $mergedProperties['parentclasses'] = array();
        array_push($mergedProperties['parentclasses'], $baseProperties['parentclass'], $parentProperties['parentclass']);
        $mergedProperties['parentclasses'] = array_unique($mergedProperties['parentclasses']);
        $mergedProperties['interfaces'] = array_unique(array_merge($baseProperties['interfaces'], $parentProperties['interfaces']));

        $mergedProperties['parentclasses'] = array_filter($mergedProperties['parentclasses'], function($var){
            return !empty($var);
        });

        $mergedProperties['interfaces'] = array_filter($mergedProperties['interfaces'], function($var){
            return !empty($var);
        });

        $mergedProperties['constants'] = array_unique(array_merge($baseProperties['constants'], $parentProperties['constants']));

        $mergedProperties['file']       = $baseProperties['file'];
        $mergedProperties['startLine']  = $baseProperties['startLine'];
        $mergedProperties['docComment'] = $baseProperties['docComment'];
        $mergedProperties['classname']  = $baseProperties['classname'];
        return $mergedProperties;
    }

    public function validateClass($fileName)
    {
        if(!is_file($fileName)) {
            return false;
        }

        if(array_key_exists($fileName, $this->validFiles)) {
            return $this->validFiles[$fileName];
        }

        $parsedClassData = $this->parseClass($fileName);
        $namespaces = $parsedClassData['namespaces'];
        $classLineData = $parsedClassData['class_line_data'];
        $classTokens = array();
        $classTokens[] = $classLineData['extends'];
        $classTokens = array_merge($classTokens,  $classLineData['implements']);
        $className = $classLineData['classname'];
        $tokens = array();
        //print_r($namespaces);exit;
        foreach($classTokens as $classToken) {
            if(empty($classToken)) {
                continue;
            }
            $fqcn = $this->guessClass($classToken, $namespaces);
            if(empty($fqcn)) {
                return false;
            }
            if(!array_key_exists($fqcn, $this->fqcn_file)) { //it may be an internal interface
                try {
                    $reflectionClass = new \ReflectionClass($fqcn);
                    continue;
                } catch (\Exception $e) {
                    return false;
                }
            }
            $isValidFqcnProperties = $this->validateClass($this->fqcn_file[$fqcn]);
            if(!$isValidFqcnProperties) {
                return false;
            }
        }
        foreach($parsedClassData['constructor_arguments'] as $classToken) {
            if(empty($classToken)) {
                continue;
            }
            if($classToken == $className) {
                continue;
            }
            $fqcn = $this->guessClass($classToken, $namespaces);
            if(empty($fqcn)) {
                return false;
            }
            if(!array_key_exists($fqcn, $this->fqcn_file)) { //it may be an internal interface
                try {
                    $reflectionClass = new \ReflectionClass($fqcn);
                    continue;
                } catch (\Exception $e) {
                    return false;
                }
            }
            $isValidFqcnProperties = $this->validateClass($this->fqcn_file[$fqcn]);
            if(!$isValidFqcnProperties) {
                return false;
            }
        }

        if(array_key_exists($className, $this->fqcn_file) && $this->fqcn_file[$className] == $fileName) {
            $classFqcn = $className;
        } else{
            $classFqcn = $this->guessClass($className, $namespaces);
        }

        if(empty($classFqcn)) {
            return false;
        }

        $this->validFiles[$fileName] = $classFqcn;
        return $classFqcn;
    }

    private function getClassInfo($fqcn)
    {
        $classData = $this->getEmptyClassData($fqcn);
        if(empty($fqcn)) {
            return $classData;
        }
        try {
            $reflectionClass = new \ReflectionClass($fqcn);
        } catch (\Exception $e) {
            return $classData;
        }

        if($reflectionClass->isInternal()) { 
            $fqcn = $reflectionClass->getName();
            if(array_key_exists($fqcn, $this->coreIndex['classes'])) {
                return $this->coreIndex['classes'][$reflectionClass->getName()];
            } else {
                return $classData;
            }
        }
        $classContent = array();

        $classData['file']      = $reflectionClass->getFileName();
        $classData['startLine'] = $reflectionClass->getStartLine();
        $docComment             = $reflectionClass->getDocComment();
        if(!$docComment) {
            $docComment = "";
        }
        preg_match("/(\\\\)?(\w+)$/", $reflectionClass->name, $classNameMatches);
        $classData['docComment']         = $this->trimDocComment($docComment);
        $classContent                    = $this->getClassContent($reflectionClass->getFileName(), $fqcn);

        $classData['namespaces']['file'] = $reflectionClass->getNamespaceName();
        $parsedClassData                 = $this->parseClass($classData['file']);
        $className                       = $classNameMatches[2];
        $classData['namespaces']         = $parsedClassData['namespaces'];
        $classData['className']          = $className;
        $this->classes[]                 = $className;

        if(array_key_exists($className, $this->class_fqcn)) {
            if(is_array($this->class_fqcn[$className])
                && !in_array($fqcn, $this->class_fqcn[$className])
            ) {

                $this->class_fqcn[$className][] = $fqcn;

            } elseif(is_string($this->class_fqcn[$className])
                && $this->class_fqcn[$className] != $fqcn
            ) {

                $fqcns                        = array();
                $fqcns[]                      = $this->class_fqcn[$className];
                $fqcns[]                      = $fqcn;
                $this->class_fqcn[$className] = $fqcns;
            }
        } else {
            $this->class_fqcn[$className] = $fqcn;
        }

        $this->getConstantData($reflectionClass, $classData);

        $classMethods = $reflectionClass->getMethods(); 

        foreach($classMethods as $reflectionMethod) {
            if($reflectionMethod->class === $fqcn) {
                $this->getMethodData($reflectionMethod, $classContent, $classData);
            }
        }

        $classProperties = $reflectionClass->getProperties();
        foreach($classProperties as $classProperty) {
            if($classProperty->class == $fqcn){
                $this->getPropertyData($classProperty, $classData);
            }
        }

        $classData['parentclass'] = "";
        $parentClass = $reflectionClass->getParentClass();
        //if($parentClass && !$parentClass->isInternal() ){
        if($parentClass){
            $classData['parentclass'] = $parentClass->getName();
        }
        $classData['interfaces'] = $reflectionClass->getInterfaceNames();
        $classData['classname'] = $className;

        return $classData;
    }

    private function getMethodData(ReflectionMethod $reflectionMethod, $classContent, &$classData) 
    {
        //TODO: parse local variable
        $modifiers  = Reflection::getModifierNames($reflectionMethod->getModifiers());
        $startLine  = $reflectionMethod->getStartLine();
        $endLine    = $reflectionMethod->getEndline();
        $parameters = $reflectionMethod->getParameters();
        $docComment = $reflectionMethod->getDocComment();
        if(!$docComment) {
            $docComment = "";
        }
        $parsedComment     = $this->parseDocComment($docComment, 'method');
        $out               = array();
        $out['params']     = array();
        $out['docComment'] = $this->trimDocComment($docComment);
        $out['inheritdoc'] = $parsedComment['inheritdoc'];
        $out['startLine']  = $startLine;
        $out['endLine']    = $endLine;

        $origin = $reflectionMethod->getDeclaringClass()->getFileName() == false? "": $reflectionMethod->getDeclaringClass()->getFileName();
        $origin = $this->normalizePath($origin);
        $out['origin'] = $origin;

        $params = array();
        foreach ($parameters as $parameter) {
            $parameterName = '$'.$parameter->getName();
            try {
                $parameterClass = $parameter->getClass();
            } catch (\Exception $e) {
                $parameterClass = "";
            }
            $parameterType = "";
            //not defined try to find in the doccomment
            if(empty($parameterClass)) {
                if(array_key_exists($parameterName, $parsedComment['params'])) {
                    $parameterType = $parsedComment['params'][$parameterName];
                }
            } else {
                $parameterType = $parameter->getClass()->getName();
            }
            $params[] = $parameterType. ' '. $parameterName;
            $out['params'][$parameterName] = $parameterType;
        }

        if(array_key_exists('return', $parsedComment) && $parsedComment['return'] != "") {
            $returnType  = "";
            $arrayReturn = 0;
            $returnTypes = explode('|', trim($parsedComment['return']));
            foreach ($returnTypes as $rType) {
                if(preg_match('/\[\]$/', $rType)) {
                    $arrayReturn = 1;
                    $rType = trim($rType, "[]");
                }
                if(!$this->isScalar($rType)) {
                    $returnType = $rType;
                    if($returnType[0] == '\\') {
                        $returnType = substr($returnType, 1);
                    }
                    break;
                }
            }
            if(empty($returnType)) {
                $out['return'] = "";
            } else {
                $out['return'] = $this->guessClass($returnType, $classData['namespaces']);
            }
            $out['array_return'] = $arrayReturn;
        }

        $return = empty($parsedComment['return'])? "none" : $parsedComment['return'];
        $out['signature']  = '('. join(', ', $params) . ')  : '. $return;

        foreach($modifiers as $modifier) {
            $classData['methods']['modifier'][$modifier][] = $reflectionMethod->name;
        }

        $classData['methods']['all'][$reflectionMethod->name] = $out;
    }

    private function getConstantData(ReflectionClass $reflectionClass, &$classData)
    {
        $constants = array();
        try {
            $constants = $reflectionClass->getConstants();
        } catch (\Exception $e) {
            //echo  $e->getMessage();
        }
        foreach ($constants as $key => $value) {
            if(is_string($value)) {
                $constants[$key] = $this->fixUTF8($value);
            }
        }
        $classData['constants'] = $constants;
    }

    private function parseDocComment($docComment, $type)
    {
        $out = array();
        $comments = explode('*', $docComment);
        if(count($comments) > 1) {
            //var_dump($comments);
        }
        if($type == 'method') {
            $out['params'] = array();
            $out['return'] = "";
            $out['inheritdoc'] = 0;
        } else if($type == 'property') {
            $out['var'] = "";
            $out['inheritdoc'] = 0;
        }
        foreach ($comments as $comment) {
            $comment = trim(trim($comment), "{}/");
            $splits = preg_split('/\s+/', $comment);
            if(empty($splits)) {
                continue;
            }
            if($type=='method'){         
                switch ($splits[0]) {
                case '@param':
                    if(count($splits) == 1) {
                        continue;
                    } elseif(count($splits) == 2) {
                        $splits[2] = $splits[1];
                    }
                    $out['params'][$splits[2]] = $splits[1];
                    break;
                case '@return':
                    if(count($splits) < 2){
                        continue;
                    }
                    $out['return'] = $splits[1];
                    break;
                case '@inheritdoc':
                    $out['inheritdoc'] = 1;
                    break;
                } 
            } else if($type == 'property') {
                if($splits[0] == '@var') {
                    if(count($splits) < 2) {
                        continue;
                    }
                    $out['var'] = $splits[1];
                }
            }
        }
        return $out;
    }

    private function getPropertyData(ReflectionProperty $reflectionProperty, &$classData)
    {
        $sclarTypes = array('boolean', 'integer','float', 'string', 'array', 'object', 'resource', 'mixed', 'number', 'callback');
        $modifiers = Reflection::getModifierNames($reflectionProperty->getModifiers());
        $docComment = $reflectionProperty->getDocComment();
        if(!$docComment) {
            $docComment = "";
        }
        $parsedComment = $this->parseDocComment($docComment, 'property');
        $type = $parsedComment['var'];
        $out = array();
        $out['type'] = "";
        $out['array_type'] = 0;
        if(preg_match('/\[\]$/', $type)) {
            $out['array_type'] = 1;
        }
        if(!$this->isScalar($type) && !empty($type)) {
            if($type[0] == '\\') {
                $type = substr($type, 1);
            }
            if(preg_match('/\[\]$/', $type)) {
                $type = trim($type, "[]");
            }
            $out['type'] = $this->guessClass($type, $classData['namespaces']);
        }
        $out['inheritdoc'] = $parsedComment['inheritdoc'];
        $out['docComment'] = $this->trimDocComment($docComment);
        $origin = $reflectionProperty->getDeclaringClass()->getFileName() == false? "": $reflectionProperty->getDeclaringClass()->getFileName();
        $origin = $this->normalizePath($origin);
        $out['origin'] = $origin;
        $classData['properties']['all'][$reflectionProperty->name] = $out;
        foreach ($modifiers as $modifier) {
            $classData['properties']['modifier'][$modifier][] = $reflectionProperty->name;
        }
    }

    private function guessClass($classToken, $namespaces)
    {
        if(empty($classToken)) {
            return "";
        }
        if($classToken[0] == '\\') {
            return $classToken; //assuming it is a internal clas
        }
        //replace alias namespaces
        foreach ($namespaces['alias'] as $aliasName => $usesKey) {
            $useValue = $namespaces['uses'][$usesKey];
            if($usesKey == $useValue) {
                $namespaces['uses'][$aliasName] = $useValue;
            } else {
                $namespaces['uses'][$aliasName] = $useValue. "\\". $usesKey;
            }
        }

        $aliasReverse = array_flip($namespaces['alias']);
        if(array_key_exists($classToken, $namespaces['uses']) && !array_key_exists($classToken, $aliasReverse)){ //in uses 
            if(array_key_exists($classToken, $namespaces['alias'])) {
                $fqcn = $namespaces['uses'][$classToken];
            }  else {
                $fqcn = $namespaces['uses'][$classToken]. "\\". $classToken;
            }
        } elseif(!empty($namespaces['alias']) && array_key_exists($classToken, $namespaces['alias'])) { //in alias
            $classToken = $namespaces['alias'][$classToken];
            $fqcn = $namespaces['uses'][$classToken]. "\\". $classToken;
        } else { //append with file namespace
            $fileNameSpace = "";
            if(array_key_exists('file', $namespaces)) { //has namespace
                $fileNameSpace = $namespaces['file'] . "\\";

            }
            $fqcn =  trim($fileNameSpace. $classToken);
        }

        if($fqcn == "" && array_key_exists($classToken, $this->fqcn_file)){
            $fqcn = $classToken;
        }

        if(array_key_exists($fqcn, $this->fqcn_file)) {
            return $fqcn;
        } else{
            try {
                $reflectionClass = new \ReflectionClass($fqcn);
                return $fqcn;
            } catch (\Exception $e) {
                //ldd($e->getMessage());
                $fqcn = "";
            }
            if(empty($fqcn)){
                try {
                    $reflectionClass = new \ReflectionClass($classToken);
                    return $classToken;
                } catch (\Exception $e) {
                    $fqcn = "";
                }
            }
        }
        return $fqcn;
    }

    private function getClassContent($fileLocation, $className)
    {
        $fp = fopen($fileLocation, 'r');
        if(!$fp) {
            throw new \Exception("read file of  class ". $className." failed");
        }

        $classContent = array();
        while (($line = fgets($fp, 4096)) !== false) {
            $classContent[] = $line;
        }
        if (!feof($fp)) {
            echo "Error: unexpected fgets() fail\n";
        }
        fclose($fp);
        return $classContent;
    }

    private function trimDocComment($docComment)
    {
        $comments = explode("\n", $docComment);
        $newComments = array();
        $count = 1;
        foreach ($comments as $comment) {
            $comment = trim($comment);
            $comment = str_replace("/**", '', $comment, $count);
            $comment = str_replace("*", '', $comment, $count);
            $comment = str_replace("*/", '', $comment, $count);
            $comment = str_replace("/", '', $comment, $count);
            $newComments[] = $comment;
        }
        $docComment = join("\n", $newComments);
        $docComment = $this->fixUTF8($docComment);
        return $docComment;
    }

    private function fixUTF8($content)
    {
        return iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($content));
    }


    private function getEmptyMergeProperty() 
    {
        return array(
            'methods' => array(
                'modifier' => array(
                    'public' => array(),
                    'private' => array(),
                    'protected' => array(),
                    'final' => array(),
                    'static' => array(),
                    'interface' => array(),
                    'abstract' => array(),
                ),
                //'all' => array(),
                'all' => array("nnnnnnnn" => "nnnnnnnnnnnn")
            ),
            'namespaces' => array(
                'uses' => array(),
                'alias' => array()
            ),
            'properties' => array(
                'modifier' => array(
                    'public' => array(),
                    'private' => array(),
                    'protected' => array(),
                    'final' => array(),
                    'static' => array(),
                    'interface' => array(),
                    'abstract' => array(),
                ),
                //'all' => array()
                'all' => array("nnnnnnnn" => "nnnnnnnnnnnn"),
                "origin" => "",
            ),
            'constants' => array(),
            'parentclasses' => array(),
            'interfaces' => array(),
            'classname' => "",
            'startLine' => 0,
            'docComment' => "",
            'file' => ''
        );
    }

    private function getEmptyClassData($className)
    {
        $classData = array();
        $classData['classname'] = $className;
        $classData['startLine'] = 1;
        $classData['docComment'] = "";
        $classData['methods'] = array(
            'modifier' => array(
                'public' => array(),
                'private' => array(),
                'protected' => array(),
                'final' => array(),
                'static' => array(),
                'interface' => array(),
                'abstract' => array(),
            ),
            'all' => array("nnnnnnnn" => "nnnnnnnnnnnn"),
            'origin' => ""
        );
        $classData['properties'] = array(
            'modifier' => array(
                'public' => array(),
                'private' => array(),
                'protected' => array(),
                'final' => array(),
                'static' => array(),
                'interface' => array(),
                'abstract' => array(),
            ),
            'all' => array("nnnnnnnn" => "nnnnnnnnnnnn")
        );
        $classData['parentclass'] = "";
        $classData['parentclasses'] = array();
        $classData['interfaces'] = array();
        $classData['file'] = "";
        $classData['namespaces'] = array(
            'uses' => array(),
            'alias' => array(),
        );
        
        $classData['constants'] = array();

        return $classData;
    }

    private function isScalar($type) 
    {
        $scalarsTypes = array('boolean', 'integer','float', 'string', 'array', 'object', 
            'resource', 'mixed', 'number', 'callback', 'null', 'void', 'bool', 'self', 'int', 'callable');
        return in_array(strtolower($type), $scalarsTypes);
    }

    private function getFQCNfromFile($file, $file_fqcn)
    {
        if(array_key_exists($file, $this->file_fqcn)) {
            return $this->file_fqcn[$file];
        } else{
            $parsedData = parseClass($file);
            $classLineData = $parsedData['class_line_data'];
            $className = $classLineData['classname'];
            if(array_key_exists('file', $parsedData['namespaces'])) {
                $fqcn = $parsedData['namespaces']['file'] . "\\" . $className;
            } else {
                $fqcn = $className;
            }
            return $fqcn;
        }
    }

    /**
     * Gets the value of indexFileName
     *
     * @return indexFileName
     */
    public function getIndexFileName()
    {
        return $this->indexFileName;
    }

    /**
     * Sets the value of indexFileName
     *
     * @param string $indexFileName name of the file
     *
     * @return $this
     */
    public function setIndexFileName($indexFileName ="./.phpcomplete_extended/phpcomplete_index")
    {
        $this->indexFileName = $indexFileName;
        return $this;
    }

    /**
     * Gets the value of reportFileName
     *
     * @return reportFileName
     */
    public function getReportFileName()
    {
        return $this->reportFileName;
    }

    /**
     * Sets the value of reportFileName
     *
     * @param string $reportFileName report file name
     *
     * @return $this
     */
    public function setReportFileName($reportFileName="./.phpcomplete_extended/report.txt")
    {
        $this->reportFileName = $reportFileName;
        return $this;
    }

    public function writeToFile($fileName, $data) 
    {
        file_put_contents($fileName, $data);
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

    /**
     * Splits multple use, declarations in one line
     * Easier for parsing later
     * @param array $classContent
     */
    public function formatClassContent($classContent)
    {
        $namespace  = "";
        $uses       = array();
        $aliases    = array();
        $className  = "";
        $extends    = array();
        $implements = array();

        $isMultiLine           = false;
        $multiLine             = "";
        $currentSection        = "";
        $formattedClassContent = array();

        $fullLine = "";
        $isClassLine = false;
        $classLine = "";
        $useEnded = false;

        $isConstructorLine = false;
        $constructorLine = "";

        foreach ($classContent as $line) {

            $line = trim(str_replace("<?php", "", $line), "\t\n ");
            $line = str_replace('/* final */', "", $line);
            if(!$isMultiLine && !$isClassLine && !$isConstructorLine && !$useEnded && count(explode(";", $line)) > 2) {
                $formattedClassContent = array_merge($formattedClassContent, explode(';', $line));
                continue;
            }

            if($isClassLine) {
                $classLine .= " " . $line;
                if(strpos($classLine, "{")) {
                    $isClassLine = false;
                    $useEnded = true;
                    $formattedClassContent[] = trim($classLine, "{}");
                }
            }

            if($isConstructorLine) {
                $constructorLine .= " " . $line;
                if(strpos($constructorLine, "{")) {
                    $isConstructorLine = false;
                    $formattedClassContent[] = trim($constructorLine, "{}");
                    break;
                }
            }

            if(!$isMultiLine && !$isClassLine && !$isConstructorLine &&
                (
                    preg_match("/^\s*namespace/", $line) 
                    || preg_match("/^\s*use/", $line) 
                    || preg_match("/^\s*(abstract|final)?\s*(class|interface)/", $line) 
                    || preg_match("/^\s*interface/", $line)
                    || preg_match("/^\s*public\s+function\s+__construct/", $line)
                )
            ){
                if(preg_match("/^\s*(abstract|final)?\s*(class|interface)/", $line )) {
                    if(strpos($line, "{") === false) {
                        $isClassLine = true;
                        $classLine .= trim($line, ";");
                        continue;
                    } else {
                        $isClassLine = false;
                        $useEnded = false;
                        $formattedClassContent[] = trim($line, ";{}");
                        continue;
                    }
                }

                if(preg_match("/^\s*public\s+function\s+__construct/", $line)) {
                    if(strpos($line, ")") === false) {
                        $isConstructorLine = true;
                        $constructorLine .= trim($line, ";");
                        continue;
                    } else {
                        $isConstructorLine = false;
                        $formattedClassContent[] = trim($line, ";{}");
                        break;
                    }
                }

                if(strpos($line, ";") === false && strpos($line, ",") >= 0) {
                    $isMultiLine = true;
                    $multiLine .= $line;
                } else {
                    $formattedClassContent[] = trim($line, ";{");
                }
                continue;
            }

            if($isMultiLine) {
                $multiLine .= $line;
                if(strpos($multiLine, ";") !== false) {
                    $isMultiLine = false;
                    if(explode(";", $multiLine) >2) {
                        $formattedClassContent = array_merge($formattedClassContent, explode(";", $multiLine));
                    } else {
                        $formattedClassContent[] = trim($multiLine, ";{");
                    }
                }
            }
        }
        array_walk($formattedClassContent, function(&$item){
            $item = trim($item);
        });
        return $formattedClassContent;
    }

    public function parseClass($fileName)
    {
        if(array_key_exists($fileName, $this->parsedClasses)) {
            return $this->parsedClasses[$fileName];
        }
        $namespace = "";
        $uses = array();
        $aliases = array();
        $className = "";
        $extends = array();
        $implements = array();
        $constructorArguments = array();
        $classNameregex = "/^\s*(abstract|final)?(\s+)?(class)\s+([\\\\,\w]+)(\s+extends\s+([\\\\\w]+))?(\s+implements\s+([^{]*))?/";
        $interfaceRegex = "/^\s*interface\s+([\\\\,\w]+)(\s+extends(.*))?/"; 
        $constructorRegex = "/^\s*public\s+function\s+__construct\((.*)\)/";
        $classContent = file($fileName);
        $formattedClassContent = $this->formatClassContent($classContent);
        $parseEnded = false;
        foreach ($formattedClassContent as $line) {
            if(preg_match("/\s*namespace\s+(.*)(;)?/", $line, $matches)) {
                $namespace = trim($matches[1], ";");
            }
            if(preg_match("/\s*use\s+(.*)/", $line, $matches)) {
                $classUses = explode(",", $matches[1]);
                foreach($classUses as $use) { // use statement
                    $alias = "";
                    if(empty($use)) {
                        continue;
                    }
                    if(strpos($use, " as ")) {
                        $ases = explode(" as ", $use);
                        $use = trim($ases[0]);
                        $alias = trim($ases[1], ";");
                    }
                    $useTokens = explode("\\", $use);
                    if(count($useTokens) == 1) {
                        $lastToken = trim($useTokens[0], "; ");
                        $uses[$useTokens[0]] = $useTokens[0];
                    } else {
                        $lastToken = trim(array_pop($useTokens), "; ");
                        if(array_key_exists($lastToken, $uses)) {
                            $lastToken = $use;
                            $uses[$lastToken] = $use;
                        } else {
                            $uses[$lastToken] = join("\\", $useTokens);
                        }
                    }

                    if(!empty($alias)) {
                        $aliases[$alias] = $lastToken;
                    }
                }
            }
            if(preg_match($classNameregex, $line, $matches)) {
                if(strlen($className) > 0){
                    continue;
                }
                $className = $matches[4];
                if(!empty($matches[6])) { //extends
                    $extends = trim($matches[6], " \n\r");
                    if(strpos($extends, "\\")) {
                        $useTokens = explode("\\", $extends);
                        $firstToken = $useTokens[0];
                        $lastToken = array_pop($useTokens);
                        $extends = $lastToken;
                        if(array_key_exists($firstToken, $uses)) {
                            $uses[$lastToken] = $uses[$firstToken]. "\\" . join("\\", $useTokens);
                        } else {
                            $uses[$lastToken] = $namespace . "\\" . join("\\", $useTokens);
                            //$uses[$lastToken] = join("\\", $useTokens);
                        }
                    }
                }
                if(!empty($matches[8])) {
                    $classImplements = explode(",", $matches[8]);
                    foreach($classImplements as $implement) { //implements
                        if(empty($implement)) { 
                            continue;
                        }
                        $implement = trim($implement, " \n\r{");
                        if(strpos($implement, "\\")) {
                            $useTokens = explode("\\", $implement);
                            $firstToken = $useTokens[0];
                            $lastToken = array_pop($useTokens);
                            $implements[] = $lastToken;
                            if(array_key_exists($firstToken, $uses)) {
                                $uses[$lastToken] = $uses[$firstToken]. "\\" . join("\\", $useTokens);
                            } else {
                                $uses[$lastToken] = $namespace . "\\" . join("\\", $useTokens);
                                //print_r($useTokens);
                                //$uses[$lastToken] = join("\\", $useTokens);
                            }
                        } else {
                            $implements[] = $implement;
                        }
                    }
                }
            }
            if(preg_match($constructorRegex, $line, $matches)) {
                $arguments = explode(",", $matches[1]);
                foreach ($arguments as $argument) {
                    $argument = trim($argument);
                    if(isset($argument[0]) && $argument[0] == '$') {
                        continue;
                    }
                    $segments = preg_split("/\s+/", $argument);
                    if(count($segments) == 1) {
                        continue;
                    }
                    if($this->isScalar($segments[0])) {
                        continue;
                    }
                    $argumentFQCN = $segments[0];
                    if(strpos($segments[0], "\\")) {
                        $useTokens = explode("\\", $segments[0]);
                        $firstToken = $useTokens[0];
                        $lastToken = array_pop($useTokens);
                        $constructorArguments[] = $lastToken;
                        if(array_key_exists($firstToken, $uses)) {
                            $uses[$lastToken] = $uses[$firstToken]. "\\" . join("\\", $useTokens);
                        } else {
                            $uses[$lastToken] = $namespace . "\\" . join("\\", $useTokens);
                        }
                    } else {
                        $constructorArguments[] = $argumentFQCN;
                    }
                }
            }
            if(preg_match($interfaceRegex, $line, $matches)) {
                $className = $matches[1];
                if(!empty($matches[3])) {
                    foreach (explode(",", $matches[3]) as $implement) {
                        //triplicate, will extract later
                        $implement = trim($implement, " \n\r{");
                        if(strpos($implement, "\\")) {
                            $useTokens = explode("\\", $implement);
                            $firstToken = $useTokens[0];
                            $lastToken = array_pop($useTokens);
                            $implements[] = $lastToken;
                            if(array_key_exists($firstToken, $uses)) {
                                $uses[$lastToken] = $uses[$firstToken]. "\\" . join("\\", $useTokens);
                            } else {
                                $uses[$lastToken] = $namespace . "\\" . join("\\", $useTokens);
                                //print_r($useTokens);
                                //$uses[$lastToken] = join("\\", $useTokens);
                            }
                        } else {
                            $implements[] = $implement;
                        }
                    }
                }
                $parseEnded = true;
            }
        }

        $parsedData = array(
            'namespaces' => array(
                'uses' => $uses,
                'alias' => $aliases,
                'file' => $namespace,
            ),
            'class_line_data' => array(
                'implements' => $implements,
                'extends' => $extends,
                'classname' => $className
            ), 
            'constructor_arguments' =>  $constructorArguments
        );
        //ldd($parsedData);
        $this->parsedClasses[$fileName] = $parsedData;
        return $parsedData;
    }

    /**
     * Gets the value of coreIndex
     *
     * @return array 
     */
    public function getCoreIndex()
    {
        return $this->coreIndex;
    }

    /**
     * Sets the value of coreIndex
     *
     * @param array  $coreIndex 
     *
     * @return this 
     */
    public function setCoreIndex($coreIndex)
    {
        $this->coreIndex = $coreIndex;
        return $this;
    }

    /**
     * Gets the value of coreIndexFile
     *
     * @return string 
     */
    public function getCoreIndexFile()
    {
        return $this->coreIndexFile;
    }

    /**
     * Sets the value of coreIndexFile
     *
     * @param string  $coreIndexFile 
     *
     * @return this 
     */
    public function setCoreIndexFile($coreIndexFile)
    {
        $this->coreIndexFile = $coreIndexFile;
        return $this;
    }

    public function processCoreIndexFile()
    {
        $this->coreIndex = json_decode(file_get_contents($this->coreIndexFile), true);
    }
}

