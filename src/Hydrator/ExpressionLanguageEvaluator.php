<?php
namespace Kassko\DataMapper\Hydrator;

use Kassko\DataMapper\Expression\ExpressionContext;
use Kassko\DataMapper\Hydrator\Exception\NotResolvableValueException;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
* ExpressionLanguageEvaluator
*
* @author kko
*/
class ExpressionLanguageEvaluator
{
    /**
     * An expression language client.
     * @var ExpressionLanguage
     */
    private $expressionLanguage;

    /**
     * @var array Contains all the context variables.
     */
    private $expressionContext;

    public function __construct(ExpressionLanguage $expressionLanguage, ExpressionContext $expressionContext)
    {
        $this->expressionLanguage = $expressionLanguage;
        $this->expressionContext = $expressionContext;
    }

    public function handle($arg)
    {
        $expressionDetected = preg_match('/expr\((.+)\)/', $arg, $matches);

        if (1 !== $expressionDetected) {
            throw new NotResolvableValueException($arg);
        }

        return $this->expressionLanguage->evaluate($matches[1], $this->expressionContext->getData());
    }
}
