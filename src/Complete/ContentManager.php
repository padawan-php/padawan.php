<?php

namespace Complete;

use Entity\Project;
use Parser\Parser;
use Generator\IndexGenerator;
use Entity\Completion\Entry;
use Entity\Completion\Context;
use Complete\Completer\Completer;

class ContentManager {
    private $parser;
    private $generator;
    private $contextResolver;
    private $completer;

    public function __construct(
        Parser $parser,
        IndexGenerator $generator,
        ContextResolver $contextResolver,
        Completer $completer
    ){
        $this->parser = $parser;
        $this->generator = $generator;
        $this->contextResolver = $contextResolver;
        $this->completer = $completer;
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
            }
            catch(\Exception $e){}
            $entries = $this->findEntries($project, $completionLine, $column, $lines);
        }
        elseif(!empty($content)) {
            $this->updateFileIndex($project, $content, $file);
        }

        return [
            "entries" => $entries,
            "context" => []
        ];
    }
    protected function findEntries(Project $project, $badLine, $column, $lines){
        $context = $this->contextResolver->getContext($badLine, $column);
        return $this->completer->getEntries($project, $context);
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
        $nodes = $this->parser
            ->parseContent($fqcn, $file, $content);
        $this->generator->processFileNodes(
            $project->getIndex(),
            $fqcn,
            $nodes
        );
    }
}
