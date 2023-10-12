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
