<?php

namespace PDICTest;

use \PDICTest\ContainerTest\{
    Example,
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
    ExampleK,
    ExampleL
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
        $stdClass = new \stdClass;
        $stdClass->isPreseted = true;

        $objects = [
            'stdClass' => $stdClass,
            'string' => 'abc',
        ];

        $this->container = new \PDIC\Container($this->getInjectionMap(), $objects);

        return $this->container;
    }

    protected function getInjectionMap()
    {
        return [
            '?aliasForString' => '@string',
            '?' . ExampleM::class => ExampleM::class,
            '?' . ExampleM1::class => ExampleM::class,
            '?factoryA' => '*' . ExampleA::class,
            '?serviceA' => ExampleA::class,
            '?k' => '*' . ExampleK::class,
            '?example' => Example::class,
            Example::class => [
                'a' => ExampleA::class,
                'b' => ExampleB::class,
                'e' => ExampleE::class,
                'f' => '~' . ExampleF::class,
                'g' => ExampleG::class,
                'h' => ExampleH::class,
                'i' => ExampleI::class,
                'j' => ExampleJ::class,
                'l' => ExampleL::class,
            ],
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
            ExampleL::class => [
                '>setA' => ExampleA::class,
                '^1' => '@string',
            ]
        ];
    }

    public function testDeepInjections()
    {
        /* @var $example Example */
        $example = $this->getContainer()->get('example');

        $this->assertInstanceOf(ExampleA::class, $example->a->exampleB->exampleA);
        $this->assertEquals($example->a, $example->a->exampleB->exampleA);
    }

    public function testExtendsInjection()
    {
        /** @var Example $example */
        $example = $this->getContainer()->get('example');

        $this->assertInstanceOf(ExampleA::class, $example->e->exampleA);
        $this->assertInstanceOf(ExampleB::class, $example->e->exampleB);
        $this->assertInstanceOf(ExampleD::class, $example->e->exampleD);
    }

    public function testMediator()
    {
        $container = $this->getContainer();

        /** @var Example $example */
        $example = $container->get('example');

        /* @var $storage \SplObjectStorage */
        $storage = $example->f;

        $this->assertInstanceOf(\SplObjectStorage::class, $storage);
        $this->assertTrue($storage->contains($example->a));
        $this->assertTrue($storage->contains($example->b));

        $this->assertFalse($storage->contains($container->get(ExampleA::class)));
        $this->assertFalse($storage->contains($container->get(ExampleB::class)));
    }

    public function testPresetObjects()
    {
        $container = $this->getContainer();

        /** @var Example $example */
        $example = $container->get('example');

        $this->assertTrue($example->e->std->isPreseted);
        $this->assertEquals($container, $example->e->container);
        $this->assertTrue(empty($container->get(\stdClass::class)->isPreseted));
    }

    public function testCreateInContainer()
    {
        /** @var Example $example */
        $example = $this->getContainer()->get('example');

        $exampleA = $example->a;
        $exampleA->test = 1;

        $exampleG = $example->g;
        $exampleG->exampleA->test = 2;

        $this->assertNotEquals($exampleA, $exampleG->exampleA);
    }

    public function testForseInjection()
    {
        /** @var Example $example */
        $example = $this->getContainer()->get('example');

        $this->assertInstanceOf(ExampleA::class, $example->h->getA());
    }

    public function testConstructorInjection()
    {
        /** @var Example $example */
        $example = $this->getContainer()->get('example');

        $this->assertInstanceOf(ExampleA::class, $example->j->exampleA);
        $this->assertInstanceOf(ExampleB::class, $example->j->exampleB);
        $this->assertTrue((string) $example->j === 'abc' . ExampleD::class);
    }

    public function testVariableInjection()
    {
        /** @var Example $example */
        $example = $this->getContainer()->get('example');

        $this->assertTrue($example->i->getString() === "abc");
    }

    public function testGetVariableFromContainer()
    {
        $this->assertEquals($this->getContainer()->get('aliasForString'), 'abc');
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

        /* @var $a2 ExampleA */
        $a2 = $container->get('serviceA');
        $a2->mustBeNotEmpty = true;

        /* @var $a3 ExampleA */
        $a3 = $container->get('serviceA');

        $this->assertEquals($a2, $a3);
        $this->assertTrue(!empty($a3->mustBeNotEmpty));

        /* @var $a ExampleA */
        $a = $container->get('factoryA');
        $a->mustBeEmpty = 1;

        $this->assertNotEquals($a2, $a);
        $this->assertNotEquals($a3, $a);

        /* @var $a1 ExampleA */
        $a1 = $container->get('factoryA');

        $this->assertNotEquals($a1, $a);
        $this->assertTrue(empty($a1->mustBeEmpty));

        /* @var $k ExampleK */
        $k = $container->get('k');
        $k->a->mustBeEmpty = 1;

        /* @var $k1 ExampleK */
        $k1 = $container->get('k');

        $this->assertNotEquals($k->a, $k1->a);
        $this->assertTrue(empty($k1->a->mustBeEmpty));
    }

    public function testSetter()
    {
        /** @var Example $example */
        $example = $this->getContainer()->get('example');

        $this->assertInstanceOf(ExampleL::class, $example->l);
        $this->assertInstanceOf(ExampleA::class, $example->l->getA());
    }

    public function testObjectInvariance()
    {
        $container = new \PDIC\Container([
            '?' . ExampleA::class => ExampleA::class,
            '?' . ExampleB::class => ExampleA::class,
        ]);

        $before = $container->get(ExampleA::class);
        $after = $container->get(ExampleB::class);

        $this->assertEquals($before, $after);

        $before->exampleA = false;

        $after = $container->get(ExampleB::class);

        $this->assertEquals($before, $after);
    }

}
