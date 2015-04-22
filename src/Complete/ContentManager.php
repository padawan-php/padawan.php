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
        ScopeResolver $scopeResolver,
        Completer $completer,
        IndexProcessor $indexProcessor,
        ScopeProcessor $scopeProcessor
    ){
        $this->parser = $parser;
        $this->generator = $generator;
        $this->contextResolver = $contextResolver;
        $this->scopeResolver = $scopeResolver;
        $this->completer = $completer;
        $this->indexProcessor = $indexProcessor;
        $this->scopeProcessor = $scopeProcessor;
    }
    public function createCompletion(
        Project $project,
        $content,
        $line,
        $column,
        $file
    ){
        $entries = [];
        if($line){
            list($lines, $badLine, $completionLine) = $this->prepareContent(
                $content,
                $line,
                $column
            );
            try {
                $this->updateFileIndex($project, $lines, $file);
                $scope = $this->findScope(
                    $project, implode("\n", $lines), $line, $file
                );
            }
            catch(\Exception $e){
                $scope = new Scope;
            }
            $entries = $this->findEntries($project, $scope, $completionLine, $column, $lines);
        }
        elseif(!empty($content)) {
            $this->updateFileIndex($project, $content, $file);
        }

        return [
            "entries" => $entries,
            "context" => []
        ];
    }
    protected function findEntries(Project $project, Scope $scope, $badLine, $column, $lines){
        $context = $this->contextResolver->getContext($badLine, $column);
        return $this->completer->getEntries($project, $context, $scope);
    }
    protected function findScope(Project $project, $content, $line, $file){
        return $this->scopeResolver->findScope($project, $content, $line, $file);
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
    protected function updateFileIndex(Project $project, $lines, $file){
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
        $this->indexProcessor->clearResultNodes();
        $parser = $this->parser;
        $parser->setProcessor($this->indexProcessor);
        $nodes = $parser->parseContent($fqcn, $file, $content);
        $this->generator->processFileNodes(
            $project->getIndex(),
            $fqcn,
            $nodes
        );
    }

    private $parser;
    private $generator;
    private $contextResolver;
    private $scopeResolver;
    private $completer;
    private $indexProcessor;
    private $scopeProcessor;
}
