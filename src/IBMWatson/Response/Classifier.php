<?php

namespace IBMWatson\Response;

class Classifier
{
    private $name;
    private $classifierId;
    private $created;

    /**
     * classification constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->name         = $data['name'];
        $this->classifierId = $data['classifier_id'];
        $this->created      = $data['created'];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getCreated()
    {
        return $this->created;
    }

}
