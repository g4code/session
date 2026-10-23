<?php

use G4\Session\SaveHandler\Couchbase;

class CouchbaseTest extends PHPUnit\Framework\TestCase
{
    public function testGcMatchesSessionHandlerInterfaceAndReturnsZero()
    {
        // constructor connects to couchbase; gc() does not need it
        $saveHandler = (new ReflectionClass(Couchbase::class))->newInstanceWithoutConstructor();

        $this->assertInstanceOf(SessionHandlerInterface::class, $saveHandler);
        $this->assertSame(0, $saveHandler->gc(3600));
    }
}
