<?php

namespace Kassko\DataMapper\Expression;

use Kassko\DataMapper\Expression\ExpressionFunction;
use Kassko\DataMapper\Expression\ExpressionFunctionProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
* This class allows compatibility with symfony/expression-language < 2.6. 
*
* Visits ExpressionLanguage to enhance it with providers.
*
* @see https://github.com/symfony/expression-language
* @see https://github.com/symfony/expression-language/blob/e5a515aa0738c1af3013127b747072124ff3da0b/ExpressionLanguage.php
*
* @author kko
*/
class ExpressionLanguageConfigurator
{
    private $providers = [];

    public function __construct(array $providers = array())
    {
        $this->providers = $providers;  
    }

    public function addProvider(ExpressionFunctionProviderInterface $provider)
    {
        $this->providers[] = $provider;
    }

    public function configure(ExpressionLanguage $expressionLanguage)
    {
        foreach ($this->providers as $provider) {
            $this->registerProvider($expressionLanguage, $provider);
        }
    }

    private function registerProvider(ExpressionLanguage $expressionLanguage, ExpressionFunctionProviderInterface $provider)
    {
        foreach ($provider->getFunctions() as $function) {
            $this->addFunction($expressionLanguage, $function);
        }
    }

    private function addFunction(ExpressionLanguage $expressionLanguage, ExpressionFunction $function)
    {
        $expressionLanguage->register($function->getName(), $function->getCompiler(), $function->getEvaluator());
    }
}
