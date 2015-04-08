<?php
namespace Kassko\DataMapper\Hydrator;

use Kassko\DataMapper\Hydrator\Exception\UnexpectedMethodArgumentException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
* ExpressionLanguageMethodArgumentResolver
*
* @author kko
*/
class ExpressionLanguageMethodArgumentResolver implements MethodArgumentResolverInterface
{
    private $expressionLanguage;
    private $simpleMethodArgResolver;

    public function __construct(ExpressionLanguage $expressionLanguage, MethodArgumentResolverInterface $simpleMethodArgResolver)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->simpleMethodArgResolver = $simpleMethodArgResolver;
    }

    public function handle($arg, $object)
    {
        $expressionDetected = preg_match('/expr\((.+)\)/', $arg, $matches);

        if (1 !== $expressionDetected) {
            throw new UnexpectedMethodArgumentException($arg);
        }

        return $this->expressionLanguage->evaluate(
            $matches[1], 
            [
                'arg_resolver' => $this->simpleMethodArgResolver,
                'this' => $object
            ]
        );
    }
}
