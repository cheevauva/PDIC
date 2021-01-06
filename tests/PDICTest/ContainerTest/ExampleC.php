<?php

namespace PDICTest\ContainerTest;

class ExampleC implements \PDIC\InterfaceUsePDICServiceLocator
{

    /**
     * @var \PDIC\ServiceLocator
     */
    protected $container;

    /**
     * @return \PDIC\ServiceLocator
     */
    public function getContainer()
    {
        return $this->container;
    }

    public function setPDICServiceLocatory(\PDIC\ServiceLocator $serviceLocator)
    {
        $this->container = $serviceLocator;
    }

}
