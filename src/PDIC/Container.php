<?php

/**
 * @license LICENCE
 */

namespace PDIC;

class Container implements \Psr\Container\ContainerInterface
{

    const PREFIX_VARIABLE = '@';
    const PREFIX_FORCE = '!';
    const PREFIX_FACTORY = '*';
    const PREFIX_SERVICE = '=';
    const PREFIX_CONSTRUCTOR_INJECT = '^';
    const PREFIX_SETTER_INJECT = '>';
    const PREFIX_ALIAS = '?';
    const PREFIX_MEDIATOR = '~';

    /**
     * @var array
     */
    protected $entries = [];

    /**
     * @var array
     */
    protected $map = [];

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * @var Configuration 
     */
    protected $configuration;

    /**
     * @param array $map
     * @param array $entries
     */
    public function __construct(array $map, array $entries = [], Configuration $configuration = null)
    {
        $this->entries = $entries;
        $this->entries[get_class($this)] = $this;
        $this->configuration = is_null($configuration) ? new Configuration : $configuration;

        foreach ($map as $id => $def) {
            if ($this->configuration->isSupportAliases && $id[0] === static::PREFIX_ALIAS) {
                $this->aliases[substr($id, 1)] = $def;
            } else {
                $this->map[$id] = $def;
            }
        }
    }

    /**
     * @param string $id
     * @return object
     * @throws ExceptionNotFound
     * @throws Exception
     */
    public function get($id)
    {
        if ($this->configuration->isSupportAliases && isset($this->aliases[$id])) {
            $id = $this->aliases[$id];
        } else {
            $id = static::PREFIX_FACTORY . $id;
        }

        return $this->fetch($id);
    }

    /**
     * @param string $id
     * @return object
     * @throws ExceptionNotFound
     * @throws Exception
     */
    protected function fetch($id)
    {
        if (empty($id)) {
            throw new Exception('id must be defined');
        }

        if ($this->configuration->isSupportAliases && $id[0] === static::PREFIX_ALIAS) {
            return $this->get(substr($id, 1));
        }

        $isMediator = $id[0] === static::PREFIX_MEDIATOR;

        if ($isMediator) {
            if (!$this->configuration->isSupportMediator) {
                throw new Exception('I am not allowed to do this, because isSupportMediator defined as false');
            }

            $id = substr($id, 1);
        }

        $isFactory = $id[0] === static::PREFIX_FACTORY;
        $isVariable = $id[0] === static::PREFIX_VARIABLE;
        $isService = !$isFactory && !$isVariable;

        if ($isVariable || $isFactory) {
            $id = substr($id, 1);
        }

        if (($isService || $isVariable) && isset($this->entries[$id])) {
            return $this->entries[$id];
        }

        if ($isVariable) {
            throw new ExceptionNotFound(sprintf('variable "%s" not found', $id));
        }

        if (!class_exists($id, true)) {
            throw new ExceptionNotFound(sprintf('class "%s" not found', $id));
        }

        if (!$this->configuration->isSupportInherit) {
            $properties = isset($this->map[$id]) ? $this->map[$id] : [];
        } else {
            $properties = $this->getPropertiesByClass($id);
        }

        $constructorProperties = [];

        if ($this->configuration->isSupportInjectionToConstructor) {
            foreach ($properties as $property => $entryId) {
                if ($property[0] == static::PREFIX_CONSTRUCTOR_INJECT) {
                    $constructorProperties[substr($property, 1)] = $this->fetch($entryId);
                    unset($properties[$property]);
                }
            }

            if (!empty($constructorProperties)) {
                ksort($constructorProperties, SORT_NUMERIC);
            }
        }

        $entry = new $id(...$constructorProperties);

        if ($isService) {
            $this->entries[$id] = $entry;
        }

        if ($this->configuration->isSupportInjectionToSetter) {
            foreach ($properties as $property => $entryId) {
                if ($property[0] === static::PREFIX_SETTER_INJECT) {
                    $entry->{substr($property, 1)}($this->fetch($entryId));
                    unset($properties[$property]);
                }
            }
        }

        if ($this->configuration->isSupportInjectionToProperty) {
            $this->setPropertiesToObject($entry, $properties);
        }

        if ($isMediator) {
            $entry = $entry();

            if ($isService) {
                $this->entries[$id] = $entry;
            }
        }

        return $entry;
    }

    /**
     * @param string $class
     * @return array
     */
    protected function getPropertiesByClass($class)
    {
        $classes = [];

        if ($this->configuration->isSupportInheritTraits) {
            $classes += $this->getTraits($class);
        }

        if ($this->configuration->isSupportInheritInterfaces) {
            $classes += class_implements($class);
        }

        if ($this->configuration->isSupportInheritParents) {
            $classes += class_parents($class);
        }

        $classes[] = $class;

        $properties = [];

        foreach ($classes as $class) {
            if (empty($this->map[$class])) {
                continue;
            }

            foreach ($this->map[$class] as $key => $value) {
                $properties[$key] = $value;
            }
        }

        return $properties;
    }

    /**
     * @param object $object
     * @param array $properties
     * @throws Exception
     */
    protected function setPropertiesToObject($object, array $properties)
    {
        try {
            foreach ($properties as $property => $entryId) {
                $isForce = $property[0] === static::PREFIX_FORCE;

                if (!$isForce) {
                    if ($this->configuration->isCheckPropertyExists && !property_exists($object, $property)) {
                        throw new \ReflectionException(sprintf('%s: Property %s not found', get_class($object), $property));
                    }

                    $object->{$property} = $this->fetch($entryId);
                } else {
                    if (!$this->configuration->isSupportForcedInjactionToProperty) {
                        throw new Exception('I am not allowed to do this, because isSupportForcedInjactionToProperty defined as false');
                    }

                    $property = substr($property, 1);

                    if (empty($reflecitonClass)) {
                        $reflecitonClass = new \ReflectionClass($object);
                    }

                    $reflectionProperty = $reflecitonClass->getProperty($property);

                    if ($reflectionProperty->isPublic()) {
                        $reflectionProperty->setValue($object, $this->fetch($entryId));
                    } else {
                        $reflectionProperty->setAccessible(true);
                        $reflectionProperty->setValue($object, $this->fetch($entryId));
                        $reflectionProperty->setAccessible(false);
                    }
                }
            }
        } catch (Exception $e) {
            $message = 'For class (' . get_class($object) . '), property (' . $property . '): ';
            $message .= $e->getMessage();

            throw new Exception($message);
        }
    }

    /**
     * @param string $class
     * @return array
     */
    protected function getTraits($class)
    {
        $traits = [];

        do {
            $traits += class_uses($class);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits += $this->getTraits($trait);
        }

        return array_unique($traits);
    }

    public function has($id)
    {
        return isset($this->entries[$id]);
    }

}
