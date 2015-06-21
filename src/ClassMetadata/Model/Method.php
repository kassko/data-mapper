<?php

namespace Kassko\DataMapper\ClassMetadata\Model;

/**
 * @author kko
 */
class Method
{
	/**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $function;

    /**
     * @var array
     */
    private $args = [];

    public function __construct($class = null, $function = null, array $args = [])
    {
        $this->class = $class; 
        $this->function = $function; 
        $this->args = $args;    
    }

    public function isEquals(Method $method)
    {
        return $this->class === $method->class && $this->function === $method->function;
    }

    /**
     * Gets the value of class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Sets the value of class.
     *
     * @param string $class the class
     *
     * @return self
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Gets the value of function.
     *
     * @return string
     */
    public function getFunction()
    {
        return $this->function;
    }

    /**
     * Sets the value of function.
     *
     * @param string $function the function
     *
     * @return self
     */
    public function setFunction($function)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Gets the value of args.
     *
     * @return array
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * Sets the value of args.
     *
     * @param array $args the args
     *
     * @return self
     */
    public function setArgs(array $args)
    {
        $this->args = $args;

        return $this;
    }
}
