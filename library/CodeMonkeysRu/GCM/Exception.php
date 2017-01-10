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
    const CURL_ERROR = 7;
    const MISMATCH_SENDER_ID = 8;
}
