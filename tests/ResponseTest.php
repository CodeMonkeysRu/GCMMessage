<?php
namespace CodeMonkeysRu\GCM;

/**
 * Class ResponseTest
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \CodeMonkeysRu\GCM\Response
     */
    private $responseOK;
    private $responseHeadersOK;
    private $responseInvalidDataKey;

    protected function setUp()
    {
        $messageOK = new \CodeMonkeysRu\GCM\Message(array(1, 2, 3, 4, 5, 6, 7));
        
        $responseHeadersOK = ["HTTP\/1.1 200 OK",
        "Content-Type: application\/json; charset=UTF-8",
        "Date: Sat, 14 Jan 2017 05:32:39 GMT",
        "Expires: Sat, 14 Jan 2017 05:32:39 GMT",
        "Cache-Control: private, max-age=0",
        "X-Content-Type-Options: nosniff","X-Frame-Options: SAMEORIGIN",
        "X-XSS-Protection: 1; mode=block",
        "Server: GSE",
        "Alt-Svc: quic=\":443\"; ma=2592000; v=\"35,34\"",
        "Accept-Ranges: none",
        "Vary: Accept-Encoding",
        "Transfer-Encoding: chunked"];
                            
        $responseBodyOK = '{ "multicast_id": 216,
            "success": 3,
            "failure": 4,
            "canonical_ids": 1,
            "results": [
              { "message_id": "1:0408" },
              { "error": "Unavailable" },
              { "error": "InvalidRegistration" },
              { "message_id": "1:1516" },
              { "message_id": "1:2342", "registration_id": "32" },
              { "error": "NotRegistered"},
              { "error": "MismatchSenderId"}
            ]
          }';
          
        $this->responseHeadersOK = $responseHeadersOK;
        $this->responseOK = new \CodeMonkeysRu\GCM\Response($messageOK, $responseBodyOK, $responseHeadersOK);
                        
        $messageInvalidDataKey = new \CodeMonkeysRu\GCM\Message(array(1, 2));
        $responseBodyInvalidDataKey = '{ "multicast_id": 216,
            "success": 0,
            "failure": 2,
            "canonical_ids": 0,
            "results": [
              { "error": "InvalidDataKey" },
              { "error": "InvalidDataKey" }          
            ]
          }';
          
        $this->responseInvalidDataKey = new \CodeMonkeysRu\GCM\Response(
            $messageInvalidDataKey,
            $responseBodyInvalidDataKey,
            $responseHeadersOK
        );
    }

    public function testGetResponseHeaders()
    {
        $this->assertEquals($this->responseHeadersOK, $this->responseOK->getResponseHeaders());
    }

    public function testGetNewRegistrationIds()
    {
        $this->assertEquals(array(5 => 32), $this->responseOK->getNewRegistrationIds());
    }

    public function testGetInvalidRegistrationIds()
    {
        $this->assertEquals(array(3, 6), $this->responseOK->getInvalidRegistrationIds());
    }

    public function testGetUnavailableRegistrationIds()
    {
        $this->assertEquals(array(2), $this->responseOK->getUnavailableRegistrationIds());
    }
    
    public function testGetExistsMismatchSenderId()
    {
        $this->assertEquals(true, $this->responseOK->getExistsMismatchSenderId())
        &&
        $this->assertEquals(false, $this->responseInvalidDataKey->getExistsMismatchSenderId());
    }
    public function testGetMismatchSenderIdIds()
    {
        $this->assertEquals(array(7), $this->responseOK->getMismatchSenderIdIds());
    }
    public function testGetExistsInvalidDataKey()
    {
        $this->assertEquals(true, $this->responseInvalidDataKey->getExistsInvalidDataKey())
        &&
        $this->assertEquals(false, $this->responseOK->getExistsInvalidDataKey());
    }
}
