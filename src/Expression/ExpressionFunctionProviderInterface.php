<?php

namespace Kassko\DataMapper\Expression;

/**
* This interface allows compatibility with symfony/expression-language < 2.6 
*
* @see https://github.com/symfony/expression-language
* @see https://github.com/symfony/expression-language/blob/e5a515aa0738c1af3013127b747072124ff3da0b/ExpressionFunctionProviderInterface.php
*/
interface ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[] An array of Function instances
     */
    public function getFunctions();
}