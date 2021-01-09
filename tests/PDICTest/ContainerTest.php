<?php

namespace PDICTest;

use \PDICTest\ContainerTest\{
    ExampleA,
    ExampleB,
    ExampleC,
    ExampleD,
    ExampleE,
    ExampleF,
    ExampleG
};

class ContainerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PDIC\Container 
     */
    protected $container;

    /**
     * @return \PDIC\Container
     */
    protected function getContainer()
    {
        $objects = [
            'stdClass' => new \stdClass,
        ];

        $this->container = new \PDIC\Container($this->getInjectionMap(), $objects);

        return $this->container;
    }

    protected function getInjectionMap()
    {
        return [
            ExampleA::class => [
                'exampleA' => ExampleA::class,
                'exampleB' => ExampleB::class,
            ],
            ExampleB::class => [
                'exampleA' => ExampleA::class,
            ],
            ExampleC::class => [
                'a' => ExampleA::class,
                'b' => ExampleB::class,
                'c' => ExampleC::class,
            ],
            ExampleD::class => [
                'exampleA' => ExampleA::class,
                'exampleB' => ExampleB::class,
            ],
            ExampleE::class => [
                'exampleD' => ExampleD::class,
                'std' => 'stdClass',
                'container' => 'PDIC\Container',
            ],
            ExampleG::class => [
                'exampleA' => '*' . ExampleA::class,
            ],
        ];
    }

    public function testRelatedInjections()
    {
        $container = $this->getContainer();

        /* @var $exampleA ExampleA */
        $exampleA = $container->get(ExampleA::class);

        $this->assertInstanceOf(ExampleA::class, $exampleA->exampleB->exampleA);
    }

    public function testMediatorComponent()
    {
        $container = $this->getContainer();

        /** @var ExampleD $exampleD */
        $exampleD = $container->get(ExampleD::class);

        $this->assertInstanceOf(ExampleA::class, $exampleD->getExampleAFromServiceLocator());
        $this->assertInstanceOf(ExampleB::class, $exampleD->getExampleBFromServiceLocator());
        $this->assertInstanceOf('PDIC\ServiceLocator', $exampleD->getServiceLocator());
    }

    public function testExtendsInjection()
    {
        $container = $this->getContainer();

        /** @var ExampleE $exampleE */
        $exampleE = $container->get(ExampleE::class);

        $this->assertInstanceOf(ExampleA::class, $exampleE->exampleA);
        $this->assertInstanceOf(ExampleB::class, $exampleE->exampleB);
        $this->assertInstanceOf(ExampleD::class, $exampleE->exampleD);
    }

    public function testServiceLocator()
    {
        $container = $this->getContainer();

        /* @var $exampleC \PDICTest\ContainerTest\ExampleC */
        $exampleC = $container->get(ExampleC::class);

        $this->assertInstanceOf(ExampleA::class, $exampleC->getContainer()->get('a'));
        $this->assertInstanceOf(ExampleB::class, $exampleC->getContainer()->get('b'));
        $this->assertInstanceOf(ExampleC::class, $exampleC->getContainer()->get('c'));

        $this->assertTrue($exampleC->getContainer()->has('a'));
        $this->assertFalse($exampleC->getContainer()->has('d'));
    }

    public function testMediator()
    {
        $container = $this->getContainer();

        /* @var $exampleF \SplObjectStorage */
        $exampleF = $container->get(ExampleF::class);

        $this->assertInstanceOf(\SplObjectStorage::class, $exampleF);

        $this->assertTrue($exampleF->contains($container->get(ExampleA::class)));
        $this->assertTrue($exampleF->contains($container->get(ExampleB::class)));
    }

    public function testCreateAsGetWithLocalPrefix()
    {
        $container = $this->getContainer();

        /* @var $exampleA1 \PDICTest\ContainerTest\ExampleA */
        $exampleA1 = $container->get('*' . ExampleA::class);
        $exampleA1->test = 1;

        /* @var $exampleA2 \PDICTest\ContainerTest\ExampleA */
        $exampleA2 = $container->get('*' . ExampleA::class);
        $exampleA2->test = 2;

        $this->assertNotEquals($exampleA1, $exampleA2);
    }

    public function testPresetObjects()
    {
        $container = $this->getContainer();

        /* @var $exampleE ExampleE */
        $exampleE = $container->get(ExampleE::class);

        $this->assertInstanceOf(\stdClass::class, $exampleE->std);
        $this->assertEquals($container, $exampleE->container);
    }

    public function testCreateInContainer()
    {
        $container = $this->getContainer();

        /* @var $exampleA \PDICTest\ContainerTest\ExampleA */
        $exampleA = $container->get(ExampleA::class);
        $exampleA->test = 1;

        /* @var $exampleG \PDICTest\ContainerTest\ExampleG */
        $exampleG = $container->get(ExampleG::class);
        $exampleG->exampleA->test = 2;

        $this->assertNotEquals($exampleA, $exampleG->exampleA);
    }

    public function testNotFoundException()
    {
        $container = $this->getContainer();

        $object = null;

        try {
            /* @var $exampleC \PDICTest\ContainerTest\ExampleC */
            $exampleC = $container->get(ExampleC::class);

            $object = $exampleC->getContainer()->get('d');
        } catch (\Exception $ex) {
            $this->assertInstanceOf(\Psr\Container\NotFoundExceptionInterface::class, $ex);
        }

        try {
            $object = $container->get('main');
        } catch (\Exception $ex) {
            $this->assertInstanceOf(\Psr\Container\NotFoundExceptionInterface::class, $ex);
        }
        
        $this->assertNull($object);
    }

}
