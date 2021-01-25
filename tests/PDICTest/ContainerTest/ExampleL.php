<?php

namespace PDICTest\ContainerTest;

class ExampleL
{

    /**
     * @var ExampleA 
     */
    protected $a;

    public function getA()
    {
        return $this->a;
    }

    public function setA(ExampleA $a)
    {
        $this->a = $a;
    }

}
