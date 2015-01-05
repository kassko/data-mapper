<?php

namespace Solfa\Bundle\ScalesBundle\Scales;

class Color
{
    private $red;
    private $green;
    private $blue;
    private $sad;
    private $dark;
    private $behaviour;

    /**
     * Gets the value of red.
     *
     * @return mixed
     */
    public function getRed()
    {
        return $this->red;
    }

    /**
     * Sets the value of red.
     *
     * @param mixed $red the red
     *
     * @return self
     */
    public function setRed($red)
    {
        $this->red = $red;

        return $this;
    }

    /**
     * Gets the value of green.
     *
     * @return mixed
     */
    public function getGreen()
    {
        return $this->green;
    }

    /**
     * Sets the value of green.
     *
     * @param mixed $green the green
     *
     * @return self
     */
    public function setGreen($green)
    {
        $this->green = $green;

        return $this;
    }

    /**
     * Gets the value of blue.
     *
     * @return mixed
     */
    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * Sets the value of blue.
     *
     * @param mixed $blue the blue
     *
     * @return self
     */
    public function setBlue($blue)
    {
        $this->blue = $blue;

        return $this;
    }

    /**
     * Gets the value of sad.
     *
     * @return mixed
     */
    public function getSad()
    {
        return $this->sad;
    }

    /**
     * Sets the value of sad.
     *
     * @param mixed $sad the sad
     *
     * @return self
     */
    public function setSad($sad)
    {
        $this->sad = $sad;

        return $this;
    }

    /**
     * Gets the value of dark.
     *
     * @return mixed
     */
    public function getDark()
    {
        return $this->dark;
    }

    /**
     * Sets the value of dark.
     *
     * @param mixed $dark the dark
     *
     * @return self
     */
    public function setDark($dark)
    {
        $this->dark = $dark;

        return $this;
    }

    /**
     * Gets the value of behaviour.
     *
     * @return mixed
     */
    public function getBehaviour()
    {
        return $this->behaviour;
    }

    /**
     * Sets the value of behaviour.
     *
     * @param mixed $behaviour the behaviour
     *
     * @return self
     */
    public function setBehaviour(Behaviour $behaviour)
    {
        $this->behaviour = $behaviour;

        return $this;
    }
}
