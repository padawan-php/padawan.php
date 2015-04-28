<?php

namespace Complete;

use Entity\Project;
use Entity\Completion\Scope;
use Parser\Parser;
use Generator\IndexGenerator;
use Entity\Completion\Entry;
use Entity\Completion\Context;
use Complete\Completer\Completer;
use Complete\Resolver\ContextResolver;
use Complete\Resolver\ScopeResolver;
use Parser\Processor\IndexProcessor;
use Parser\Processor\ScopeProcessor;


class ContentManager {
    public function __construct(
        Parser $parser,
        IndexGenerator $generator,
        ContextResolver $contextResolver,
        Completer $completer,
        IndexProcessor $indexProcessor,
        ScopeProcessor $scopeProcessor
    ){
        $this->parser = $parser;
        $this->generator = $generator;
        $this->contextResolver = $contextResolver;
        $this->completer = $completer;
        $this->indexProcessor = $indexProcessor;
        $this->scopeProcessor = $scopeProcessor;
        $this->cachePool        = [];
    }
    public function createCompletion(
        Project $project,
        $content,
        $line,
        $column,
        $file
    ){
        $start = microtime(1);
        $entries = [];
        if($line){
            list($lines, $badLine, $completionLine) = $this->prepareContent(
                $content,
                $line,
                $column
            );
            try {
                $scope = $this->processFileContent($project, $lines, $line, $file);
                echo microtime(1) - $start;
                echo " for indexing preparing\n";
                echo microtime(1) - $start;
                echo " for scope preparing\n";
            }
            catch(\Exception $e){
                $scope = new Scope;
            }
            $entries = $this->findEntries($project, $scope, $completionLine, $column, $lines);
                echo microtime(1) - $start;
                echo " for entries preparing\n";
        }
        elseif(!empty($content)) {
            $this->updateFileIndex($project, $content, $file);
        }
        echo microtime(1) - $start;
        echo " seconds for creaeting completion\n";

        return [
            "entries" => $entries,
            "context" => []
        ];
    }
    protected function findEntries(Project $project, Scope $scope, $badLine, $column, $lines){
        $context = $this->contextResolver->getContext($badLine, $column);
        return $this->completer->getEntries($project, $context, $scope);
    }
    /**
     * @TODO
     * Should check for bad lines
     */
    protected function prepareContent($content, $line, $column){
        $lines = explode(PHP_EOL, $content);
        if($line > count($lines)){
            $badLine = "";
        }
        else{
            $badLine = $lines[$line-1];
        }
        $completionLine = substr($badLine, 0, $column+1);
        $lines[$line-1] = "";
        return [$lines, trim($badLine), trim($completionLine)];
    }

    /**
     *
     * @return Scope
     */
    protected function processFileContent(Project $project, $lines, $line, $file){
        if(is_array($lines)){
            $content = implode("\n", $lines);
        }
        else {
            $content = $lines;
        }
        if(empty($content)){
            return;
        }
        $fqcn = $project->getIndex()->findFQCNByFile($file);
        if(!$fqcn){
            return;
        }
        if(!array_key_exists($file, $this->cachePool)){
            $this->cachePool[$file] = [0, null, null];
        }
        if($this->isValidCache($file, $content)){
            list($hash, $indexNodes, $scopeNodes) = $this->cachePool[$file];
        }
        else {
            $this->indexProcessor->clearResultNodes();
            $this->scopeProcessor->clearResultNodes();
            $this->scopeProcessor->setIndex($project->getIndex());
            $this->scopeProcessor->setLine($line);
            $parser = $this->parser;
            $parser->addProcessor($this->indexProcessor);
            $parser->addProcessor($this->scopeProcessor);
            $nodes = $parser->parseContent($fqcn, $file, $content);
            $this->generator->processFileNodes(
                $project->getIndex(),
                $fqcn,
                $nodes
            );
            $scopeNodes = $this->scopeProcessor->getResultNodes();
            $contentHash = hash('sha1', $content);
            $this->cachePool[$file] = [$contentHash, $nodes, $scopeNodes];
        }
        return $scopeNodes[0];
    }

    private function isValidCache($file, $content){
        $contentHash = hash('sha1', $content);
        list($hash) = $this->cachePool[$file];
        return $hash === $contentHash;
    }

    private $parser;
    private $generator;
    private $contextResolver;
    private $completer;
    private $indexProcessor;
    private $scopeProcessor;
    private $cachePool;
}
