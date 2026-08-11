<?php

namespace G4\SessionTest;

use G4\Session\Container;
use G4\Session\Exception\MissingDomainNameException;
use G4\Session\Session;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Zend\Session\SessionManager;

class SessionTest extends TestCase
{
    /**
     * @var Session
     */
    private $session;

    protected function setUp(): void
    {
        $this->session = new Session([]);
    }

    protected function tearDown(): void
    {
        $this->session = null;
        unset($_SERVER['HTTP_HOST']);
    }

    public function testConstructStoresOptions()
    {
        $options = ['adapter' => ['name' => 'memcached', 'options' => []]];
        $session = new Session($options);

        $reflection = new ReflectionClass($session);
        $optionsProperty = $reflection->getProperty('options');
        $optionsProperty->setAccessible(true);

        $this->assertSame($options, $optionsProperty->getValue($session));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testConstructWithSavePathCallsSessionSavePath()
    {
        $this->expectNotToPerformAssertions();

        new Session(['save_path' => '/tmp/session-test-construct']);
    }

    public function testSetDomainName()
    {
        $this->session->setDomainName('example.com');
        $this->assertEquals('example.com', $this->session->getDomainName());
    }

    public function testSetDomainNameReturnsSelf()
    {
        $this->assertSame($this->session, $this->session->setDomainName('example.com'));
    }

    public function testGetDomainNameFromHttpHost()
    {
        $_SERVER['HTTP_HOST'] = 'example1.com';
        $this->assertEquals('example1.com', $this->session->getDomainName());
    }

    public function testGetDomainNameThrowsExceptionWhenNoDomainAndNoHttpHost()
    {
        $this->expectException(MissingDomainNameException::class);
        $this->session->getDomainName();
    }

    public function testSetDomainNameTakesPrecedenceOverHttpHost()
    {
        $_SERVER['HTTP_HOST'] = 'fromhost.com';
        $this->session->setDomainName('fromsetter.com');
        $this->assertEquals('fromsetter.com', $this->session->getDomainName());
    }

    public function testCookiePathSetsValueAndReturnsSelf()
    {
        $this->assertSame($this->session, $this->session->cookiePath('/custom/path'));

        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('cookiePath');
        $property->setAccessible(true);
        $this->assertEquals('/custom/path', $property->getValue($this->session));
    }

    public function testCookiePathDefaultValue()
    {
        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('cookiePath');
        $property->setAccessible(true);
        $this->assertEquals('/', $property->getValue($this->session));
    }

    public function testCookieHttpOnlySetsValueAndReturnsSelf()
    {
        $this->assertSame($this->session, $this->session->cookieHttpOnly(true));

        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieHttpOnly');
        $property->setAccessible(true);
        $this->assertTrue($property->getValue($this->session));
    }

    public function testCookieHttpOnlyDefaultValue()
    {
        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieHttpOnly');
        $property->setAccessible(true);
        $this->assertFalse($property->getValue($this->session));
    }

    public function testCookieSecureSetsValueAndReturnsSelf()
    {
        $this->assertSame($this->session, $this->session->cookieSecure(true));

        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSecure');
        $property->setAccessible(true);
        $this->assertTrue($property->getValue($this->session));
    }

    public function testCookieSecureDefaultValue()
    {
        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSecure');
        $property->setAccessible(true);
        $this->assertFalse($property->getValue($this->session));
    }

    public function testCookieSameSiteSetsValueAndReturnsSelf()
    {
        $this->assertSame($this->session, $this->session->cookieSameSite('Lax'));

        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSameSite');
        $property->setAccessible(true);
        $this->assertEquals('Lax', $property->getValue($this->session));
    }

    public function testCookieSameSiteDefaultValue()
    {
        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSameSite');
        $property->setAccessible(true);
        $this->assertEquals('', $property->getValue($this->session));
    }

    public function testCookieSameSiteAcceptsStrict()
    {
        $this->session->cookieSameSite('Strict');

        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSameSite');
        $property->setAccessible(true);
        $this->assertEquals('Strict', $property->getValue($this->session));
    }

    public function testCookieSameSiteAcceptsEmptyString()
    {
        $this->session->cookieSameSite('');

        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSameSite');
        $property->setAccessible(true);
        $this->assertEquals('', $property->getValue($this->session));
    }

    public function testCookieSameSiteThrowsExceptionForInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->session->cookieSameSite('Invalid');
    }

