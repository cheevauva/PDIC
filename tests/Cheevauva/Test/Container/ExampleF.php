<?php

namespace Cheevauva\Test\Container;

class ExampleF extends ExampleA implements \Cheevauva\Contract\Container\Mediator
{

    public function get()
    {
        $storage = new \SplObjectStorage;
        $storage->attach($this->exampleA);
        $storage->attach($this->exampleB);

        return $storage;
    }

}
