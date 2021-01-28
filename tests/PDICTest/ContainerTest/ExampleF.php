<?php

namespace PDICTest\ContainerTest;

class ExampleF
{

    /**
     * @var ExampleA
     */
    public $exampleA;

    /**
     * @var ExampleB
     */
    public $exampleB;

    public function __invoke()
    {
        $storage = new \SplObjectStorage;
        $storage->offsetSet($this->exampleA, 'a');
        $storage->offsetSet($this->exampleB, 'b');

        return $storage;
    }

}
