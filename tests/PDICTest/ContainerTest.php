<?php

namespace PDICTest;

use \PDICTest\ContainerTest\{
    ExampleA,
    ExampleB,
    ExampleC,
    ExampleD,
    ExampleE,
    ExampleF,
    ExampleG,
    ExampleH,
    ExampleI,
    ExampleJ,
    ExampleK
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
            'string' => 'abc',
        ];

        $this->container = new \PDIC\Container($this->getInjectionMap(), $objects);

        return $this->container;
    }

    protected function getInjectionMap()
    {
        return [
            '?factoryA' => '*' . ExampleA::class,
            '?serviceA' => ExampleA::class,
            '?k' => '*' . ExampleK::class,
            ExampleA::class => [
                'exampleA' => ExampleA::class,
                'exampleB' => ExampleB::class,
            ],
            ExampleB::class => [
                'exampleA' => ExampleA::class,
            ],
            ExampleC::class => [
                'a' => ExampleA::class,
            ],
            ExampleF::class => [
                'exampleA' => ExampleA::class,
                'exampleB' => ExampleB::class,
            ],
            ExampleD::class => [
                'exampleA' => ExampleA::class,
                'exampleB' => ExampleB::class,
            ],
            ExampleE::class => [
                'exampleD' => ExampleD::class,
                'std' => \stdClass::class,
                'container' => \PDIC\Container::class,
            ],
            ExampleG::class => [
                'exampleA' => '*' . ExampleA::class,
            ],
            ExampleH::class => [
                '!a' => ExampleA::class,
            ],
            ExampleI::class => [
                '!string' => '@string',
            ],
            ExampleJ::class => [
                '^2' => ExampleD::class,
                '^1' => '@string',
            ],
            ExampleK::class => [
                'a' => '?factoryA',
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

    public function testExtendsInjection()
    {
        $container = $this->getContainer();

        /** @var ExampleE $exampleE */
        $exampleE = $container->get(ExampleE::class);

        $this->assertInstanceOf(ExampleA::class, $exampleE->exampleA);
        $this->assertInstanceOf(ExampleB::class, $exampleE->exampleB);
        $this->assertInstanceOf(ExampleD::class, $exampleE->exampleD);
    }

    public function testMediator()
    {
        $container = $this->getContainer();

        /* @var $storage \SplObjectStorage */
        $storage = $container->get(ExampleF::class);

        /* @var $exampleA ExampleA */
        $exampleA = $container->get(ExampleA::class);

        $this->assertInstanceOf(\SplObjectStorage::class, $storage);
        $this->assertTrue($storage->contains($exampleA->exampleA));
        $this->assertTrue($storage->contains($exampleA->exampleB));

        $this->assertFalse($storage->contains($container->get(ExampleA::class)));
        $this->assertFalse($storage->contains($container->get(ExampleB::class)));
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

        /* @var $exampleA ExampleA */
        $exampleA = $container->get(ExampleA::class);
        $exampleA->test = 1;

        /* @var $exampleG ExampleG */
        $exampleG = $container->get(ExampleG::class);
        $exampleG->exampleA->test = 2;

        $this->assertNotEquals($exampleA, $exampleG->exampleA);
    }

    public function testForseInjection()
    {
        $container = $this->getContainer();

        /* @var $exampleH ExampleH */
        $exampleH = $container->get(ExampleH::class);

        $this->assertInstanceOf(ExampleA::class, $exampleH->getA());
    }

    public function testConstructorInjection()
    {
        $container = $this->getContainer();

        /* @var $exampleJ ExampleJ */
        $exampleJ = $container->get(ExampleJ::class);

        $this->assertInstanceOf(ExampleA::class, $exampleJ->exampleA);
        $this->assertInstanceOf(ExampleB::class, $exampleJ->exampleB);
        $this->assertTrue((string) $exampleJ === 'abc' . ExampleD::class);
    }

    public function testVariableInjection()
    {
        $container = $this->getContainer();

        /* @var $exampleI ExampleI */
        $exampleI = $container->get(ExampleI::class);

        $this->assertTrue($exampleI->getString() === "abc");
    }

    public function testGetVariableFromContainerException()
    {
        $string = null;

        try {
            $string = $this->getContainer()->get('string');
        } catch (\Exception $ex) {
            $this->assertTrue($ex->getMessage() === 'class "string" not found');
            $this->assertInstanceOf(\Psr\Container\NotFoundExceptionInterface::class, $ex);
        }

        $this->assertNull($string);
    }

    public function testNotFoundPropertyException()
    {
        $container = $this->getContainer();

        $object = null;

        try {
            $object = $container->get(ExampleC::class);
        } catch (\Exception $ex) {
            $this->assertTrue($ex->getMessage() === "PDICTest\ContainerTest\ExampleC: Property a not found");
            $this->assertInstanceOf(\ReflectionException::class, $ex);
        }

        $this->assertNull($object);
    }

    public function testNotFoundException()
    {
        $container = $this->getContainer();

        $object = null;

        try {
            $object = $container->get('main');
        } catch (\Exception $ex) {
            $this->assertInstanceOf(\Psr\Container\NotFoundExceptionInterface::class, $ex);
        }

        $this->assertNull($object);
    }

    public function testAliases()
    {
        $container = $this->getContainer();
        /* @var $exampleA2 ExampleA */
        $exampleA2 = $container->get('serviceA');
        $exampleA2->mustBeNotEmpty = true;

        /* @var $exampleA3 ExampleA */
        $exampleA3 = $container->get('serviceA');

        $this->assertNotEquals(ExampleA::class, $exampleA2);
        $this->assertTrue(!empty($exampleA3->mustBeNotEmpty));

        /* @var $exampleA ExampleA */
        $exampleA = $container->get('factoryA');
        $exampleA->mustBeEmpty = 1;

        /* @var $exampleA1 ExampleA */
        $exampleA1 = $container->get('factoryA');

        $this->assertNotEquals(ExampleA::class, $exampleA);
        $this->assertTrue(empty($exampleA1->mustBeEmpty));

        /* @var $exampleK ExampleK */
        $container->get('k')->a->mustBeEmpty = 1;
        $this->assertTrue(empty($container->get('k')->a->mustBeEmpty));
    }

}
