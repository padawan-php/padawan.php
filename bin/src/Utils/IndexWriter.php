<?php

namespace Utils;

class IndexWriter{

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

    /**
     *
     * @var PathResolver
     */
    private $path;

    public function __construct(PathResolver $path){
        $this->path             = $path;
        $this->indexFileName    = './.padawan.vim/phpcomplete_index';
        $this->reportFileName   = './.padawan.vim/report.txt';
        $this->coreIndexFile    = './.padawan.vim/core_index';
        $this->pluginIndexFile  = './.padawan/plugin_index';
    }

    public function writeIndex($index){
        $this->writeToFile(
            $this->getIndexFileName(),
            $this->prepareIndex($index)
        );
    }
    public function writeReport($invalidClasses){
        $this->writeToFile($this->getReportFileName(), implode("\n", $invalidClasses));
    }
    public function writePluginIndexes()
    {
        $indexes = $this->execHook('getIndex', true);
        if(empty($indexes)) {
            return;
        }
        $this->writeToFile($this->pluginIndexFile, json_encode($indexes));
    }
    protected function prepareIndex($index){
        $arr = $index->toArray();
        $arr["class_list"] = [];
        $arr["namespaces"] = array_values($arr["namespaces"]);
        foreach($arr["classes"] AS $fqcn => $class){
            $className = $class["classname"];
            $arr["class_list"][] = $className;
            $arr["class_func_menu_entries"][] = [
                "word" => $className,
                "kind" => "c",
                "menu" => $fqcn,
                "info" => $fqcn
            ];
        }
        ksort($arr["class_fqcn"]);
        sort($arr["class_list"]);
        asort($arr["fqcn_file"]);
        $jsonIndex = json_encode($arr);
        $lastJsonError = json_last_error();
        if($lastJsonError != JSON_ERROR_NONE) {
            exit;
        }
        return $jsonIndex;
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
    public function setIndexFileName($indexFileName)
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
    public function setReportFileName($reportFileName)
    {
        $this->reportFileName = $reportFileName;
        return $this;
    }




    protected function loadCoreIndex(){
        return [];
    }
    protected function writeToFile($fileName, $data)
    {
        $this->path->write($fileName, $data);
    }
    // @TODO Should refactor it with \Command\UpdateCommand
    //public function writeUpdatedClassInfo($fileName, $cacheFileName)
    //{
    //$time = microtime(true);
    //$this->generator->processCoreIndexFile();
    //$fileName        = $this->normalizePath($fileName);
    //$classCache      = json_decode(file_get_contents($this->indexFileName), true);
    //$extends         = $classCache['extends'];
    //$implements      = $classCache['implements'];
    //$this->fqcn_file = $classCache['fqcn_file'];
    //$this->file_fqcn = $classCache['file_fqcn'];
    //$this->class_fqcn = $classCache['class_fqcn'];
    //$fileData        = array();
    //if(!is_file($this->pluginIndexFile)) {
    //$pluginIndex = array();
    //} else{
    //$pluginIndex     = json_decode(file_get_contents($this->pluginIndexFile), true);
    //}

    //$this->execHook("init", false, $this->loader);
    //$this->execHook("preUpdateIndex", false, $pluginIndex);

    //$fqcn = $this->validateClass($fileName);
    //if(empty($fqcn)) {
    //return;
    //}

    //if(array_key_exists($fileName, $classCache['file_fqcn'])) {
    //$prevData = $classCache['classes'][$fqcn];
    //} else {
    //$prevData = array(
    //'parentclasses' => array(),
    //'interfaces' =>  array()
    //);
    //}

    //$classData                    = $this->processClass($fqcn);
    //$classCache['classes'][$fqcn] = $classData;
    //$classCache['class_fqcn'] = $this->class_fqcn;
    //$classCache['class_func_menu_entries'] = $this->createMenuEntries($this->class_fqcn, $this->coreIndex['function_list']);

    //$fileData['classdata']['file'] = $fileName;
    //$fileData['classdata']['fqcn'] = $fqcn;
    //$fileData['classdata']['data'] = $classData;

    //$fileData['extends']    = $this->getUpdatedExtraData($fqcn, $prevData, $classData, $classCache, $extends, 'parentclasses', 'extends');
    //$fileData['interfaces'] = $this->getUpdatedExtraData($fqcn, $prevData, $classData, $classCache, $implements, 'interfaces', 'implements');

    //$classCache['file_fqcn'][$fileName] = $fqcn;
    //$classCache['fqcn_file'][$fqcn]     = $fileName;

    //file_put_contents('.phpcomplete_extended/'. $cacheFileName, json_encode($fileData));
    //file_put_contents('.phpcomplete_extended/phpcomplete_index', json_encode($classCache));
    //$this->generator->execHook("postUpdateIndex", false, $classData, $classCache, $this);
    //$this->writePluginIndexes();

    //return array($classCache, $fileData);
    //}
}
