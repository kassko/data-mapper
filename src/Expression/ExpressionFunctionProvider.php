<?php
namespace Kassko\DataMapper\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;

/**
* ExpressionFunctionProvider
*
* @author kko
*/
class ExpressionFunctionProvider 
{
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'service',
                function ($arg) {
                        return sprintf('$this->simpleMethodArgResolver->resolveService(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['arg_resolver']->resolveService($value);
                }
            ),
            new ExpressionFunction(
                'field',
                function ($arg) {
                    return sprintf('$this->simpleMethodArgResolver->resolveFieldValue(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['arg_resolver']->resolveFieldValue($value);
                }
            )
        ];
    }
}