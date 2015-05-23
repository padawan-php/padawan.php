<?php

namespace Complete\Resolver;

use Entity\Completion\Token;
use Entity\Completion\Context;
use Entity\Completion\Scope;
use Entity\Index;
use Entity\FQCN;
use Parser\ErrorFreePhpParser;
use Parser\UseParser;
use Psr\Log\LoggerInterface;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;

class ContextResolver{
    public function __construct(
        ErrorFreePhpParser $parser,
        NodeTypeResolver $typeResolver,
        LoggerInterface $logger,
        UseParser $useParser
    ){
        $this->parser = $parser;
        $this->typeResolver = $typeResolver;
        $this->logger = $logger;
        $this->useParser = $useParser;
    }
    public function getContext($badLine, Index $index, Scope $scope = null){
        if(empty($badLine)){
            throw new \Exception("Could not define empty line context");
        }
        if(empty($scope)){
            $scope = new Scope;
        }

        $token = $this->getLastToken($badLine);
        $this->logger->addDebug(sprintf('Found token \'%s\' with type %s', $token->getSymbol(), $token->getType()));
        return $this->createContext($scope, $token, $badLine, $index);
    }

    /**
     * @return Token
     */
    protected function getLastToken($badLine){
        try {
            $symbols = @token_get_all($this->prepareLine($badLine));
        }
        catch(\Exception $e){
            $symbols = [0,0];
        }
        $token = null;
        array_shift($symbols);
        do {
            $token = $this->addSymbolForToken(array_pop($symbols), $token);
        } while(!$token->isReady() && count($symbols));
        return $token;
    }

    protected function createContext(Scope $scope, Token $token, $badLine, Index $index){
        $context = new Context($scope, $token);
        $nodes = $this->parser->parse($this->prepareLine($badLine));

        if($token->isObjectOperator() || $token->isStaticOperator()){
            if(is_array($nodes)){
                $workingNode = array_pop($nodes);
            }
            else {
                $workingNode = $nodes;
            }
            $isThis = false;
            if($workingNode instanceof Variable && $workingNode->name === 'this')
            {
                $isThis = true;
            }
            if(
                $workingNode instanceof Name
            )
            {
                $nodeFQCN = $this->useParser->getFQCN($workingNode);
                if(
                    $scope->getFQCN() instanceof FQCN
                    && $nodeFQCN->toString() === $scope->getFQCN()->toString()
                )
                {
                    $isThis = true;
                }
            }
            $context->setData([
                $this->typeResolver->getChainType($workingNode, $index, $scope),
                $isThis
            ]);
        }
        if($token->isUseOperator()
            || $token->isNamespaceOperator()
            || $token->isNewOperator()
        ){
            $context->setData(trim($token->getSymbol()));
        }

        return $context;
    }

    protected function addSymbolForToken($symbol, Token $token = null){
        if(is_array($symbol)){
            $code = $symbol[0];
            $symbol = $symbol[1];
        }
        else {
            $code = $symbol;
        }
        if(empty($token)){
            $token = new Token($code, $symbol);
        }
        else {
            $token->add($code, $symbol);
        }
        return $token;
    }

    protected function prepareLine($badLine){
        if(strpos($badLine, '<?php') === false
            || strpos($badLine, '<?') === false
        ){
            $badLine = '<?php ' . $badLine;
        }
        $badLine = str_replace(['elseif', 'else', 'catch'], '', $badLine);
        return $badLine;
    }

    private $logger;
    private $parser;
    private $typeResolver;
    private $useParser;
}
