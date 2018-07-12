<?php


namespace G4\SessionTest;


use G4\Session\Exception\MissingDomainNameException;
use G4\Session\Session;


class SessionTest extends \PHPUnit_Framework_TestCase
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


    protected function setUp()
    {
        $this->session = new Session([]);
    }

    protected function tearDown()
    {
        $this->session = null;
    }
}
