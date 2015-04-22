<?php

use Command\GenerateCommand;
use Command\CompleteCommand;
use Command\ErrorCommand;

describe('Router', function(){
    beforeEach(function(){
        $this->router = new \Router;
    });
    describe('->getCommand()', function(){
        it('should return GenerateCommand when generate name is passed', function(){
            expect($this->router->getCommand('generate'))
                ->to->be->an->instanceof(GenerateCommand::class);
        });
        it('should return CompleteCommand when complete name is passed', function(){
            expect($this->router->getCommand('complete'))
                ->to->be->an->instanceof(CompleteCommand::class);
        });
        it('should return ErrorCommand when unknown name is passed', function(){
            expect($this->router->getCommand('someUknownName'))
                ->to->be->an->instanceof(ErrorCommand::class);
        });
    });
});
