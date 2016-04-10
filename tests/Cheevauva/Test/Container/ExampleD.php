<?php

namespace Cheevauva\Test\Container;

class ExampleD implements \Cheevauva\Contract\Container\Mediator, \Cheevauva\Contract\Container\UseServiceLocator
{

    /**
     * @var \Cheevauva\Container\ServiceLocator 
     */
    protected $container;

    public function get()
    {
        return $this->container;
    }

    public function setContainer(\Cheevauva\Container\ServiceLocator $container)
    {
        $this->container = $container;
    }

}
