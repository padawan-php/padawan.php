<?php

namespace Plugin;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Complete\Resolver\NodeTypeResolver;
use Complete\Resolver\TypeResolveEvent;
use Entity\FQCN;
use PhpParser\Node\Arg;
use PhpParser\Node\Scalar\String_;
use Parser\UseParser;

class Test
{
    public function __construct(
        EventDispatcher $dispatcher,
        UseParser $useParser
    ) {
        $this->dispatcher = $dispatcher;
        $this->useParser = $useParser;
    }

    public function load()
    {
        if ($this->isLoaded) {
            return;
        }
        $this->isLoaded = true;
        $plugin = $this;
        $this->dispatcher->addListener(
            NodeTypeResolver::BLOCK_START,
            function (TypeResolveEvent $e) use ($plugin) {
                $plugin->parentType = $e->getType();
            }
        );
        $this->dispatcher->addListener(
            NodeTypeResolver::BLOCK_END,
            function (TypeResolveEvent $e) use ($plugin) {
                $parentType = $plugin->parentType;
                if ($parentType instanceof FQCN
                    && $parentType->toString() === 'DI\\Container'
                ) {
                    /** @var \Entity\Chain\MethodCall */
                    $chain = $e->getChain();
                    if ($chain->getType() === 'method' && count($chain->getArgs()) > 0) {
                        $firstArg = array_pop($chain->getArgs())->value;
                        if ($firstArg instanceof String_) {
                            $className = $firstArg->value;
                            $fqcn = $plugin->useParser->parseFQCN($className);
                            $e->setType($fqcn);
                        }
                    }
                }
            }
        );
    }

    private $parentType;
    /** @var EventDispatcher */
    private $dispatcher;
    private $useParser;
    private $isLoaded = false;
}
