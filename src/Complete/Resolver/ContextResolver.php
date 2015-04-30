<?php

namespace Complete\Resolver;

use Entity\Completion\Token;
use Entity\Completion\Context;
use Entity\Completion\Scope;
use Entity\Index;
use Parser\ErrorFreePhpParser;
use Psr\Log\LoggerInterface;

class ContextResolver{
    public function __construct(
        ErrorFreePhpParser $parser,
        NodeTypeResolver $typeResolver,
        LoggerInterface $logger
    ){
        $this->parser = $parser;
        $this->typeResolver = $typeResolver;
        $this->logger = $logger;
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
        $symbols = token_get_all($this->prepareLine($badLine));
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
            $context->setData(
                $this->typeResolver->getChainType($workingNode, $index, $scope)
            );
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
        return $badLine;
    }

    private $logger;
    private $parser;
    private $typeResolver;
}
