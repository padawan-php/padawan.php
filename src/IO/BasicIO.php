<?php

namespace IO;

use Utils\PathResolver;

class BasicIO{
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
        $this->indexFileName    = './.padawan/project';
        $this->reportFileName   = './.padawan/report.txt';
    }

    /**
     * Gets PathResolver service
     *
     * @return PathResolver
     */
    public function getPath(){
        return $this->path;
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
    public function getIndexFileName($rootDir = null)
    {
        $indexPath = $this->indexFileName;
        if($rootDir){
            $indexPath = $this->getPath()->join([
                $rootDir,
                $indexPath
            ]);
        }
        return $indexPath;
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
}
