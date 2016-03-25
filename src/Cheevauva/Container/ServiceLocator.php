<?php

namespace Cheevauva\Container;

class ServiceLocator
{

    /**
     * @var \Cheevauva\Contact\Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * 
     * @param array $metadata
     * @param \Cheevauva\Contact\Container $container
     */
    public function __construct(array $metadata, $container)
    {
        $this->metadata = $metadata;
        $this->container = $container;
    }

    public function get($path)
    {
        return $this->container->get($this->metadata[$path]);
    }

}
