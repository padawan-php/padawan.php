<?php

namespace Padawan\Domain\Completer;

use Padawan\Domain\Project;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Completion\Entry;
use Padawan\Domain\Project\FQN;
use Psr\Log\LoggerInterface;

class ClassNameCompleter extends AbstractInCodeBodyCompleter
{
    public function getEntries(Project $project, Context $context) {
        $entries = [];
        $postfix = $this->getPostfix($context);
        $candidates = [];
        $fqcns = $context->getScope()->getUses()->searchByPrefix($postfix);
        $candidates = array_map(function(FQN $fqcn) {
            return $fqcn->toString();
        }, $fqcns);
        $scope = $context->getScope()->getNamespace();

        if ($postfix[0] === '\\') {
            if (strlen($postfix) <= 2) {
                // won't complete the root namespace
                return [];
            }
            $scope = null;
            $postfix = substr($postfix, 1);
        }
        $candidates = array_merge(
            $this->formatEntries($candidates, $postfix),
            $this->getByNamespacePrefix($project, $context, $scope, $postfix)
        );
        return $candidates;
    }

    private function getByNamespacePrefix(Project $project, Context $context, $scope, $prefix = '')
    {
        $entries = [];
        $index = $project->getIndex();
        $candidates = array_merge(
            array_keys($index->getClasses()),
            array_keys($index->getInterfaces())
        );

        if (is_null($scope)) {
            $keyword = $prefix;
        } else {
            $keyword = $scope->toString() . '\\' . $prefix;
        }
        $keyword = str_replace('\\\\', '\\', $keyword);
        if ($keyword[0] === '\\') {
            $keyword = substr($keyword, 1);
        }
        $candidates = array_filter($candidates, function($name) use ($keyword) {
            return strpos($name, $keyword) === 0;
        });

        if (is_null($scope)) {
            $search = '';
        } else {
            $search = $scope->toString() . '\\';
            $search = str_replace('\\\\', '\\', $search);
            if ($search[0] === '\\') {
                $search = substr($search, 1);
            }
        }
        foreach ($candidates as $fqcnString) {
            if (!empty($search)) {
                $menu = str_replace($search, '', $fqcnString);
            } else {
                $menu = $fqcnString;
            }
            $complete = str_replace($prefix, '', $menu);
            $this->logger->debug('entry', ['name' => $complete, 'fqcn' => $fqcnString]);
            $entries[] = new Entry(
                $complete, $scope, '', $menu
            );
        }
        return $entries;
    }

    private function formatEntries($candidates, $prefix)
    {
        $entries = [];
        foreach ($candidates as $name => $fqcnString) {
            $complete = str_replace($prefix, '', $name);
            $this->logger->debug('entry', ['name' => $complete, 'fqcn' => $fqcnString]);
            $entries[] = new Entry(
                $complete, '', '', $complete
            );
        }

        return $entries;
    }

    public function canHandle(Project $project, Context $context)
    {
        if ($context->isUse()) return false;

        $postfix = $this->getPostfix($context);
        return $context->isClassName()
            || (
                parent::canHandle($project, $context)
                && ($context->isString() || $context->isEmpty())
                && strlen($postfix) > 0
            );
    }

    private function getPostfix(Context $context)
    {
        if (is_string($context->getData())) {
            return trim($context->getData());
        }
        return "";
    }

    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }
}
