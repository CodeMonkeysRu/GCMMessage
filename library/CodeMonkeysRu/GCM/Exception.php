<?php

namespace CodeMonkeysRu\GCM;

/**
 * Class Exception
 *
 * @package CodeMonkeysRu\GCM
 * @author Vladimir Savenkov <ivariable@gmail.com>
 * @author Steve Tauber <taubers@gmail.com>
 */
class Exception extends \Exception {

    const ILLEGAL_API_KEY = 1;
    const AUTHENTICATION_ERROR = 2;
    const MALFORMED_REQUEST = 3;
    const UNKNOWN_ERROR = 4;
    const MALFORMED_RESPONSE = 5;
    const INVALID_PARAMS = 6;
    const INVALID_TTL = 7;
    const OUTSIDE_TTL = 8;
}