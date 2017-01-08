<?php
namespace CodeMonkeysRu\GCM;

class Response
{

    /**
     * Unique ID (number) identifying the multicast message.
     *
     * @var integer
     */
    private $multicastId = null;

    /**
     * Number of messages that were processed without an error.
     *
     * @var integer
     */
    private $success = null;

    /**
     * Number of messages that could not be processed.
     *
     * @var integer
     */
    private $failure = null;

    /**
     * Number of results that contain a canonical registration ID.
     *
     * @var integer
     */
    private $canonicalIds = null;
    
    /**
     * Response headers.
     *
     * @var string[]
     */
    private $responseHeaders = null;
	
    /**
     * Did Google demand that we try again.
     *
     * @var boolean
     */
    private $mustRetry = null;
	
    /**
     * Number of seconds to wait.
     *
     * @var integer
     */
    private $waitSeconds = null;

    /**
     * Array of objects representing the status of the messages processed.
     * The objects are listed in the same order as the request
     * (i.e., for each registration ID in the request, its result is listed in the same index in the response)
     * and they can have these fields:
     *      message_id: String representing the message when it was successfully processed.
     *      registration_id: If set, means that GCM processed the message but it has another canonical
     *                       registration ID for that device, so sender should replace the IDs on future requests
     *                       (otherwise they might be rejected). This field is never set if there is an error in the request.
     *      error: String describing an error that occurred while processing the message for that recipient.
     *             The possible values are the same as documented in the above table. Note in particular "Unavailable"
     *             (meaning GCM servers were busy and could not process the message for that particular recipient,
     *             so it could be retried).
     *
     * @var array
     */
    private $results = array();

    public function __construct(Message $message, $responseBody, $responseHeaders)
    {
        $this->responseHeaders = $responseHeaders;
           
	$this->mustRetry = false;  
		    
	foreach($responseHeaders as $header) {
            if (strpos($header, 'Retry-After') !== false) {
		$this->mustRetry = true;
               	$this->waitSeconds = (int) explode(" ", $header)[1];
		break;
	        }
	    }	
			
        $data = \json_decode($responseBody, true);
        if ($data === null) {
            throw new Exception("Malformed reponse body. ". $responseBody, Exception::MALFORMED_RESPONSE);
        }
        $this->multicastId = $data['multicast_id'];
        $this->failure = $data['failure'];
        $this->success = $data['success'];
        $this->canonicalIds = $data['canonical_ids'];
        $this->results = array();
        
        foreach ($message->getRegistrationIds() as $key => $registrationId) {
            $this->results[$registrationId] = $data['results'][$key];
        }
    }
    
    public function getRawResponse() {
        return $this->responseBody;
    }
    
    public function getResponseHeadersArray() {
        return $this->responseHeaders;
    }
    
    public function getMulticastId()
    {
        return $this->multicastId;
    }
	
    public function getMustRetry()
    {
        return $this->mustRetry;
    }
	
    public function getWaitSeconds()
    {
        return $this->waitSeconds;
    }

    public function getSuccessCount()
    {
        return $this->success;
    }
    
    public function getTotallyNormalCount()
    {
        return($this->success - $this->canonicalIds);
    }    
    
    public function getFailureCount() //both implementation errors and server errors are included here.
    {
        return $this->failure;
    }

    public function getNewRegistrationIdsCount()
    {
        return $this->canonicalIds;
    }
    
    public function isTotallyNormal()
    {
        return (($this->getNewRegistrationIdsCount() == 0) && ($this->getFailureCount() == 0));
    }

    public function getResults()
    {
        return $this->results;
    }
    
    /**
     * Return a numeric array of registration ids which worked without any error or need to update.
     */
    
    public function getAsWrittenIds()
    {        
        $filteredResults = array_filter($this->results,
            function($result) {
                return isset($result['message_id']) && !isset($result['registration_id']);
            });

        return array_keys($filteredResults);
    }
    
    /**
     * Return an array of expired registration ids linked to new id
     * All old registration ids must be updated to new ones in DB
     *
     * @return array oldRegistrationId => newRegistrationId
     */
    public function getNewRegistrationIds()
    {
        if ($this->getNewRegistrationIdsCount() == 0) {
            return array();
        }
        $filteredResults = array_filter($this->results,
            function($result) {
                return isset($result['registration_id']);
            });

        $data = array_map(function($result) {
                return $result['registration_id'];
            }, $filteredResults);

        return $data;
    }
    
    public function someIdsNew()
    {
        $bool = (count($this->getNewRegistrationIds()) != 0);
        return $bool;
    }

    /**
     * Returns an array containing invalid registration ids
     * They must be removed from DB because the application was uninstalled from the device.
     *
     * @return array
     */
    public function getInvalidRegistrationIds()
    {
        if ($this->getFailureCount() == 0) {
            return array();
        }
        $filteredResults = array_filter($this->results,
            function($result) {
                return (
                    isset($result['error'])
                    &&
                    (
                    ($result['error'] == "NotRegistered")
                    ||
                    ($result['error'] == "InvalidRegistration")                    
                    ||
                    ($result['error'] == "DeviceMessageRateExceeded")
                    )
                    );
            });

        return array_keys($filteredResults);
    }

    public function someIdsInvalid()
    {
        $bool = (count($this->getInvalidRegistrationIds()) != 0);
        return $bool;
    }

    /**
     * Returns an array of registration ids for which you must resend a message (?),
     * cause devices aren't available now.
     *
     * @TODO: check if it be auto sended later
     *
     * @return array
     */
    public function getUnavailableRegistrationIds()
    {
        if ($this->getFailureCount() == 0) {
            return array();
        }
        $filteredResults = array_filter($this->results,
            function($result) {
                return (
                    isset($result['error'])
                    &&
                    (
                    ($result['error'] == "Unavailable")
                    ||
                    ($result['error'] == "InternalServerError")
                    )
                    );
            });

        return array_keys($filteredResults);
    }
    
    public function someIdsUnavailable() {
        $bool = (count($this->getUnavailableRegistrationIds()) != 0);
        return $bool;
    }

    //invalid data key
    
    public function getInvalidDataKeysIds() {
        if ($this->getFailureCount() == 0) {
            return array();
        }
        $filteredResults = array_filter($this->results,
            function($result) {
                return (
                    isset($result['error'])
                    &&
                    ($result['error'] == "InvalidDataKey")
                    );
            });

        return array_keys($filteredResults);
    
    }
    
    public function existsInvalidDataKey() {
        $bool = (count($this->getInvalidDataKeysIds()) != 0);
        return $bool;
    }
    
    //mismatch sender id
    
    public function getMismatchSenderIdIds() {
        if ($this->getFailureCount() == 0) {
            return array();
        }
        $filteredResults = array_filter($this->results,
            function($result) {
                return (
                    isset($result['error'])
                    &&
                    ($result['error'] == "MismatchSenderId")
                    );
            });

        return array_keys($filteredResults);
    }
    
    public function existsMismatchSenderId() {
        $bool = (count($this->getMismatchSenderIdIds()) != 0);
        return $bool;
    }

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
