<?php

namespace Cheevauva\Test\Container;

class ExampleC implements \Cheevauva\Contract\Container\UseServiceLocator
{

    /**
     * @var \Cheevauva\Container\ServiceLocator
     */
    protected $container;

    /**
     * @param \Cheevauva\Container\ServiceLocator $container
     */
    public function setContainer(\Cheevauva\Container\ServiceLocator $container)
    {
        $this->container = $container;
    }

    /**
     * @return \Cheevauva\Container\ServiceLocator
     */
    public function getContainer()
    {
        return $this->container;
    }

}
