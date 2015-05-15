<?php

namespace Kassko\DataMapper\Expression;

use Kassko\DataMapper\Expression\ExpressionFunction;

/**
* ExpressionFunctionProvider
*
* @author kko
*/
class ExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'class',
                function ($arg) {
                    return sprintf('arg_resolver.resolveClass(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['arg_resolver']->resolveClass($value);
                }
            ),
            new ExpressionFunction(
                'field',
                function ($arg) {
                    return sprintf('arg_resolver.resolveFieldValue(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['arg_resolver']->resolveFieldValue($value);
                }
            ),
            new ExpressionFunction(
                'source',
                function ($arg) {
                    return sprintf('arg_resolver.resolveSourceResult(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['arg_resolver']->resolveSourceResult($value);
                }
            ),
        ];
    }
}
