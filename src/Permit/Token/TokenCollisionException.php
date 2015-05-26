<?php namespace Permit\Token;

use DateTime;

class TokenCollisionException extends TokenException
{

    public $validUntil;

    public $originalToken;

    public function __construct(DateTime $validUntil=null, $originalToken='')
    {
        $this->validUntil = $validUntil;
        $this->originalToken = $originalToken;
    }

}