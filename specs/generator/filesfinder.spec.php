<?php

use Prophecy\Argument;
use Padawan\Domain\Project;
use Padawan\Domain\Project\Index;
use Padawan\Framework\Utils\PathResolver;
use Padawan\Framework\Generator\FilesFinder;
use Padawan\Framework\Domain\Project\InMemoryIndex;

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
        $this->project = new Project(new InMemoryIndex, "/project");
    });
    describe('->findProjectFiles()', function() {
        it('returns all php files from project', function() {
            expect($this->files->findProjectFiles($this->project))->to->be->equal([
                '/some/AnotherFile.php',
                '/peridot.php'
            ]);
        });
    });
});
