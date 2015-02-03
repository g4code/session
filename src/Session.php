<?php

namespace G4\Session;


class Session
{

    const COUCHBASE = 'couchbase';
    const MEMCACHED = 'memcached';

    private $options;


    public function __construct($options)
    {
        $this->options = $options;
        $this->setSavePath();
    }

    public function start()
    {
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