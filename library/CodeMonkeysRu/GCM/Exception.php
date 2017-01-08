<?php
namespace CodeMonkeysRu\GCM;

class Exception extends \Exception
{

    const ILLEGAL_API_KEY = 1;
    const AUTHENTICATION_ERROR = 2;
    const MALFORMED_REQUEST = 3;
    const UNKNOWN_ERROR = 4;
    const MALFORMED_RESPONSE = 5;
    const INTERNAL_SERVER_ERROR = 6;
    const SERVICE_UNAVAILABLE = 7;
    const INVALID_DATA_KEY = 8;
    const CURL_ERROR = 9;
    const MISMATCH_SENDER_ID = 10;
}

/*
Copyright (c) 2014 Vladimir Savenkov

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
