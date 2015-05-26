<?php

namespace Kassko\DataMapper\ClassMetadata;

/**
 * @author kko
 */
class Method
{
	/**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $method;

    /**
     * @var array
     */
    public $args = [];

    public function __construct($class, $method, $args)
    {
        $this->class = $class; 
        $this->method = $method; 
        $this->args = $args;    
    }
}
