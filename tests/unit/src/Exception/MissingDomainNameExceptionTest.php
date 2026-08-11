<?php

namespace G4\SessionTest\Exception;

use G4\Session\ErrorCodes;
use G4\Session\Exception\MissingDomainNameException;
use PHPUnit\Framework\TestCase;

class MissingDomainNameExceptionTest extends TestCase
{
    public function testExtendsException()
    {
        $exception = new MissingDomainNameException();
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testMessage()
    {
        $exception = new MissingDomainNameException();
        $this->assertEquals('Missing Domain Name', $exception->getMessage());
    }

    public function testCode()
    {
        $exception = new MissingDomainNameException();
        $this->assertEquals(ErrorCodes::MISSING_DOMAIN_NAME_EXCEPTION, $exception->getCode());
    }

    public function testMessageConstant()
    {
        $this->assertEquals('Missing Domain Name', MissingDomainNameException::MESSAGE);
    }
}
