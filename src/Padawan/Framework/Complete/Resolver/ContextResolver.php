<?php

namespace Padawan\Framework\Complete\Resolver;

use Padawan\Domain\Completion\Token;
use Padawan\Domain\Completion\Context;
use Padawan\Domain\Scope;
use Padawan\Domain\Scope\FileScope;
use Padawan\Domain\Project\FQN;
use Padawan\Domain\Project\Index;
use Padawan\Domain\Project\FQCN;
use Padawan\Parser\ErrorFreePhpParser;
use Padawan\Parser\UseParser;
use Psr\Log\LoggerInterface;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;

class ContextResolver
{
    public function __construct(
        ErrorFreePhpParser $parser,
        NodeTypeResolver $typeResolver,
        LoggerInterface $logger,
        UseParser $useParser
    ) {
        $this->parser = $parser;
        $this->typeResolver = $typeResolver;
        $this->logger = $logger;
        $this->useParser = $useParser;
    }
    public function getContext($badLine, Index $index, Scope $scope = null)
    {
        if (empty($scope)) {
            $scope = new FileScope(new FQN);
        }

        $token = $this->getLastToken($badLine);
        $this->logger->debug(sprintf(
            'Found token \'%s\' with type %s',
            $token->getSymbol(),
            $token->getType()
        ));
        return $this->createContext($scope, $token, $badLine, $index);
    }

    /**
     * @return Token
     */
    protected function getLastToken($badLine)
    {
        try {
            $symbols = @token_get_all($this->prepareLine($badLine, false));
        } catch (\Exception $e) {
            $symbols = [0, 0];
        }
        $token = null;
        array_shift($symbols);
        do {
            $token = $this->addSymbolForToken(array_pop($symbols), $token);
        } while (!$token->isReady() && count($symbols));
        return $token;
    }

    protected function createContext(Scope $scope, Token $token, $badLine, Index $index)
    {
        $context = new Context($scope, $token);
        $nodes = $this->parser->parse($this->prepareLine($badLine));

        if ($token->isObjectOperator() || $token->isStaticOperator() || $token->isMethodCall()) {
            if (is_array($nodes)) {
                $workingNode = array_pop($nodes);
            } else {
                $workingNode = $nodes;
            }
            $types = $this->typeResolver->getChainType($workingNode, $index, $scope);
            $workingNodeType = array_pop($types);
            $isThis = $scope->getFQCN() instanceof FQCN
                && $workingNodeType instanceof FQCN
                && $workingNodeType->toString() === $scope->getFQCN()->toString();
            $context->setData([
                $workingNodeType,
                $isThis,
                $types,
                $workingNode
            ]);
        }
        if ($token->isUseOperator()
            || $token->isNamespaceOperator()
            || $token->isNewOperator()
        ) {
            $context->setData(trim($token->getSymbol()));
        }

        return $context;
    }

    protected function addSymbolForToken($symbol, Token $token = null)
    {
        if (is_array($symbol)) {
            $code = $symbol[0];
            $symbol = $symbol[1];
        } else {
            $code = $symbol;
        }
        if (empty($token)) {
            $token = new Token($code, $symbol);
        } else {
            $token->add($code, $symbol);
        }
        return $token;
    }

    protected function prepareLine($badLine, $wrapFunctionCall = true)
    {
        if (strpos($badLine, '<?php') === false
            || strpos($badLine, '<?') === false
        ) {
            $badLine = '<?php ' . $badLine;
        }
        $badLine = str_replace(['elseif', 'else', 'catch'], '', $badLine);
        if ($wrapFunctionCall && $badLine[strlen($badLine) - 1] === '(') {
            $badLine .= ')';
        }
        return $badLine;
    }

    private $logger;
    private $parser;
    private $typeResolver;
    private $useParser;
}
