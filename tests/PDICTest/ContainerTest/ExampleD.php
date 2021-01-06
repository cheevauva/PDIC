<?php

namespace PDICTest\ContainerTest;

class ExampleD implements \PDIC\InterfaceUsePDICServiceLocator
{

    /**
     * @var \PDIC\ServiceLocator 
     */
    protected $serviceLocatior;

    public function setPDICServiceLocatory(\PDIC\ServiceLocator $serviceLocator)
    {
        $this->serviceLocatior = $serviceLocator;
    }

    public function getExampleAFromServiceLocator()
    {
        return $this->serviceLocatior->get('exampleA');
    }

    public function getExampleBFromServiceLocator()
    {
        return $this->serviceLocatior->get('exampleB');
    }

    public function getServiceLocator()
    {
        return $this->serviceLocatior;
    }

}
