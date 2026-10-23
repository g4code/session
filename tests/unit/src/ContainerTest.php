<?php

namespace G4\SessionTest;

use G4\Session\Container;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Laminas\Session\Container as LaminasContainer;

class ContainerTest extends TestCase
{
    /**
     * Laminas container starts a real session, which fails once PHPUnit has sent output
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testConstructorCreatesLaminasContainer()
    {
        $g4Container = new Container('test_name');

        $reflection = new ReflectionClass($g4Container);
        $property = $reflection->getProperty('container');

        $this->assertInstanceOf(LaminasContainer::class, $property->getValue($g4Container));
    }

    public function testOffsetExistsReturnsTrueWhenKeyExists()
    {
        $laminasContainer = $this->createMock(LaminasContainer::class);
        $laminasContainer->method('offsetExists')->with('foo')->willReturn(true);

        $g4Container = $this->withLaminasContainer($laminasContainer);

        $this->assertTrue($g4Container->offsetExists('foo'));
    }

    public function testOffsetExistsReturnsFalseWhenKeyDoesNotExist()
    {
        $laminasContainer = $this->createMock(LaminasContainer::class);
        $laminasContainer->method('offsetExists')->with('foo')->willReturn(false);

        $g4Container = $this->withLaminasContainer($laminasContainer);

        $this->assertFalse($g4Container->offsetExists('foo'));
    }

    public function testOffsetGetReturnsValue()
    {
        $laminasContainer = $this->createMock(LaminasContainer::class);
        $laminasContainer->method('offsetGet')->with('foo')->willReturn('bar');

        $g4Container = $this->withLaminasContainer($laminasContainer);

        $this->assertEquals('bar', $g4Container->offsetGet('foo'));
    }

    public function testOffsetSetCallsLaminasContainer()
    {
        $laminasContainer = $this->createMock(LaminasContainer::class);
        $laminasContainer->expects($this->once())
            ->method('offsetSet')
            ->with('foo', 'bar');

        $g4Container = $this->withLaminasContainer($laminasContainer);

        $g4Container->offsetSet('foo', 'bar');
    }

    public function testOffsetUnsetCallsLaminasContainer()
    {
        $laminasContainer = $this->createMock(LaminasContainer::class);
        $laminasContainer->expects($this->once())
            ->method('offsetUnset')
            ->with('foo');

        $g4Container = $this->withLaminasContainer($laminasContainer);

        $g4Container->offsetUnset('foo');
    }

    public function testGetDataReturnsArrayCopy()
    {
        $data = ['foo' => 'bar', 'baz' => 'qux'];

        $laminasContainer = $this->createMock(LaminasContainer::class);
        $laminasContainer->method('getArrayCopy')->willReturn($data);

        $g4Container = $this->withLaminasContainer($laminasContainer);

        $this->assertSame($data, $g4Container->getData());
    }

    public function testHasDataReturnsTrueWhenDataIsNotEmpty()
    {
        $laminasContainer = $this->createMock(LaminasContainer::class);
        $laminasContainer->method('getArrayCopy')->willReturn(['foo' => 'bar']);

        $g4Container = $this->withLaminasContainer($laminasContainer);

        $this->assertTrue($g4Container->hasData());
    }

    public function testHasDataReturnsFalseWhenDataIsEmpty()
    {
        $laminasContainer = $this->createMock(LaminasContainer::class);
        $laminasContainer->method('getArrayCopy')->willReturn([]);

        $g4Container = $this->withLaminasContainer($laminasContainer);

        $this->assertFalse($g4Container->hasData());
    }

    public function testSetDataCallsExchangeArrayAndReturnsSelf()
    {
        $data = ['foo' => 'bar'];

        $laminasContainer = $this->createMock(LaminasContainer::class);
        $laminasContainer->expects($this->once())
            ->method('exchangeArray')
            ->with($data);

        $g4Container = $this->withLaminasContainer($laminasContainer);

        $this->assertSame($g4Container, $g4Container->setData($data));
    }

    private function withLaminasContainer(LaminasContainer $laminasContainer): Container
    {
        $reflection = new ReflectionClass(Container::class);
        $g4Container = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('container')->setValue($g4Container, $laminasContainer);

        return $g4Container;
    }
}
