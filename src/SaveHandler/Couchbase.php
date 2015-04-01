<?php

namespace G4\Session\SaveHandler;

class Couchbase implements \Zend\Session\SaveHandler\SaveHandlerInterface
{

    /**
     * @var \G4\Mcache\Mcache
     */
    private $couchbase;

    /**
     * @var array
     */
    private $options;


    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options   = $options;
        $this->couchbase = \G4\Mcache\McacheFactory::createInstance(
            \G4\Mcache\McacheFactory::DRIVER_COUCHBASE,
            $this->options,
            __CLASS__
        );
    }

    /**
     * Unnecessary for couchbase
     */
    public function open($savePath, $name)
    {
        return true;
    }

    /**
     * Unnecessary for couchbase
     */
    public function close()
    {
        return true;
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function read($id)
    {
        return $this->couchbase->get($id);
    }

    /**
     * @param string $id
     * @param string $data
     * @return bool
     */
    public function write($id, $data)
    {
        $result = $this->couchbase->set($id, $data, $this->getLifetime());
        return !empty($result);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        return $this->couchbase->delete($id);
    }

    /**
     * Unnecessary for couchbase
     */
    public function gc($maxlifetime)
    {
        return true;
    }

    /**
     * @return int
     */
    private function getLifetime()
    {
        return isset($this->options['lifetime'])
            ? (int) $this->options['lifetime']
            : 0;
    }
}