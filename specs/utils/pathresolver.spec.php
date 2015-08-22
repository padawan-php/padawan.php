<?php

use Utils\PathResolver;
use Phine\Path\Path;

describe('PathResolver', function () {
    describe('->relative()', function () {
        beforeEach(function () {
            $this->path = new PathResolver(new Path);
        });
        it('returns path from parent', function () {
            expect(
                $this->path->relative('/path/root', '/path/root/folder/project')
            )->to->be->equal('folder/project');
        });
        it('returns path from parent with ./', function () {
            expect(
                $this->path->relative('/path/root/', '/path/root/folder/project', true)
            )->to->be->equal('./folder/project');
        });
        it('returns path from sibling', function () {
            expect(
                $this->path->relative('/path/root/another/project/inner/', '/path/root/folder/project')
            )->to->be->equal('../../../folder/project');
        });
    });
});
