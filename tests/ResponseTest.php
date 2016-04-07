<?php

class ResponseTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var \CodeMonkeysRu\GCM\Response
     */
    private $response;

    protected function setUp()
    {
        $message = new \CodeMonkeysRu\GCM\Message(array(1, 2, 3, 4, 5, 6));
        $responseBody = '{ "multicast_id": 216,
            "success": 3,
            "failure": 3,
            "canonical_ids": 1,
            "results": [
              { "message_id": "1:0408" },
              { "error": "Unavailable" },
              { "error": "InvalidRegistration" },
              { "message_id": "1:1516" },
              { "message_id": "1:2342", "registration_id": "32" },
              { "error": "NotRegistered"}
            ],
            "error": null
          }';

        $this->response = new \CodeMonkeysRu\GCM\Response($message, $responseBody);
    }

    public function testGetNewRegistrationIds()
    {
        $this->assertEquals(array(5 => 32), $this->response->getNewRegistrationIds());
    }

    public function testGetInvalidRegistrationIds()
    {
        $this->assertEquals(array(3, 6), $this->response->getInvalidRegistrationIds());
    }

    public function testGetUnavailableRegistrationIds()
    {
        $this->assertEquals(array(2), $this->response->getUnavailableRegistrationIds());
    }

}