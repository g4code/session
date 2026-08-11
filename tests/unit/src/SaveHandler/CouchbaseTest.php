<?php

namespace G4\SessionTest\SaveHandler;

use G4\Mcache\Mcache;
use G4\Session\SaveHandler\Couchbase;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CouchbaseTest extends TestCase
{
    /**
     * @var Couchbase
     */
    private $saveHandler;

    protected function setUp(): void
    {
        $this->saveHandler = new Couchbase([
            'bucket'   => 'default',
            'servers'  => ['127.0.0.1:8091'],
            'lifetime' => 3600,
        ]);
    }

    public function testImplementsSaveHandlerInterface()
    {
        $this->assertInstanceOf(\Zend\Session\SaveHandler\SaveHandlerInterface::class, $this->saveHandler);
    }

    public function testImplementsSessionHandlerInterface()
    {
        $this->assertInstanceOf(\SessionHandlerInterface::class, $this->saveHandler);
    }

    public function testOpenReturnsTrue()
    {
        $this->assertTrue($this->saveHandler->open('/path', 'sessionname'));
    }

    public function testCloseReturnsTrue()
    {
        $this->assertTrue($this->saveHandler->close());
    }

    public function testGcReturnsTrue()
    {
        $this->assertTrue($this->saveHandler->gc(3600));
    }

    public function testReadReturnsStringFromMcache()
    {
        $mcache = $this->createMock(Mcache::class);
        $mcache->expects($this->once())
            ->method('key')
            ->with('session-id')
            ->willReturnSelf();
        $mcache->expects($this->once())
            ->method('get')
            ->willReturn('session-data');

        $this->injectMcache($mcache);

        $this->assertEquals('session-data', $this->saveHandler->read('session-id'));
    }

    public function testReadReturnsEmptyStringWhenMcacheReturnsFalsy()
    {
        $mcache = $this->createMock(Mcache::class);
        $mcache->method('key')->willReturnSelf();
        $mcache->method('get')->willReturn(false);

        $this->injectMcache($mcache);

        $this->assertEquals('', $this->saveHandler->read('session-id'));
    }

    public function testReadReturnsEmptyStringWhenMcacheReturnsNull()
    {
        $mcache = $this->createMock(Mcache::class);
        $mcache->method('key')->willReturnSelf();
        $mcache->method('get')->willReturn(null);

        $this->injectMcache($mcache);

        $this->assertEquals('', $this->saveHandler->read('session-id'));
    }

    public function testWriteCallsMcacheSetAndReturnsTrue()
    {
        $mcache = $this->createMock(Mcache::class);
        $mcache->method('key')->with('session-id')->willReturnSelf();
        $mcache->method('value')->with('data')->willReturnSelf();
        $mcache->method('expiration')->with(3600)->willReturnSelf();
        $mcache->method('set')->willReturn(true);

        $this->injectMcache($mcache);

        $this->assertTrue($this->saveHandler->write('session-id', 'data'));
    }

    public function testWriteReturnsFalseWhenMcacheSetReturnsEmpty()
    {
        $mcache = $this->createMock(Mcache::class);
        $mcache->method('key')->willReturnSelf();
        $mcache->method('value')->willReturnSelf();
        $mcache->method('expiration')->willReturnSelf();
        $mcache->method('set')->willReturn(false);

        $this->injectMcache($mcache);

        $this->assertFalse($this->saveHandler->write('session-id', 'data'));
    }

    public function testWriteUsesLifetimeFromOptions()
    {
        $reflection = new ReflectionClass(Couchbase::class);
        $method = $reflection->getMethod('getLifetime');
        $method->setAccessible(true);

        $this->assertEquals(3600, $method->invoke($this->saveHandler));
    }

    public function testGetLifetimeReturnsZeroWhenNotSet()
    {
        $saveHandler = new Couchbase(['bucket' => 'default', 'servers' => ['127.0.0.1:8091']]);

        $reflection = new ReflectionClass(Couchbase::class);
        $method = $reflection->getMethod('getLifetime');
        $method->setAccessible(true);

        $this->assertEquals(0, $method->invoke($saveHandler));
    }

    public function testDestroyCallsMcacheDelete()
    {
        $mcache = $this->createMock(Mcache::class);
        $mcache->method('key')->with('session-id')->willReturnSelf();
        $mcache->method('delete')->willReturn(true);

        $this->injectMcache($mcache);

        $this->assertTrue($this->saveHandler->destroy('session-id'));
    }

    public function testDestroyReturnsFalseWhenMcacheDeleteReturnsFalse()
    {
        $mcache = $this->createMock(Mcache::class);
        $mcache->method('key')->willReturnSelf();
        $mcache->method('delete')->willReturn(false);

        $this->injectMcache($mcache);

        $this->assertFalse($this->saveHandler->destroy('session-id'));
    }

    private function injectMcache($mcache)
    {
        $reflection = new ReflectionClass($this->saveHandler);
        $property = $reflection->getProperty('mcache');
        $property->setAccessible(true);
        $property->setValue($this->saveHandler, $mcache);
    }
}
