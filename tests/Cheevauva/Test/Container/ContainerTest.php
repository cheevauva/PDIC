<?php

namespace Cheevauva\Test\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Cheevauva\Container 
     */
    protected $container;

    /**
     * @return \Cheevauva\Container
     */
    protected function getContainer()
    {
        if (!empty($this->container)) {
            return $this->container;
        }

        $this->container = new \Cheevauva\Container(array(
            'Cheevauva\Test\Container\ExampleA' => array(
                'exampleA' => 'Cheevauva\Test\Container\ExampleA',
                'exampleB' => 'Cheevauva\Test\Container\ExampleB',
            ),
            'Cheevauva\Test\Container\ExampleB' => array(
                'exampleA' => 'Cheevauva\Test\Container\ExampleA',
            ),
            'Cheevauva\Test\Container\ExampleC' => array(
                'exampleA' => 'Cheevauva\Test\Container\ExampleA',
                'exampleB' => 'Cheevauva\Test\Container\ExampleB',
                'exampleC' => 'Cheevauva\Test\Container\ExampleC',
            ),
            'stdClass' => array(
                'exampleA' => 'Cheevauva\Test\Container\ExampleA',
                'exampleB' => 'Cheevauva\Test\Container\ExampleB',
            ),
            'Cheevauva\Test\Container\ExampleE' => array(
                'exampleD' => 'Cheevauva\Test\Container\ExampleD',
            ),
        ));

        return $this->container;
    }

    public function testRelatedInjections()
    {
        $container = $this->getContainer();

        /* @var $exampleA \Cheevauva\Test\Container\ExampleA */
        $exampleA = $container->get('Cheevauva\Test\Container\ExampleA');

        $this->assertInstanceOf('Cheevauva\Test\Container\ExampleA', $exampleA->exampleB->exampleA);
    }

    public function testLogicComponent()
    {
        $object = new \stdClass;

        $container = $this->getContainer();
        $container->set('Cheevauva\Test\Container\ExampleD', $object);

        $this->assertInstanceOf('Cheevauva\Test\Container\ExampleA', $object->exampleA);
        $this->assertInstanceOf('Cheevauva\Test\Container\ExampleB', $object->exampleB);
        $this->assertInstanceOf('stdClass', $container->get('stdClass'));
        $this->assertInstanceOf('stdClass', $container->get('Cheevauva\Test\Container\ExampleD'));
    }

    public function testExtendsInjection()
    {
        $object = new \stdClass;

        $container = $this->getContainer();
        $container->set('Cheevauva\Test\Container\ExampleD', $object);

        $exampleE = $container->get('Cheevauva\Test\Container\ExampleE');

        $this->assertInstanceOf('Cheevauva\Test\Container\ExampleA', $exampleE->exampleA);
        $this->assertInstanceOf('Cheevauva\Test\Container\ExampleB', $exampleE->exampleB);
        $this->assertInstanceOf('stdClass', $exampleE->exampleD);
    }

    public function testServiceLocator()
    {
        $container = $this->getContainer();

        /* @var $exampleC \Cheevauva\Test\Container\ExampleC */
        $exampleC = $container->get('Cheevauva\Test\Container\ExampleC');

        $this->assertInstanceOf('Cheevauva\Test\Container\ExampleA', $exampleC->getContainer()->get('exampleA'));
        $this->assertInstanceOf('Cheevauva\Test\Container\ExampleB', $exampleC->getContainer()->get('exampleB'));
        $this->assertInstanceOf('Cheevauva\Test\Container\ExampleC', $exampleC->getContainer()->get('exampleC'));
    }

    public function testMediator()
    {
        $container = $this->getContainer();

        /* @var $exampleF \SplObjectStorage */
        $exampleF = $container->get('Cheevauva\Test\Container\ExampleF');
        
        $this->assertInstanceOf('SplObjectStorage', $exampleF);
        
        $this->assertTrue($exampleF->contains($container->get('Cheevauva\Test\Container\ExampleA')));
        $this->assertTrue($exampleF->contains($container->get('Cheevauva\Test\Container\ExampleB')));
    }

}
