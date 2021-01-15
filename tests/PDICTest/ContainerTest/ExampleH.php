<?php

namespace PDICTest\ContainerTest;

class ExampleH
{
    /**
     * @var ExampleA
     */
    protected $a;

    public function getA()
    {
        return $this->a;
    }
}