    public function testCookieSameSiteThrowsExceptionForNone()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->session->cookieSameSite('None');
    }

    public function testGetReturnsValueWhenKeyExists()
    {
        $container = $this->createMock(Container::class);
        $container->method('offsetExists')->with('foo')->willReturn(true);
        $container->method('offsetGet')->with('foo')->willReturn('bar');

        $this->injectContainer($container);

        $this->assertEquals('bar', $this->session->get('foo'));
    }

    public function testGetReturnsDefaultWhenKeyDoesNotExistAndDefaultProvided()
    {
        $container = $this->createMock(Container::class);
        $container->method('offsetExists')->with('foo')->willReturn(false);

        $this->injectContainer($container);

        $this->assertEquals('default', $this->session->get('foo', 'default'));
    }

    public function testGetReturnsNullWhenKeyDoesNotExistAndNoDefault()
    {
        $container = $this->createMock(Container::class);
        $container->method('offsetExists')->with('foo')->willReturn(false);

        $this->injectContainer($container);

        $this->assertNull($this->session->get('foo'));
    }

    public function testGetReturnsNullWhenKeyDoesNotExistAndDefaultIsNull()
    {
        $container = $this->createMock(Container::class);
        $container->method('offsetExists')->with('foo')->willReturn(false);

        $this->injectContainer($container);

        $this->assertNull($this->session->get('foo', null));
    }

    public function testHasReturnsTrueWhenKeyExists()
    {
        $container = $this->createMock(Container::class);
        $container->method('offsetExists')->with('foo')->willReturn(true);

        $this->injectContainer($container);

        $this->assertTrue($this->session->has('foo'));
    }

    public function testHasReturnsFalseWhenKeyDoesNotExist()
    {
        $container = $this->createMock(Container::class);
        $container->method('offsetExists')->with('foo')->willReturn(false);

        $this->injectContainer($container);

        $this->assertFalse($this->session->has('foo'));
    }

    public function testSetCallsContainerOffsetSet()
    {
        $container = $this->createMock(Container::class);
        $container->expects($this->once())
            ->method('offsetSet')
            ->with('foo', 'bar');

        $this->injectContainer($container);

        $this->session->set('foo', 'bar');
    }

    public function testRemoveCallsContainerOffsetUnset()
    {
        $container = $this->createMock(Container::class);
        $container->expects($this->once())
            ->method('offsetUnset')
            ->with('foo');

        $this->injectContainer($container);

        $this->session->remove('foo');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testDestroyCallsManagerDestroyAndSetsCookie()
    {
        $manager = $this->createMock(SessionManager::class);
        $manager->expects($this->once())
            ->method('destroy')
            ->with([
                'send_expire_cookie' => true,
                'clear_storage'      => true,
            ]);

        $this->injectManager($manager);

        $this->session->destroy();
    }

    public function testGetLifetimeReturnsZeroWhenNotSet()
    {
        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('getLifetime');
        $method->setAccessible(true);

        $session = new Session([]);
        $this->assertEquals(0, $method->invoke($session));
    }

    public function testGetLifetimeReturnsValueFromOptions()
    {
        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('getLifetime');
        $method->setAccessible(true);

        $session = new Session([
            'adapter' => [
                'options' => ['lifetime' => 3600],
            ],
        ]);
        $this->assertEquals(3600, $method->invoke($session));
    }

    public function testGetOptionsReturnsOptionsForNonMemcachedAdapter()
    {
        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('getOptions');
        $method->setAccessible(true);

        $options = [
            'adapter' => [
                'name'    => 'couchbase',
                'options' => [
                    'host'    => '127.0.0.1',
                    'servers' => ['127.0.0.1:8091'],
                ],
            ],
        ];
        $session = new Session($options);

        $result = $method->invoke($session);
        $this->assertSame($options, $result);
    }

    public function testGetOptionsUnsetsMemcachedSpecificKeys()
    {
        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('getOptions');
        $method->setAccessible(true);

        $options = [
            'adapter' => [
                'name'    => Session::MEMCACHED,
                'options' => [
                    'host'           => '127.0.0.1',
                    'port'           => 11211,
                    'bucket'         => 'default',
                    'lifetime'        => 3600,
                    'persistent'      => true,
                    'tcp_keepalive'   => true,
                    'dnssrv'          => false,
                    'servers'         => ['server1', '', 'server2'],
                ],
            ],
        ];
        $session = new Session($options);

        $result = $method->invoke($session);
        $adapterOptions = $result['adapter']['options'];

        $this->assertArrayNotHasKey('host', $adapterOptions);
        $this->assertArrayNotHasKey('port', $adapterOptions);
        $this->assertArrayNotHasKey('bucket', $adapterOptions);
        $this->assertArrayNotHasKey('lifetime', $adapterOptions);
        $this->assertArrayNotHasKey('persistent', $adapterOptions);
        $this->assertArrayNotHasKey('tcp_keepalive', $adapterOptions);
        $this->assertArrayNotHasKey('dnssrv', $adapterOptions);
        $this->assertCount(2, $adapterOptions['servers']);
        $this->assertContains('server1', $adapterOptions['servers']);
        $this->assertContains('server2', $adapterOptions['servers']);
    }

    public function testGetOptionsFiltersEmptyServersWhenArray()
    {
        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('getOptions');
        $method->setAccessible(true);

        $options = [
            'adapter' => [
                'name'    => 'couchbase',
                'options' => [
                    'servers' => ['', '', ''],
                ],
            ],
        ];
        $session = new Session($options);

        $result = $method->invoke($session);
        $this->assertSame([], $result['adapter']['options']['servers']);
    }

    public function testGetSaveHandlerReturnsCouchbaseForCouchbaseAdapter()
    {
        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('getSaveHandler');
        $method->setAccessible(true);

        $options = [
            'adapter' => [
                'name'    => Session::COUCHBASE,
                'options' => [
                    'host'     => '127.0.0.1',
                    'port'     => 8091,
                    'bucket'   => 'default',
                    'password' => '',
                    'servers'  => ['127.0.0.1:8091'],
                ],
            ],
        ];
        $session = new Session($options);

        $handler = $method->invoke($session);
        $this->assertInstanceOf(\G4\Session\SaveHandler\Couchbase::class, $handler);
    }

    public function testCouchbaseConstant()
    {
        $this->assertEquals('couchbase', Session::COUCHBASE);
    }

    public function testMemcachedConstant()
    {
        $this->assertEquals('memcached', Session::MEMCACHED);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testStartWithCouchbaseAdapter()
    {
        $this->defineFakeCouchbaseClass();

        $savePath = sys_get_temp_dir() . '/g4-session-test-start';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }

        $options = [
            'save_path' => $savePath,
            'adapter'   => [
                'name'    => Session::COUCHBASE,
                'options' => [
                    'bucket'   => 'default',
                    'servers'  => ['127.0.0.1:8091'],
                    'lifetime' => 3600,
                ],
            ],
        ];

        $session = new Session($options);
        $session->setDomainName('example.com');

        $this->assertSame($session, $session->start());

        $session->set('foo', 'bar');
        $this->assertTrue($session->has('foo'));
        $this->assertEquals('bar', $session->get('foo'));
        $this->assertEquals('default-val', $session->get('missing', 'default-val'));

        $session->remove('foo');
        $this->assertFalse($session->has('foo'));

        $session->destroy();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testStartThrowsWhenCouchbaseClientMissing()
    {
        $savePath = sys_get_temp_dir() . '/g4-session-test-start-err';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }

        $options = [
            'save_path' => $savePath,
            'adapter'   => [
                'name'    => Session::COUCHBASE,
                'options' => [
                    'bucket'   => 'default',
                    'servers'  => ['127.0.0.1:8091'],
                    'lifetime' => 3600,
                ],
            ],
        ];

        $session = new Session($options);
        $session->setDomainName('example.com');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Couchbase client missing!');

        $session->start();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testGetConfigReturnsStandardConfig()
    {
        $savePath = sys_get_temp_dir() . '/g4-session-test-config';
        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }

        $session = new Session(['save_path' => $savePath]);

        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('getConfig');
        $method->setAccessible(true);

        $config = $method->invoke($session);
        $this->assertInstanceOf(\Zend\Session\Config\StandardConfig::class, $config);
        $this->assertEquals($savePath, $config->getSavePath());
    }

    public function testGetStorageReturnsStorageInterface()
    {
        $options = [
            'adapter' => [
                'name'    => 'memory',
                'options' => [],
            ],
        ];
        $session = new Session($options);

        $reflection = new ReflectionClass(Session::class);
        $method = $reflection->getMethod('getStorage');
        $method->setAccessible(true);

        $storage = @$method->invoke($session);
        $this->assertInstanceOf(\Zend\Cache\Storage\StorageInterface::class, $storage);
    }

    private function defineFakeCouchbaseClass()
    {
        if (class_exists(\Couchbase::class, false)) {
            return;
        }
        eval(<<<'PHP'
class Couchbase
{
    public function setTimeout($timeout) { return $this; }
    public function get($key) { return false; }
    public function set($key, $value, $expiration) { return true; }
    public function delete($key) { return true; }
    public function replace($key, $value, $expiration) { return true; }
}
PHP
        );
    }

    private function injectContainer($container)
    {
        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('container');
        $property->setAccessible(true);
        $property->setValue($this->session, $container);
    }

    private function injectManager($manager)
    {
        $reflection = new ReflectionClass($this->session);
        $property = $reflection->getProperty('manager');
        $property->setAccessible(true);
        $property->setValue($this->session, $manager);
    }
}
