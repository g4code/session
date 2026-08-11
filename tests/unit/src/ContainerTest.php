<?php

namespace G4\SessionTest;

use G4\Session\Container;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Zend\Session\Container as ZendContainer;

class ContainerTest extends TestCase
{
    public function testConstructorCreatesZendContainer()
    {
        $g4Container = new Container('test_name');

        $reflection = new ReflectionClass($g4Container);
        $property = $reflection->getProperty('container');
        $property->setAccessible(true);

        $this->assertInstanceOf(ZendContainer::class, $property->getValue($g4Container));
    }

    public function testOffsetExistsReturnsTrueWhenKeyExists()
    {
        $zendContainer = $this->createMock(ZendContainer::class);
        $zendContainer->method('offsetExists')->with('foo')->willReturn(true);

        $g4Container = $this->withZendContainer($zendContainer);

        $this->assertTrue($g4Container->offsetExists('foo'));
    }

    public function testOffsetExistsReturnsFalseWhenKeyDoesNotExist()
    {
        $zendContainer = $this->createMock(ZendContainer::class);
        $zendContainer->method('offsetExists')->with('foo')->willReturn(false);

        $g4Container = $this->withZendContainer($zendContainer);

        $this->assertFalse($g4Container->offsetExists('foo'));
    }

    public function testOffsetGetReturnsValue()
    {
        $zendContainer = $this->createMock(ZendContainer::class);
        $zendContainer->method('offsetGet')->with('foo')->willReturn('bar');

        $g4Container = $this->withZendContainer($zendContainer);

        $this->assertEquals('bar', $g4Container->offsetGet('foo'));
    }

    public function testOffsetSetCallsZendContainer()
    {
        $zendContainer = $this->createMock(ZendContainer::class);
        $zendContainer->expects($this->once())
            ->method('offsetSet')
            ->with('foo', 'bar');

        $g4Container = $this->withZendContainer($zendContainer);

        $g4Container->offsetSet('foo', 'bar');
    }

    public function testOffsetUnsetCallsZendContainer()
    {
        $zendContainer = $this->createMock(ZendContainer::class);
        $zendContainer->expects($this->once())
            ->method('offsetUnset')
            ->with('foo');

        $g4Container = $this->withZendContainer($zendContainer);

        $g4Container->offsetUnset('foo');
    }

    public function testGetDataReturnsArrayCopy()
    {
        $data = ['foo' => 'bar', 'baz' => 'qux'];

        $zendContainer = $this->createMock(ZendContainer::class);
        $zendContainer->method('getArrayCopy')->willReturn($data);

        $g4Container = $this->withZendContainer($zendContainer);

        $this->assertSame($data, $g4Container->getData());
    }

    public function testHasDataReturnsTrueWhenDataIsNotEmpty()
    {
        $zendContainer = $this->createMock(ZendContainer::class);
        $zendContainer->method('getArrayCopy')->willReturn(['foo' => 'bar']);

        $g4Container = $this->withZendContainer($zendContainer);

        $this->assertTrue($g4Container->hasData());
    }

    public function testHasDataReturnsFalseWhenDataIsEmpty()
    {
        $zendContainer = $this->createMock(ZendContainer::class);
        $zendContainer->method('getArrayCopy')->willReturn([]);

        $g4Container = $this->withZendContainer($zendContainer);

        $this->assertFalse($g4Container->hasData());
    }

    public function testSetDataCallsExchangeArrayAndReturnsSelf()
    {
        $data = ['foo' => 'bar'];

        $zendContainer = $this->createMock(ZendContainer::class);
        $zendContainer->expects($this->once())
            ->method('exchangeArray')
            ->with($data);

        $g4Container = $this->withZendContainer($zendContainer);

        $this->assertSame($g4Container, $g4Container->setData($data));
    }

    private function withZendContainer(ZendContainer $zendContainer): Container
    {
        $g4Container = new Container('test_name');

        $reflection = new ReflectionClass($g4Container);
        $property = $reflection->getProperty('container');
        $property->setAccessible(true);
        $property->setValue($g4Container, $zendContainer);

        return $g4Container;
    }
}
