<?php

namespace IO;

class Writer extends BasicIO{
    public function write($project){
        $this->writeToFile(
            $this->getIndexFileName($project->getRootDir()),
            $this->prepareIndex($project)
        );
    }
    public function writeReport($invalidClasses){
        $this->writeToFile(
            $this->getReportFileName(),
            implode("\n", $invalidClasses)
        );
    }
    protected function prepareIndex($index){
        $str = serialize($index);
        return $str;
    }
    protected function writeToFile($fileName, $data)
    {
        $this->getPath()->write($fileName, $data);
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
