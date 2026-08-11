<?php

namespace G4\SessionTest;

use G4\Session\ErrorCodes;
use PHPUnit\Framework\TestCase;

class ErrorCodesTest extends TestCase
{
    public function testMissingDomainNameExceptionCode()
    {
        $this->assertEquals(70001, ErrorCodes::MISSING_DOMAIN_NAME_EXCEPTION);
    }
}
