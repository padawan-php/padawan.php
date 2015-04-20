<?php

namespace Complete\Resolver;

use Entity\Project;
use Entity\Completion\Scope;
use Parser\ScopeParser;

class ScopeResolver {
    public function __construct(ScopeParser $parser){
        $this->parser = $parser;
    }
    public function findScope(Project $project, $content, $line, $file){
        $scope = new Scope;
        $index = $project->getIndex();
        $fqcn = $index->findFQCNByFile($file);
        if(empty($fqcn)){
            return $scope;
        }
        $scope->setFQCN($fqcn);
        $scope = $this->parser->parseContent($project, $scope, $content, $line);
        return $scope;
    }

    private $parser;
}
