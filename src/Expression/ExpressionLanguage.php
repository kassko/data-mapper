<?php

namespace Kassko\DataMapper\Expression;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage as BaseExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ParserCache\ParserCacheInterface;

/**
 * Adds some function to the default ExpressionLanguage.
 *
 * @author kko
 *
 * @see ExpressionFunctionProvider
 */
class ExpressionLanguage extends BaseExpressionLanguage
{
    public function __construct(ParserCacheInterface $cache = null, array $providers = array())
    {
        // prepend the default provider to let users override it easily
        array_unshift($providers, new ExpressionFunctionProvider());

        parent::__construct($cache, $providers);
    }
}
