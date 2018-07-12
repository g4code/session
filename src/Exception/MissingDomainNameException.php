<?php

namespace G4\Session\Exception;

use Exception;
use G4\Session\ErrorCodes;

class MissingDomainNameException extends Exception
{

    const MESSAGE = 'Missing Domain Name';

    public function __construct()
    {
        parent::__construct(self::MESSAGE, ErrorCodes::MISSING_DOMAIN_NAME_EXCEPTION);
    }
}
