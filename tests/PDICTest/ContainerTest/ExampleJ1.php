<?php

namespace PDICTest\ContainerTest;

class ExampleJ1 extends ExampleA
{

    /**
     * @var string
     */
    protected $string;

    public function __construct($string1, ExampleD $exampleD)
    {
        $this->string = $string1 . get_class($exampleD);
    }

    public function __toString()
    {
        return $this->string;
    }

}
