<?php

use Generator\FilesFinder;
use Utils\PathResolver;
use Entity\Project;
use Entity\Index;
use Prophecy\Argument;

describe('FilesFinder', function() {
    beforeEach(function() {
        $this->mock = $this->getProphet()->prophesize(PathResolver::class);
        $this->mock->getDirFilesRecursive(Argument::any())->willReturn([
            '/test/TestClass.php',
            '/some/AnotherFile.php',
            '/some/yaml/file.yml',
            '/composer.json',
            '/peridot.php'
        ]);
        $this->mock->relative(Argument::any(), Argument::any())->will(function($args) {
            return $args[1];
        });
        $this->files = new FilesFinder($this->mock->reveal());
        $this->project = new Project(new Index, "/project");
    });
    describe('->getProjectFiles()', function() {
        it('returns all php files from project', function() {
            expect($this->files->getProjectFiles($this->project))->to->be->equal([
                '/test/TestClass.php',
                '/some/AnotherFile.php',
                '/peridot.php'
            ]);
        });
    });
});
