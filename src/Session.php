<?php

namespace G4\Session;

use G4\Session\Exception\MissingDomainNameException;
use G4\Session\SaveHandler\Couchbase;
use Laminas\Cache\Storage\StorageInterface;
use Laminas\Cache\StorageFactory;
use Laminas\Session\Config\StandardConfig;
use Laminas\Session\Container as LaminasContainer;
use Laminas\Session\SaveHandler\Cache;
use Laminas\Session\SessionManager;
use Laminas\Session\Storage\SessionArrayStorage;

class Session
{

    private const COUCHBASE = 'couchbase';
    private const MEMCACHED = 'memcached';

    private $container;

    /**
     * @var SessionManager
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
     * @var string
     */
    private $cookiePath = '/';

    /**
     * @var bool
     */
    private $cookieHttpOnly = false;

    /**
     * @var bool
     */
    private $cookieSecure = false;

    /**
     * @var string
     */
    private $cookieSameSite = '';

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        $this->setSavePath();
    }

    /**
     * @return void
     */
    public function destroy(): void
    {
        $this->manager->destroy([
            'send_expire_cookie' => true,
            'clear_storage'      => true,
        ]);
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            0,
            $params['path'],
            $params['domain'],
            $params['secure'],
            isset($params['httponly'])
        );
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
            : $defaultValue;
    }

    /**
     * @return string
     * @throws MissingDomainNameException
     */
    public function getDomainName(): string
    {
        if ($this->domainName === null && empty($_SERVER['HTTP_HOST'])) {
            throw new MissingDomainNameException();
        }
        return $this->domainName ?? $_SERVER['HTTP_HOST'];
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key): bool
    {
        return $this->container->offsetExists($key);
    }

    /**
     * @param string $key
     * @return void
     */
    public function remove($key): void
    {
        $this->container->offsetUnset($key);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set($key, $value): void
    {
        $this->container->offsetSet($key, $value);
    }

    /**
     * @param string $domainName
     * @return Session
     */
    public function setDomainName($domainName): Session
    {
        $this->domainName = $domainName;
        return $this;
    }

    public function cookiePath(string $path)
    {
        $this->cookiePath = $path;
        return $this;
    }

    public function cookieHttpOnly(bool $value)
    {
        $this->cookieHttpOnly = $value;
        return $this;
    }

    public function cookieSecure(bool $value)
    {
        $this->cookieSecure = $value;
        return $this;
    }

    public function cookieSameSite(string $value)
    {
        $validValues = ['', 'None', 'Strict'];
        if (!in_array($value, $validValues)) {
            throw new \InvalidArgumentException('Invalid value for cookieSameSite');
        }
        $this->cookieSameSite = $value;
        return $this;
    }

    /**
     * @return Session
     * @throws MissingDomainNameException
     */
    public function start(): Session
    {
        session_set_cookie_params([
            'lifetime' => $this->getLifetime(),
            'path' => $this->cookiePath,
            'domain' => $this->getDomainName(),
            'secure' => $this->cookieSecure,
            'httponly' => $this->cookieHttpOnly,
            'samesite' => $this->cookieSameSite,
        ]);

        $this->manager = new SessionManager($this->getConfig());
        $this->manager
            ->setSaveHandler($this->getSaveHandler())
            ->setStorage(new SessionArrayStorage())
            ->start();
        LaminasContainer::setDefaultManager($this->manager);

        $this->container = new Container(__CLASS__);

        return $this;
    }

    /**
     * @return StandardConfig
     */
    private function getConfig(): StandardConfig
    {
        $config = new StandardConfig();
        $config->setOptions([
            'save_path' => $this->options['save_path'],
        ]);
        return $config;
    }

    /**
     * @return int
     */
    private function getLifetime(): int
    {
        return $this->options['adapter']['options']['lifetime'] ?? 0;
    }

    /**
     * @return Cache|Couchbase
     * @throws \Exception
     */
    private function getSaveHandler()
    {
        return $this->getOptions()['adapter']['name'] === self::COUCHBASE
            ? new Couchbase($this->getOptions()['adapter']['options'])
            : new Cache(\Laminas\Cache\StorageFactory::factory($this->getOptions()));
    }

    // @todo: Drasko: write our own memcached adapter!
    /**
     * @return array:
     */
    private function getOptions(): array
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
        if (is_array($this->options['adapter']['options']['servers'])) {
            $this->options['adapter']['options']['servers'] =
                array_filter($this->options['adapter']['options']['servers']);
        }
        return $this->options;
    }

    /**
     * @return StorageInterface
     */
    private function getStorage(): StorageInterface
    {
        return StorageFactory::factory($this->getOptions());
    }

    /**
     * @return void
     */
    private function setSavePath(): void
    {
        if (!empty($this->options['save_path'])) {
            session_save_path($this->options['save_path']);
            ini_set('session.save_path', $this->options['save_path']);
        }
    }
}
