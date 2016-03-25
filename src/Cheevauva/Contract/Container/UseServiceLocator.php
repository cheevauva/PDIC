<?php

namespace Cheevauva\Contract\Container;

interface UseServiceLocator
{

    /**
     * @param \Cheevauva\Container\ServiceLocator $container
     */
    public function setContainer(\Cheevauva\Container\ServiceLocator $container);
}
