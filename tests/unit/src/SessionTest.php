<?php

use G4\Session\Exception\MissingDomainNameException;
use G4\Session\Session;

class SessionTest extends PHPUnit\Framework\TestCase
{

    /**
     * @var Session
     */
    private $session;

    public function testSetDomainName()
    {
        $this->session->setDomainName('example.com');
        $this->assertEquals('example.com', $this->session->getDomainName());
    }

    public function testGetDomainName()
    {
        $_SERVER['HTTP_HOST'] = 'example1.com';
        $this->assertEquals('example1.com', $this->session->getDomainName());
    }

    public function testGetDomainNameException()
    {
        $this->expectException(MissingDomainNameException::class);
        $this->session->getDomainName();
    }

    public function testCookiePathDefault()
    {
        $reflection = new \ReflectionClass($this->session);
        $property = $reflection->getProperty('cookiePath');
        $property->setAccessible(true);
        $this->assertEquals('/', $property->getValue($this->session));
    }

    public function testCookiePathSetter()
    {
        $result = $this->session->cookiePath('/custom/path');
        $this->assertInstanceOf(Session::class, $result);
        
        $reflection = new \ReflectionClass($this->session);
        $property = $reflection->getProperty('cookiePath');
        $property->setAccessible(true);
        $this->assertEquals('/custom/path', $property->getValue($this->session));
    }

    public function testCookieHttpOnlyDefault()
    {
        $reflection = new \ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieHttpOnly');
        $property->setAccessible(true);
        $this->assertFalse($property->getValue($this->session));
    }

    public function testCookieHttpOnlySetter()
    {
        $result = $this->session->cookieHttpOnly(true);
        $this->assertInstanceOf(Session::class, $result);
        
        $reflection = new \ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieHttpOnly');
        $property->setAccessible(true);
        $this->assertTrue($property->getValue($this->session));
    }

    public function testCookieSecureDefault()
    {
        $reflection = new \ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSecure');
        $property->setAccessible(true);
        $this->assertFalse($property->getValue($this->session));
    }

    public function testCookieSecureSetter()
    {
        $result = $this->session->cookieSecure(true);
        $this->assertInstanceOf(Session::class, $result);
        
        $reflection = new \ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSecure');
        $property->setAccessible(true);
        $this->assertTrue($property->getValue($this->session));
    }

    public function testCookieSameSiteDefault()
    {
        $reflection = new \ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSameSite');
        $property->setAccessible(true);
        $this->assertEquals('', $property->getValue($this->session));
    }

    public function testCookieSameSiteSetterWithEmptyString()
    {
        $result = $this->session->cookieSameSite('');
        $this->assertInstanceOf(Session::class, $result);
        
        $reflection = new \ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSameSite');
        $property->setAccessible(true);
        $this->assertEquals('', $property->getValue($this->session));
    }

    public function testCookieSameSiteSetterWithLax()
    {
        $result = $this->session->cookieSameSite('Lax');
        $this->assertInstanceOf(Session::class, $result);
        
        $reflection = new \ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSameSite');
        $property->setAccessible(true);
        $this->assertEquals('Lax', $property->getValue($this->session));
    }

    public function testCookieSameSiteSetterWithStrict()
    {
        $result = $this->session->cookieSameSite('Strict');
        $this->assertInstanceOf(Session::class, $result);
        
        $reflection = new \ReflectionClass($this->session);
        $property = $reflection->getProperty('cookieSameSite');
        $property->setAccessible(true);
        $this->assertEquals('Strict', $property->getValue($this->session));
    }

    public function testCookieSameSiteSetterWithInvalidValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value for cookieSameSite');
        $this->session->cookieSameSite('None');
    }

    public function testCookieSameSiteSetterWithInvalidValueRandom()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value for cookieSameSite');
        $this->session->cookieSameSite('InvalidValue');
    }

    public function testFluentInterfaceChaining()
    {
        $result = $this->session
            ->setDomainName('example.com')
            ->cookiePath('/app')
            ->cookieHttpOnly(true)
            ->cookieSecure(true)
            ->cookieSameSite('Strict');
        
        $this->assertInstanceOf(Session::class, $result);
        $this->assertEquals('example.com', $this->session->getDomainName());
    }

    public function testGetOptionsUnsetsCouchbaseOnlyKeysForMemcached()
    {
        $method = (new ReflectionClass(Session::class))->getMethod('getOptions');
        $session = new Session([
            'adapter' => [
                'name'    => 'memcached',
                'options' => [
                    'host'          => '127.0.0.1',
                    'port'          => 11211,
                    'bucket'        => 'default',
                    'lifetime'      => 3600,
                    'persistent'    => true,
                    'tcp_keepalive' => true,
                    'dnssrv'        => false,
                    'timeout'       => 1000000,
                    'servers'       => ['server1', '', 'server2'],
                ],
            ],
        ]);

        $adapterOptions = $method->invoke($session)['adapter']['options'];

        $this->assertSame(['servers'], array_keys($adapterOptions));
        $this->assertSame(['server1', 2 => 'server2'], $adapterOptions['servers']);
    }

    public function testGetOptionsKeepsOptionsForCouchbase()
    {
        $method = (new ReflectionClass(Session::class))->getMethod('getOptions');
        $options = [
            'adapter' => [
                'name'    => 'couchbase',
                'options' => [
                    'servers'       => ['127.0.0.1:8091'],
                    'timeout'       => 1000000,
                    'tcp_keepalive' => true,
                    'dnssrv'        => false,
                ],
            ],
        ];

        $this->assertSame($options, $method->invoke(new Session($options)));
    }

    protected function setUp(): void
    {
        $this->session = new Session([]);
    }

    protected function tearDown(): void
    {
        $this->session = null;
        $_SERVER['HTTP_HOST'] = null;
    }
}
