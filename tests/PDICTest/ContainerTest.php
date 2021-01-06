<?php

namespace PDICTest;

class ContainerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PDIC\Container 
     */
    protected $container;

    /**
     * @var array
     */
    protected $injectionsMap = [
        'PDICTest\ContainerTest\ExampleA' => [
            'exampleA' => 'PDICTest\ContainerTest\ExampleA',
            'exampleB' => 'PDICTest\ContainerTest\ExampleB',
        ],
        'PDICTest\ContainerTest\ExampleB' => [
            'exampleA' => 'PDICTest\ContainerTest\ExampleA',
        ],
        'PDICTest\ContainerTest\ExampleC' => [
            'exampleA' => 'PDICTest\ContainerTest\ExampleA',
            'exampleB' => 'PDICTest\ContainerTest\ExampleB',
            'exampleC' => 'PDICTest\ContainerTest\ExampleC',
        ],
        'PDICTest\ContainerTest\ExampleD' => [
            'exampleA' => 'PDICTest\ContainerTest\ExampleA',
            'exampleB' => 'PDICTest\ContainerTest\ExampleB',
        ],
        'PDICTest\ContainerTest\ExampleE' => [
            'exampleD' => 'PDICTest\ContainerTest\ExampleD',
            'std' => 'stdClass',
            'container' => 'PDIC\Container',
        ],
    ];

    /**
     * @return \PDIC\Container
     */
    protected function getContainer()
    {
        $objects = [
            'stdClass' => new \stdClass,
        ];
        
        $this->container = new \PDIC\Container($this->injectionsMap, $objects);

        return $this->container;
    }

    public function testRelatedInjections()
    {
        $container = $this->getContainer();

        /* @var $exampleA \PDICTest\ContainerTest\ExampleA */
        $exampleA = $container->get('PDICTest\ContainerTest\ExampleA');

        $this->assertInstanceOf('PDICTest\ContainerTest\ExampleA', $exampleA->exampleB->exampleA);
    }

    public function testMediatorComponent()
    {
        $container = $this->getContainer();
        /** @var ContainerTest\ExampleD $exampleD */
        $exampleD = $container->get('PDICTest\ContainerTest\ExampleD');

        $this->assertInstanceOf('PDICTest\ContainerTest\ExampleA', $exampleD->getExampleAFromServiceLocator());
        $this->assertInstanceOf('PDICTest\ContainerTest\ExampleB', $exampleD->getExampleBFromServiceLocator());
        $this->assertInstanceOf('PDIC\ServiceLocator', $exampleD->getServiceLocator());
    }

    public function testExtendsInjection()
    {
        $container = $this->getContainer();

        $exampleE = $container->get('PDICTest\ContainerTest\ExampleE');

        $this->assertInstanceOf('PDICTest\ContainerTest\ExampleA', $exampleE->exampleA);
        $this->assertInstanceOf('PDICTest\ContainerTest\ExampleB', $exampleE->exampleB);
        $this->assertInstanceOf('PDICTest\ContainerTest\ExampleD', $exampleE->exampleD);
    }

    public function testServiceLocator()
    {
        $container = $this->getContainer();

        /* @var $exampleC \PDICTest\ContainerTest\ExampleC */
        $exampleC = $container->get('PDICTest\ContainerTest\ExampleC');

        $this->assertInstanceOf('PDICTest\ContainerTest\ExampleA', $exampleC->getContainer()->get('exampleA'));
        $this->assertInstanceOf('PDICTest\ContainerTest\ExampleB', $exampleC->getContainer()->get('exampleB'));
        $this->assertInstanceOf('PDICTest\ContainerTest\ExampleC', $exampleC->getContainer()->get('exampleC'));
    }

    public function testMediator()
    {
        $container = $this->getContainer();

        /* @var $exampleF \SplObjectStorage */
        $exampleF = $container->get('PDICTest\ContainerTest\ExampleF');

        $this->assertInstanceOf('SplObjectStorage', $exampleF);

        $this->assertTrue($exampleF->contains($container->get('PDICTest\ContainerTest\ExampleA')));
        $this->assertTrue($exampleF->contains($container->get('PDICTest\ContainerTest\ExampleB')));
    }

    public function testCreate()
    {
        $container = $this->getContainer();

        /* @var $exampleA1 \PDICTest\ContainerTest\ExampleA */
        $exampleA1 = $container->create('PDICTest\ContainerTest\ExampleA');
        $exampleA1->test = 1;

        /* @var $exampleA1 \PDICTest\ContainerTest\ExampleA */
        $exampleA2 = $container->create('PDICTest\ContainerTest\ExampleA');
        $exampleA2->test = 2;

        $this->assertNotEquals($exampleA1, $exampleA2);
    }

    public function testCreateAsGetWithLocalPrefix()
    {
        $container = $this->getContainer();

        /* @var $exampleA1 \PDICTest\ContainerTest\ExampleA */
        $exampleA1 = $container->get('*PDICTest\ContainerTest\ExampleA');
        $exampleA1->test = 1;

        /* @var $exampleA1 \PDICTest\ContainerTest\ExampleA */
        $exampleA2 = $container->get('*PDICTest\ContainerTest\ExampleA');
        $exampleA2->test = 2;

        $this->assertNotEquals($exampleA1, $exampleA2);
    }
    
    public function testPresetObjects()
    {
        $container = $this->getContainer();

        /* @var $exampleE \PDICTest\ContainerTest\ExampleE */
        $exampleE= $container->get('PDICTest\ContainerTest\ExampleE');
        
        $this->assertInstanceOf('stdClass', $exampleE->std);
        $this->assertEquals($container, $exampleE->container);
    }
}
