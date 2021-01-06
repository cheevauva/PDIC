<?php

/**
 * @license LICENCE
 */

namespace PDIC;

class Container implements InterfaceContainer
{

    /**
     * @var array
     */
    protected $objects;

    /**
     * @var array
     */
    protected $injections = [];

    /**
     * @param array $injections
     * @param array $objects
     */
    public function __construct(array $injections, array $objects = [])
    {
        $this->injections = $injections;
        $this->objects = $objects;
        $this->objects[get_class($this)] = $this;
    }

    /**
     * @param string $className
     * @return object
     * @throws ExceptionNotFound
     * @throws Exception
     * @throws RuntimeException
     */
    public function create($className)
    {
        return $this->get(static::LOCAL_PREFIX . $className);
    }

    /**
     * @param string $id
     * @return object
     * @throws ExceptionNotFound
     * @throws Exception
     * @throws RuntimeException
     */
    public function get($id)
    {
        if (empty($id)) {
            throw new RuntimeException('id must be defined');
        }

        $isLocal = $id[0] === static::LOCAL_PREFIX;
        $isGlobal = !$isLocal;

        if ($isGlobal) {
            if (isset($this->objects[$id])) {
                return $this->objects[$id];
            }
        } else {
            $id = substr($id, 1);
        }

        if (!class_exists($id, true)) {
            throw new ExceptionNotFound($id);
        }

        $object = new $id;

        if ($isGlobal) {
            $this->objects[$id] = $object;
        }

        $properties = $this->getPropertiesByClass($id);

        if ($object instanceof InterfaceUsePDICServiceLocator) {
            $object->setPDICServiceLocatory(new ServiceLocator($properties, $this));
        } else {
            $this->setPropertiesToObject($object, $properties);
        }

        if ($object instanceof InterfaceMediator) {
            $object = $object->get();
        }

        if ($isGlobal) {
            $this->objects[$id] = $object;
        }

        return $object;
    }

    /**
     * @param string $class
     * @return array
     */
    protected function getPropertiesByClass($class)
    {
        $classes = $this->getTraits($class);
        $classes += class_parents($class);
        $classes[] = $class;

        $properties = [];

        foreach ($classes as $class) {
            if (empty($this->injections[$class])) {
                continue;
            }

            foreach ($this->injections[$class] as $key => $value) {
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
            foreach ($properties as $property => $class) {
                $object->{$property} = $this->get($class);
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
        return isset($this->objects[$id]);
    }

}
