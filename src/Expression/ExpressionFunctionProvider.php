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
                    return sprintf('value_resolver.resolveClass(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['value_resolver']->resolveClass($value);
                }
            ),
            new ExpressionFunction(
                'field',
                function ($arg) {
                    return sprintf('value_resolver.resolveFieldValue(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['value_resolver']->resolveFieldValue($value);
                }
            ),
            new ExpressionFunction(
                'source',
                function ($arg) {
                    return sprintf('value_resolver.resolveSourceResult(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['value_resolver']->resolveSourceResult($value);
                }
            ),
            new ExpressionFunction(
                'var',
                function ($arg) {
                    return sprintf('value_resolver.resolveVariable(%s)', $arg);
                }, 
                function (array $context, $value) {
                    return $context['value_resolver']->resolveVariable($value);
                }
            ),
            new ExpressionFunction(
                'data',
                function () {
                    return 'value_resolver.resolveRawData()';
                }, 
                function (array $context) {
                    return $context['value_resolver']->resolveRawData();
                }
            ),
        ];
    }
}
