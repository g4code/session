<?php

namespace G4\Session;


class Session
{

    const COUCHBASE = 'couchbase';
    const MEMCACHED = 'memcached';

    private $container;

    /**
     * @var \Zend\Session\SessionManager
     */
    private $manager;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $domainName;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->domainName = null;
        $this->options    = $options;
        $this->setSavePath();
    }

    /**
     * @return void
     */
    public function destroy()
    {
        $this->manager->destroy([
            'send_expire_cookie' => true,
            'clear_storage'      => true,
        ]);
        $params = session_get_cookie_params();
        setcookie(session_name(), '', 0, $params['path'], $params['domain'], $params['secure'], isset($params['httponly']));
    }

    /**
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get($key, $defaultValue = null)
    {
        return $this->container->offsetExists($key)
            ? $this->container->offsetGet($key)
            : ($defaultValue === null ? null : $defaultValue);
    }

    /**
     * @return string
     */
    public function getDomainName()
    {
        return $this->domainName === null
            ? $_SERVER['HTTP_HOST']
            : $this->domainName;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->container->offsetExists($key);
    }

    /**
     * @param string $key
     * @return void
     */
    public function remove($key)
    {
        $this->container->offsetUnset($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value)
    {
        $this->container->offsetSet($key, $value);
    }

    /**
     * @param string $domainName
     * @return \G4\Session\Session
     */
    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
        return $this;
    }

    /**
     * @return \G4\Session\Session
     */
    public function start()
    {
        session_set_cookie_params($this->getLifetime(), '/', $this->getDomainName());

        $this->manager = new \Zend\Session\SessionManager($this->getConfig());
        $this->manager
            ->setSaveHandler($this->getSaveHandler())
            ->setStorage(new \Zend\Session\Storage\SessionArrayStorage())
            ->start();
        \Zend\Session\Container::setDefaultManager($this->manager);

        $this->container = new Container(__CLASS__);

        return $this;
    }

    /**
     * @return \Zend\Session\Config\StandardConfig
     */
    private function getConfig()
    {
        $config = new \Zend\Session\Config\StandardConfig();
        $config->setOptions([
            'save_path' => $this->options['save_path'],
        ]);
        return $config;
    }

    /**
     * @return int
     */
    private function getLifetime()
    {
        return isset($this->options['adapter']['options']['lifetime'])
            ? $this->options['adapter']['options']['lifetime']
            : 0;
    }

    /**
     * @return mixed
     */
    private function getSaveHandler()
    {
        return $this->getOptions()['adapter']['name'] === self::COUCHBASE
            ? new \G4\Session\SaveHandler\Couchbase($this->getOptions()['adapter']['options'])
            : new \Zend\Session\SaveHandler\Cache(\Zend\Cache\StorageFactory::factory($this->getOptions()));
    }

    // @todo: Drasko: write our own memcached adapter!
    /**
     * @return array:
     */
    private function getOptions()
    {
        if ($this->options['adapter']['name'] === self::MEMCACHED) {
            unset(
                $this->options['adapter']['options']['host'],
                $this->options['adapter']['options']['port'],
                $this->options['adapter']['options']['bucket'],
                $this->options['adapter']['options']['lifetime'],
                $this->options['adapter']['options']['persistent']
            );
        }
        return $this->options;
    }

    /**
     * @return \Zend\Cache\Storage\StorageInterface
     */
    private function getStorage()
    {
        return \Zend\Cache\StorageFactory::factory($this->getOptions());
    }

    /**
     * @return void
     */
    private function setSavePath()
    {
        if (!empty($this->options['save_path'])) {
            session_save_path($this->options['save_path']);
            ini_set('session.save_path', $this->options['save_path']);
        }
    }
}