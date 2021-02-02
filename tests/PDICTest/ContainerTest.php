<?php

namespace PDICTest;

use PDICTest\ContainerTest\Example;
use PDICTest\ContainerTest\ExampleA;
use PDICTest\ContainerTest\ExampleB;
use PDICTest\ContainerTest\ExampleC;
use PDICTest\ContainerTest\ExampleC1;
use PDICTest\ContainerTest\ExampleC2;
use PDICTest\ContainerTest\ExampleC3;
use PDICTest\ContainerTest\ExampleD;
use PDICTest\ContainerTest\ExampleE;
use PDICTest\ContainerTest\ExampleF;
use PDICTest\ContainerTest\ExampleG;
use PDICTest\ContainerTest\ExampleH;
use PDICTest\ContainerTest\ExampleI;
use PDICTest\ContainerTest\ExampleJ;
use PDICTest\ContainerTest\ExampleK;
use PDICTest\ContainerTest\ExampleL;
use PDICTest\ContainerTest\ExampleL1;

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
            '?factoryA' => '*' . ExampleA::class,
            '?serviceA' => ExampleA::class,
            '?k' => '*' . ExampleK::class,
            '?example' => Example::class,
            '?f' => '~' . ExampleF::class,
            Example::class => [
                'a' => ExampleA::class,
                'b' => ExampleB::class,
                'e' => ExampleE::class,
                'f' => '?f',
                'f1' => '~*' . ExampleF::class,
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
            ExampleC1::class => [
                'c' => ExampleC::class,
            ],
            ExampleC2::class => [
                '^1' => 'main',
            ],
            ExampleC3::class => [
                '>setMain' => 'main',
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
            ExampleI::class => [
                '^1' => '@string',
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

        $this->assertNotEquals($example->f->test, $example->f1->test);

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
        $this->expectException(\Psr\Container\NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('class "string" not found');

        $this->getContainer()->get('string');
    }

    public function testNotFoundInjectionForProperty()
    {
        $this->expectException(\Psr\Container\ContainerExceptionInterface::class);
        $this->expectExceptionMessage('For class (PDICTest\ContainerTest\ExampleC1), property (c): For class (PDICTest\ContainerTest\ExampleC), property (a): property "a" not found');

        $this->getContainer()->get(ExampleC1::class);
    }

    public function testNotFoundInjectionForConstructor()
    {
        $this->expectException(\Psr\Container\ContainerExceptionInterface::class);
        $this->expectExceptionMessage('For class (PDICTest\ContainerTest\ExampleC2), constructor argument (1): class "main" not found');

        $this->getContainer()->get(ExampleC2::class);
    }

    public function testNotFoundInjectionForSetter()
    {
        $this->expectException(\Psr\Container\ContainerExceptionInterface::class);
        $this->expectExceptionMessage('For class (PDICTest\ContainerTest\ExampleC3), setter (setMain): setter "setMain" not found');

        $this->getContainer()->get(ExampleC3::class);
    }

    public function testNotFoundException()
    {
        $this->expectException(\Psr\Container\NotFoundExceptionInterface::class);
        $this->expectExceptionMessage('class "main" not found');

        $this->getContainer()->get('main');
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

    public function testPSR11container()
    {
        $pimple = new \Pimple\Container();
        $pdic = new \PDIC\Container([
            '?pimple' => '#pimple:pimple',
            '?pdic' => '#pimple:pdic',
            '?newFoo' => '#pimple:foo',
            '?bar' => '#pimple:bar',
            '?loopy' => ExampleL1::class,
            '?storage' => '#pimple:storage',
            ExampleL1::class => [
                'bar' => '?bar',
                'foo' => '?newFoo',
            ],
        ], [
            'pimple' => new \Pimple\Psr11\Container($pimple),
        ]);

        $pimple['foo'] = $pimple->factory(function () {
            $foo = new \stdClass();
            $foo->time = microtime(true);

            return $foo;
        });
        $pimple['bar'] = function () {
            $bar = new \stdClass();
            $bar->time = microtime(true);

            return $bar;
        };
        $pimple['pdic'] = function () use ($pdic) {
            return $pdic;
        };
        $pimple['pimple'] = function ($pimple) {
            return $pimple;
        };
        $pimple['storage'] = function ($pimple) {
            $storage = new \SplObjectStorage();
            $storage->attach($pimple['pdic']->get('loopy')->foo);
            $storage->attach($pimple['pdic']->get('loopy')->bar);

            return $storage;
        };

        $this->assertEquals($pimple, $pdic->get('pimple'));
        $this->assertEquals($pdic, $pdic->get('pdic'));
        $this->assertNotEquals($pdic->get('newFoo'), $pdic->get('newFoo'));
        $this->assertEquals($pdic->get('bar'), $pdic->get('bar'));
        $this->assertEquals($pdic->get('loopy')->bar, $pdic->get('bar'));
        $this->assertEquals($pdic->get('loopy')->foo, $pdic->get('loopy')->foo);
        $this->assertNotEquals($pdic->get('loopy')->foo, $pdic->get('newFoo'));

        /* @var $storage \SplObjectStorage */
        $storage = $pdic->get('storage');

        $this->assertTrue($storage->contains($pdic->get('loopy')->bar));
        $this->assertTrue($storage->contains($pdic->get('loopy')->foo));
    }

    public function testPSR11containerNotFound()
    {
        $this->expectException(\Psr\Container\ContainerExceptionInterface::class);
        $this->expectExceptionMessage('entry "pimple" not found');

        $pdic = new \PDIC\Container([
            '?pimple' => '#pimple:pimple'
        ]);
        $pdic->get('pimple');
    }

    public function testPSR11containerNotImplementInterface()
    {
        $this->expectException(\Psr\Container\ContainerExceptionInterface::class);
        $this->expectExceptionMessage('entry "pimple" not implemented PSR-11 interfface');

        $pimple = new \Pimple\Container();
        $pdic = new \PDIC\Container([
            '?pimple' => '#pimple:pimple',
        ], [
            'pimple' => $pimple,
        ]);
        $pdic->get('pimple');
    }

}
