<?php

/**
 * @license LICENCE
 */

namespace PDIC;

class Container implements \Psr\Container\ContainerInterface
{

    const PREFIX_PARAMETER = '@';
    const PREFIX_FACTORY = '*';
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
        $isVariable = $id[0] === static::PREFIX_PARAMETER;
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
            $map = isset($this->map[$id]) ? $this->map[$id] : [];
        } else {
            $map = $this->getPropertiesByClass($id);
        }

        $constructorArguments = [];
        $setters = [];
        $properties = [];

        foreach ($map as $target => $entryId) {
            switch ($target[0]) {
                case static::PREFIX_CONSTRUCTOR_INJECT:
                    $constructorArguments[substr($target, 1)] = $entryId;
                    break;
                case static::PREFIX_SETTER_INJECT:
                    $setters[substr($target, 1)] = $entryId;
                    break;
                default:
                    $properties[$target] = $entryId;
            }
        }

        if (!empty($constructorArguments)) {
            ksort($constructorArguments, SORT_NUMERIC);
        }

        try {
            $injection = 'constructor argument';

            foreach ($constructorArguments as $target => $entryId) {
                if (!$this->configuration->isSupportInjectionToConstructor && !empty($constructorArguments)) {
                    throw new Exception('I am not allowed to do this, because isSupportInjectionToConstructor defined as false');
                }

                $constructorArguments[$target] = $this->fetch($entryId);
            }

            $entry = new $id(...$constructorArguments);

            if ($isService) {
                $this->entries[$id] = $entry;
            }

            $injection = 'setter';

            foreach ($setters as $target => $entryId) {
                if (!$this->configuration->isSupportInjectionToSetter) {
                    throw new Exception('I am not allowed to do this, because isSupportInjectionToSetter defined as false');
                }

                if ($this->configuration->isCheckSetterExists && !method_exists($entry, $target)) {
                    throw new Exception(sprintf('setter "%s" not found', $target));
                }

                $entry->{$target}($this->fetch($entryId));
            }

            $injection = 'property';


            foreach ($properties as $target => $entryId) {
                if (!$this->configuration->isSupportInjectionToProperty) {
                    throw new Exception('I am not allowed to do this, because isSupportInjectionToProperty defined as false');
                }

                if ($this->configuration->isCheckPropertyExists && !property_exists($entry, $target)) {
                    throw new Exception(sprintf('property "%s" not found', $target));
                }

                $entry->{$target} = $this->fetch($entryId);
            }
        } catch (Exception $e) {
            $class = (empty($entry) ? $id : get_class($entry));
            $message = sprintf('For class (%s), %s (%s): ', $class, $injection, $target);
            $message .= $e->getMessage();

            throw new Exception($message);
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
