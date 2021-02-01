<?php

namespace PDICTest\ContainerTest;

class ExampleI
{

    /**
     * @var string
     */
    protected $string;

    public function __construct($string)
    {
        $this->string = $string;
    }

    public function getString()
    {
        return $this->string;
    }

}
