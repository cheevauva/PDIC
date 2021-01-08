<?php

/**
 * @license LICENCE
 */

namespace PDIC;

use Psr\Container\ContainerInterface;

class ServiceLocator implements ContainerInterface
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

    public function get($id)
    {
        if (empty($this->properties[$id])) {
            throw new ExceptionNotFound(sprintf('dependency "%s" not found', $id));
        }

        return $this->container->get($this->properties[$id]);
    }

    public function has($id)
    {
        return isset($this->properties[$id]);
    }

}
