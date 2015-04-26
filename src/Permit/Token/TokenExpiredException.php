<?php namespace Permit\Token;


use DateTime;

class TokenExpiredException extends TokenException
{

    public $expiryDate;

    public function __construct($msg, DateTime $expiryDate=null)
    {
        parent::__construct($msg);
        $this->expiryDate = $expiryDate;
    }
}