<?php

namespace G4\Session;

use Laminas\Session\Container as LaminasContainer;

class Container
{

    /**
     * @var LaminasContainer
     */
    private $container;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->container = new LaminasContainer($name);
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function offsetExists($key): bool
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
    public function offsetSet($key, $value): void
    {
        $this->container->offsetSet($key, $value);
    }

    /**
     * @param string $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        $this->container->offsetUnset($key);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->container->getArrayCopy();
    }

    /**
     * @return boolean
     */
    public function hasData(): bool
    {
        $data = $this->container->getArrayCopy();
        return !empty($data);
    }

    /**
     * @param array $data
     * @return Container
     */
    public function setData(array $data): Container
    {
        $this->container->exchangeArray($data);
        return $this;
    }
}
