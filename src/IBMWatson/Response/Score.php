<?php

namespace IBMWatson\Response;

class Score
{
    /**
     * @var string
     */
    private $classifierId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var float
     */
    private $match;

    /**
     * Score constructor.
     *
     * @param string $classifierId
     * @param string $name
     * @param float  $match
     */
    public function __construct($classifierId, $name, $match)
    {
        $this->classifierId = $classifierId;
        $this->name         = $name;
        $this->match        = (float)$match * 100;
    }

    /**
     * @return string
     */
    public function getClassifierId()
    {
        return $this->classifierId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return float
     */
    public function getMatch()
    {
        return $this->match;
    }
}
