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
    private $responseHeaders = [];
	
    /**
     * Did Google demand that we try again.
     *
     * @var boolean
     */
    private $mustRetry = false;
	
    /**
     * Number of seconds to wait.
     *
     * @var integer
     */
    private $waitSeconds = null;
	
    /**
     * Did you use a reserved data key?
     *
     * @var boolean
     */
    private $existsInvalidDataKey = false;
	
    /**
     * Did one of your clients register with the wrong senderId?
     * If one of them did, then presumably they all did.
     * 
     * @var boolean
     */
    private $existsMismatchSenderId = false;

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
     *             The possible values are the same as documented in the above table, plus "Unavailable"
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
        $this->existsInvalidDataKey = false;
        $this->existsMismatchSenderId = false;
        $this->results = array();
        
        foreach ($message->getRegistrationIds() as $key => $registrationId) {
            $result = $data['results'][$key];
            if (isset($result['error'])) {
	        switch ($result['error']) {
		    case "InvalidDataKey":
		        $this->existsInvalidDataKey = true;
			break;
		    case "MismatchSenderId":
		        $this->existsMismatchSenderId = true;
			break;
		    default:
		        break;
	        }
	    }
	    $this->results[$registrationId] = $result; 
        }
        $result = null;
    }
    
    public function getResponseHeaders() {
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
    
    /**
     * Both implementation errors and server errors are included here.
     *
     * @return integer
     */
    public function getFailureCount()
    {
        return $this->failure;
    }
	
    public function getExistsInvalidDataKey()
    {
        return $this->existsInvalidDataKey;
    }
	
    public function getExistsMismatchSenderId()
    {
        return $this->existsMismatchSenderId;
    }

    public function getNewRegistrationIdsCount()
    {
        return $this->canonicalIds;
    }
    
    public function getResults()
    {
        return $this->results;
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
                    )
                    );
            });

        return array_keys($filteredResults);
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
                    ||
                    ($result['error'] == "DeviceMessageRateExceeded")
                    )
                    );
            });

        return array_keys($filteredResults);
    }
	
    /**
     * Returns an array of registration ids who registered
     * for pushes using the wrong senderId.
     *
     * @return array
     */
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
}
