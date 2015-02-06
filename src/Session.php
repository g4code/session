<?php

namespace G4\Session;


class Session
{

    const COUCHBASE = 'couchbase';
    const MEMCACHED = 'memcached';

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $domainName;


    public function __construct(array $options)
    {
        $this->domainName = null;
        $this->options    = $options;
        $this->setSavePath();
    }

    public function getDomainName()
    {
        return $this->domainName === null
            ? $_SERVER['HTTP_HOST']
            : $this->domainName;
    }

    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
        return $this;
    }

    public function start()
    {
        session_set_cookie_params($this->getLifetime(), '/', $this->getDomainName());

        $manager = new \Zend\Session\SessionManager($this->getConfig());
        $manager
            ->setSaveHandler($this->getSaveHandler())
            ->setStorage(new \Zend\Session\Storage\SessionArrayStorage())
            ->start();
        \Zend\Session\Container::setDefaultManager($manager);
    }

    private function getConfig()
    {
        $config = new \Zend\Session\Config\StandardConfig();
        $config->setOptions([
            'save_path' => $this->options['save_path'],
        ]);
        return $config;
    }

    private function getLifetime()
    {
        return isset($this->options['adapter']['options']['lifetime'])
            ? $this->options['adapter']['options']['lifetime']
            : 0;
    }

    private function getSaveHandler()
    {
        return $this->getOptions()['adapter']['name'] === self::COUCHBASE
            ? new \G4\Session\SaveHandler\Couchbase($this->getOptions()['adapter']['options'])
            : new \Zend\Session\SaveHandler\Cache(\Zend\Cache\StorageFactory::factory($this->getOptions()));
    }

    // @todo: Drasko: write our own memcached adapter!
    private function getOptions()
    {
        if ($this->options['adapter']['name'] === self::MEMCACHED) {
            unset(
                $this->options['adapter']['options']['host'],
                $this->options['adapter']['options']['port'],
                $this->options['adapter']['options']['bucket'],
                $this->options['adapter']['options']['lifetime']);
        }
        return $this->options;
    }

    private function getStorage()
    {
        return \Zend\Cache\StorageFactory::factory($this->getOptions());
    }

    private function setSavePath()
    {
        if (!empty($this->options['save_path'])) {
            session_save_path($this->options['save_path']);
            ini_set('session.save_path', $this->options['save_path']);
        }
    }
}