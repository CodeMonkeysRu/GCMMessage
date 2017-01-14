<?php
namespace CodeMonkeysRu\GCM;

class Exception extends \Exception
{

    const ILLEGAL_API_KEY = 1;
    const AUTHENTICATION_ERROR = 2;
    const MALFORMED_REQUEST = 3;
    const UNKNOWN_ERROR = 4;
    const MALFORMED_RESPONSE = 5;
    const INVALID_DATA_KEY = 6;
    const MISMATCH_SENDER_ID = 7;
    
    private $mustRetry = false;
    private $waitSeconds = null;
    
    public function setMustRetry($bool)
    {    
        $this->mustRetry = $bool;
    }
    public function getMustRetry()
    {    
        return $this->mustRetry;
    }
    public function setWaitSeconds($int)
    {    
        $this->waitSeconds = $int;
    }
    public function getWaitSeconds()
    {    
        return $this->waitSeconds;
    }
}
