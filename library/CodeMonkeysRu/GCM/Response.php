<?php
namespace CodeMonkeysRu\GCM;

/**
 * Class Response
 *
 * @package CodeMonkeysRu\GCM
 * @author Vladimir Savenkov <ivariable@gmail.com>
 * @author Steve Tauber <taubers@gmail.com>
 */
class Response {

    /**
     * Unique ID (number) identifying the multicast message.
     *
     * @var integer
     */
    protected $multicastId = null;

    /**
     * Number of messages that were processed without an error.
     *
     * @var integer
     */
    protected $success = null;

    /**
     * Number of messages that could not be processed.
     *
     * @var integer
     */
    protected $failure = null;

    /**
     * Number of results that contain a canonical registration ID.
     *
     * @var integer
     */
    protected $canonicalIds = null;

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
    protected $results = array();

    /**
     * Array of IDs grouped by failure.
     *
     * @var array
     */
    protected $failedIds = array();

    /**
     * Array of expired IDs and their new counterpart.
     *
     * @var array
     */
    protected $newRegistrationIds = array();

    /**
     * Constructor
     *
     * @param Message   $message Original message
     * @param \stdClass $responseBody Response from Curl.
     */
    public function __construct(Message $message, \stdClass $responseBody) {
        $this->multicastId = $responseBody->multicast_id;
        $this->success = $responseBody->success;
        $this->failure = $responseBody->failure;
        $this->canonicalIds = $responseBody->canonical_ids;
        $this->results = array();
        $sentIds = $message->getRegistrationIds();
        foreach ($responseBody->results as $k => $v) {
            $id = $sentIds[$k];
            //Convert from stdClass to assoc array
            $array = get_object_vars($v);
            $this->results[$id] = $array;
            //New Registration IDs
            if(isset($array['registration_id'])) {
                $this->newRegistrationIds[$id] = $array['registration_id'];
            }
            //Failures
            if(isset($array['error'])) {
                $this->failedIds[$array['error']][$id] = $array;
            }
        }
    }

    public function getMulticastId() {
        return $this->multicastId;
    }

    public function getSuccessCount() {
        return $this->success;
    }

    public function getFailureCount() {
        return $this->failure;
    }

    public function getCanonicalIds() {
        return $this->canonicalIds;
    }

    public function getResults() {
        return $this->results;
    }

    public function getNewRegistrationIds() {
        return $this->newRegistrationIds;
    }

    public function getFailedIds() {
       return $this->failedIds;
    }
}