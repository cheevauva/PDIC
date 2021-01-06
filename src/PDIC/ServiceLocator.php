<?php

/**
 * @license LICENCE
 */

namespace PDIC;

use Psr\Container\ContainerInterface;

class ServiceLocator
{

    /**
     * @var \Cheevauva\Contact\Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $properties;

    public function __construct(array $properties, ContainerInterface $container)
    {
        $this->properties = $properties;
        $this->container = $container;
    }

    public function get($path)
    {
        return $this->container->get($this->properties[$path]);
    }

}
