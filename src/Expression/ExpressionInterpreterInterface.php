<?php
namespace Kassko\DataMapper;

/**
* ExpressionInterpreterInterface
*
* @author kko
*/
interface ExpressionInterpreterInterface
{
    public function support($expression);
    public function process();
}