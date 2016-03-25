<?php

namespace Cheevauva;

class Container
{

    /**
     * @var array
     */
    protected $objects;

    /**
     * @var \Cheevauva\Contract\Config
     */
    protected $config;

    /**
     * @var array
     */
    protected $injections = array();

    public function __construct(array $injections)
    {
        $this->injections = $injections;
    }

    /**
     * @param string $className
     * @return object
     */
    public function get($className)
    {
        if (!$className) {
            throw new \Exception('Container: path is required');
        }

        if (isset($this->objects[$className])) {
            return $this->objects[$className];
        }

        if (!class_exists($className, true)) {
            throw new \Exception(sprintf('class %s not found', $className));
        }

        $object = new $className;

        $this->set($className, $object);

        return $this->get($className);
    }

    protected function getProperties($path)
    {
        if (!class_exists($path)) {
            return array();
        }

        $classes = $this->classUsesDeep($path);
        $classes += class_parents($path);

        $paths = $classes;
        $paths[] = $path;

        $properties = array();

        foreach ($paths as $parentPath) {
            if (empty($this->injections[$parentPath])) {
                continue;
            }

            foreach ($this->injections[$parentPath] as $key => $value) {
                $properties[$key] = $value;
            }
        }

        return $properties;
    }

    /**
     * set public properties to object
     * @param string $path
     * @param object $object
     * @throws Exception
     */
    protected function handleObject($object)
    {
        $properties = $this->getProperties(get_class($object));

        if ($object instanceof \Cheevauva\Contract\Container\UseServiceLocator) {
            $object->setContainer(new \Cheevauva\Container\ServiceLocator($properties, $this));
            return;
        }

        try {
            foreach ($properties as $property => $className) {
                $object->{$property} = $this->get($className);
            }
        } catch (Exception $e) {
            $message = 'For class (' . get_class($object) . '), property (' . $property . '): ';
            $message .= $e->getMessage();

            throw new Exception($message);
        }
    }

    public function set($path, $object)
    {
        $this->objects[$path] = $object;
        $this->handleObject($object);

        if ($object instanceof \Cheevauva\Contract\Container\Mediator) {
            $this->objects[$path] = $object->get();
        }
    }

    /**
     * @param string $class
     * @return array
     */
    protected function classUsesDeep($class)
    {
        $traits = array();

        do {
            $traits += class_uses($class);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits += $this->classUsesDeep($trait);
        }

        return array_unique($traits);
    }

}
