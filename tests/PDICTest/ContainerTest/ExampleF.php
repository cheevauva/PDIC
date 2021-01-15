<?php

namespace PDICTest\ContainerTest;

class ExampleF implements \PDIC\InterfaceMediator
{

    /**
     * @var ExampleA
     */
    public $exampleA;

    /**
     * @var ExampleB
     */
    public $exampleB;

    public function get()
    {
        $storage = new \SplObjectStorage;
        $storage->offsetSet($this->exampleA, 'a');
        $storage->offsetSet($this->exampleB, 'b');

        return $storage;
    }

}
