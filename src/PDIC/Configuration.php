<?php

namespace PDIC;

class Configuration
{

    /**
     * @var bool
     */
    public $isSupportInjectionToConstructor = true;

    /**
     * @var bool
     */
    public $isSupportInjectionToProperty = true;

    /**
     * @var bool
     */
    public $isSupportInjectionToSetter = false;

    /**
     * @var bool
     */
    public $isSupportForcedInjactionToProperty = true;

    /**
     * @var bool
     */
    public $isSupportInheritTraits = true;

    /**
     * @var bool
     */
    public $isSupportInheritInterfaces = true;

    /**
     * @var bool
     */
    public $isSupportInheritParents = true;

    /**
     * @var bool
     */
    public $isSupportInherit = true;

    /**
     * @var bool
     */
    public $isSupportMediator = true;

    /**
     * @var bool
     */
    public $isCheckPropertyExists = true;

}
