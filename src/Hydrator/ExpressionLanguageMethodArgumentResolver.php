<?php
namespace Kassko\DataMapper;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
* ExpressionLanguageMethodArgumentResolver
*
* @author kko
*/
class ExpressionLanguageMethodArgumentResolver implements ExpressionFunctionProviderInterface 
{
    private $expressionLanguage;
    private $simpleMethodArgResolver;

    public function __construct(ExpressionLanguage $expressionLanguage, MethodArgumentResolver $simpleMethodArgResolver)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->simpleMethodArgResolver = $simpleMethodArgResolver;
    }

    public function handle($expression)
    {
        $expressionDetected = preg_match('/expr\((\w+)\)/', $expression, $matches);

        if (1 !== $expressionDetected) {
            throw new UnexpectedExpressionException($expression);
        }

        return $this->expressionLanguage->evaluate($matches[0]);
    }

    public function provideExpressionFunctions()
    {
        return [
            new ExpressionFunction(
                'service',
                function ($arg) {
                        return sprintf('$this->simpleMethodArgResolver->resolveService(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $this->simpleMethodArgResolver->resolveService($value);
                }
            ),
            new ExpressionFunction(
                'field',
                function ($arg) {
                    return sprintf('$this->simpleMethodArgResolver->resolveFieldValue(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $this->simpleMethodArgResolver->resolveFieldValue($value);
                }
            )
        ];
    }
}
