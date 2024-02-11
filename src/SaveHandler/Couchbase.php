<?php

namespace G4\Session\SaveHandler;

use G4\Mcache\Mcache;
use G4\Mcache\McacheFactory;
use Laminas\Session\SaveHandler\SaveHandlerInterface;

class Couchbase implements SaveHandlerInterface
{

    /**
     * @var Mcache
     */
    private $mcache;

    /**
     * @var array
     */
    private $options;

    /**
     * @param array $options
     * @throws \Exception
     */
    public function __construct(array $options)
    {
        $this->options = $options;
        $this->mcache  = McacheFactory::createInstance(
            McacheFactory::DRIVER_COUCHBASE,
            $this->options,
            __CLASS__
        );
    }

    /**
     * Unnecessary for couchbase
     */
    public function open($savePath, $name): bool
    {
        return true;
    }

    /**
     * Unnecessary for couchbase
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @param string $id
     * @return string
     */
    public function read($id): string
    {
        return $this->mcache
            ->key($id)
            ->get() ?: '';
    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data): bool
    {
        $result = $this->mcache
            ->key($id)
            ->value($data)
            ->expiration($this->getLifetime())
            ->set();

        return !empty($result);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function destroy($id): bool
    {
        return $this->mcache
            ->key($id)
            ->delete();
    }

    /**
     * Unnecessary for couchbase
     */
    public function gc($maxlifetime): bool
    {
        return true;
    }

    /**
     * @return int
     */
    private function getLifetime(): int
    {
        return isset($this->options['lifetime'])
            ? (int) $this->options['lifetime']
            : 0;
    }
}
