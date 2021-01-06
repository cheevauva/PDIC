<?php

namespace PDICTest\ContainerTest;

class ExampleF extends ExampleA implements \PDIC\InterfaceMediator
{

    public function get()
    {
        $storage = new \SplObjectStorage;
        $storage->attach($this->exampleA);
        $storage->attach($this->exampleB);

        return $storage;
    }

}
