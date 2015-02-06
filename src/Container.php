<?php

namespace G4\Session;

class Container
{

    /**
     * @var \Zend\Session\Container
     */
    private $container;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->container = new \Zend\Session\Container($name);
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function offsetExists($key)
    {
        return $this->container->offsetExists($key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->container->offsetGet($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function offsetSet($key, $value)
    {
        return $this->container->offsetSet($key, $value);
    }

    /**
     * @param string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->container->offsetUnset($key);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->container->getArrayCopy();
    }

    /**
     * @return boolean
     */
    public function hasData()
    {
        $data = $this->container->getArrayCopy();
        return !empty($data);
    }

    /**
     * @param array $data
     * @return \G4\Session\Container
     */
    public function setData(array $data)
    {
        $this->container->exchangeArray($data);
        return $this;
    }
}