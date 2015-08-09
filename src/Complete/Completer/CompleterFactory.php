<?php

namespace Complete\Completer;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Entity\Completion\Context;
use Entity\Completion\Scope;
use Entity\Project;

class CompleterFactory {
    public function __construct(
        ClassNameCompleter $classNameCompleter,
        InterfaceNameCompleter $interfaceNameCompleter,
        NamespaceCompleter $namespaceCompleter,
        ObjectCompleter $objectCompleter,
        StaticCompleter $staticCompleter,
        UseCompleter $useCompleter,
        EventDispatcher $dispatcher
    ) {
        $this->classNameCompleter = $classNameCompleter;
        $this->interfaceNameCompleter = $interfaceNameCompleter;
        $this->namespaceCompleter = $namespaceCompleter;
        $this->objectCompleter = $objectCompleter;
        $this->staticCompleter = $staticCompleter;
        $this->useCompleter = $useCompleter;
        $this->dispatcher = $dispatcher;
    }
    public function getCompleter(Context $context)
    {
        $event = new CustomCompleterEvent($context);
        if ($context->isNamespace()) {
            return $this->namespaceCompleter;
        } elseif ($context->isUse()) {
            return $this->useCompleter;
        } elseif ($context->isClassName()) {
            return $this->classNameCompleter;
        } elseif ($context->isInterfaceName()) {
            return $this->interfaceNameCompleter;
        } elseif ($context->isThis() || $context->isObject()) {
            return $this->objectCompleter;
        } elseif ($context->isClassStatic()) {
            return $this->staticCompleter;
        } else {
            $this->dispatcher->dispatch(self::CUSTOM_COMPLETER, $event);
        }
        return $event->completer;
    }

    private $classNameCompleter;
    private $interfaceNameCompleter;
    private $namespaceCompleter;
    private $objectCompleter;
    private $staticCompleter;
    private $useCompleter;
    private $dispatcher;

    const CUSTOM_COMPLETER = 'completer.custom';
}
