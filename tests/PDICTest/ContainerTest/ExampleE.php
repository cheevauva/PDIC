<?php

namespace PDICTest\ContainerTest;

class ExampleE extends ExampleA
{

    /**
     * @var ExampleD
     */
    public $exampleD;

    /**
     * @var \stdClass
     */
    public $std;

    /**
     * @var \Psr\Container\ContainerInterface
     */
    public $container;

}
