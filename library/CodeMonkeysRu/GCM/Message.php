<?php
namespace CodeMonkeysRu\GCM;

/**
 * Class Message
 *
 * @package CodeMonkeysRu\GCM
 * @author Vladimir Savenkov <ivariable@gmail.com>
 * @author Steve Tauber <taubers@gmail.com>
 * TODO: Add notification_key when it's stable and working.
 */
class Message {

    /**
     * Max size for data.
     */
    const MAX_SIZE = 4096;

    /**
     * Max TTL.
     */
    const MAX_TTL = 2419200;

    /**
     * Min TTL.
     */
    const MIN_TTL = 0;

    /**
     * Max Registration IDs.
     */
    const MAX_REG_IDS = 1000;

    /**
     * A string array with the list of devices (registration IDs) receiving the message.
     * It must contain at least 1 and at most 1000 registration IDs.
     *
     * Required.
     *
     * @var array
     */
    protected $registrationIds = array();

    /**
     * An arbitrary string (such as "Updates Available") that is used to collapse a group of like messages
     * when the device is offline, so that only the last message gets sent to the client.
     * This is intended to avoid sending too many messages to the phone when it comes back online.
     * Note that since there is no guarantee of the order in which messages get sent, the "last" message
     * may not actually be the last message sent by the application server.
     *
     * Optional.
     *
     * @var string|null
     */
    protected $collapseKey = null;

    /**
     * Message payload data.
     * If present, the payload data it will be included in the Intent as application data,
     * with the key being the extra's name.
     *
     * Optional.
     *
     * @var array|null
     */
    protected $data = null;

    /**
     * Indicates that the message should not be sent immediately if the device is idle.
     * The server will wait for the device to become active, and then only the last message
     * for each collapse_key value will be sent.
     *
     * Optional.
     *
     * @var boolean
     */
    protected $delayWhileIdle = true;

    /**
     * How long (in seconds) the message should be kept on GCM storage if the device is offline.
     *
     * Optional (default time-to-live is 4 weeks).
     *
     * @var int
     */
    protected $timeToLive = null;

    /**
     * A string containing the package name of your application.
     * When set, messages will only be sent to registration IDs that match the package name.
     *
     * Optional.
     *
     * @var string|null
     */
    protected $restrictedPackageName = null;

    /**
     * Allows developers to test their request without actually sending a message.
     *
     * Optional.
     *
     * @var boolean
     */
    protected $dryRun = false;

    /**
     * Constructor.
     *
     * @param array $registrationIds
     */
    public function __construct(array $registrationIds) {
        $this->registrationIds = $registrationIds;
    }

    /**
     * Create a Message object from array.
     *
     * @param array $array Array of params to set on the object.
     *
     * @return Message
     * @throws Exception When required params not sent.
     */
    public static function fromArray(array $array) {
        $return = null;
        if (isset($array['registration_ids']) && is_array($array['registration_ids'])) {
            $return = new Message($array['registration_ids']);
            unset($array['registration_ids']);
            foreach ($array as $k => $v) {
                $methodName = 'set' . preg_replace('/(?:^|_)(.?)/e', "strtoupper('$1')", $k);
                if (method_exists($return, $methodName)) {
                    $return->$methodName($v);
                }
            }
            return $return;
        } else {
            throw new Exception('GCM\Client::fromArray - Invalid or Missing Registration IDs: ' . print_r($array, true) , Exception::INVALID_PARAMS);
        }
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    public function toArray() {
        $return = array(
            'registration_ids' => $this->registrationIds,
            'collapse_key' => $this->collapseKey,
            'delay_while_idle' => $this->delayWhileIdle,
            'time_to_live' => $this->timeToLive,
            'restricted_package_name' => $this->restrictedPackageName,
            'dry_run' => $this->dryRun,
            'data' => $this->data
        );
        return $return;
    }

    /**
     * Convert to a JSON string.
     *
     * @return string
     */
    public function toJson() {
        return json_encode($this->toArray());
    }

    /**
     * To String.
     *
     * @return string
     */
    public function __toString() {
        return json_encode($this->toArray());
    }

    /**
     * Get Registration IDs.
     *
     * @return array
     */
    public function getRegistrationIds() {
        return $this->registrationIds;
    }

    /**
     * Set Registration IDs.
     *
     * @param array $registrationIds
     *
     * @return $this
     * @throws Exception When invalid number of Registration IDs.
     */
    public function setRegistrationIds(array $registrationIds) {
        $count = count($registrationIds);
        if (!$count || $count > self::MAX_REG_IDS) {
            throw new Exception('GCM\Client->setRegistrationIds - Must contain 1-1000 (inclusive) Registration IDs. Count: ' . $count, Exception::MALFORMED_REQUEST);
        }
        $this->registrationIds = $registrationIds;
        return $this;
    }

    public function getCollapseKey() {
        return $this->collapseKey;
    }

    public function setCollapseKey($collapseKey) {
        $this->collapseKey = $collapseKey;
        return $this;
    }

    public function getData() {
        return $this->data;
    }

    /**
     * Set Data.
     *
     * @param array $data Data to send.
     *
     * @return $this
     * @throws Exception When encoded JSON exceeds MAX_SIZE bytes.
     */
    public function setData(array $data) {
        if (strlen(json_encode($data)) > Message::MAX_SIZE) {
            throw new Exception('GCM\Client->setData - Data payload exceeds limit (max ' . Message::MAX_SIZE .' bytes)', Exception::MALFORMED_REQUEST);
        }
        $this->data = $data;
        return $this;
    }

    public function getDelayWhileIdle() {
        return $this->delayWhileIdle;
    }

    public function setDelayWhileIdle($delayWhileIdle) {
        $this->delayWhileIdle = $delayWhileIdle;
        return $this;
    }

    public function getTimeToLive() {
        return $this->timeToLive;
    }

    /**
     * Set TTL.
     *
     * @param null|integer $timeToLive Time to Live.
     *
     * @return $this
     * @throws Exception When TTL is not null|integer OR TTL is not within range
     */
    public function setTimeToLive($timeToLive) {
        if(!is_null($timeToLive) && !is_numeric($timeToLive)) {
            throw new Exception('GCM\Client->setTimeToLive - Invalid TimeToLive: ' . $timeToLive, Exception::INVALID_TTL);
        } else if(is_numeric($timeToLive) && ($timeToLive < self::MIN_TTL || $timeToLive > self::MAX_TTL)) {
            throw new Exception('GCM\Client->setTimeToLive - TimeToLive must be between '
                . self::MIN_TTL . ' and ' . self::MAX_TTL . '. Value: ' . $timeToLive, Exception::OUTSIDE_TTL);
        }
        $this->timeToLive = $timeToLive;
        return $this;
    }

    public function getRestrictedPackageName() {
        return $this->restrictedPackageName;
    }

    public function setRestrictedPackageName($restrictedPackageName) {
        $this->restrictedPackageName = $restrictedPackageName;
        return $this;
    }

    public function getDryRun() {
        return $this->dryRun;
    }

    public function setDryRun($dryRun) {
        $this->dryRun = $dryRun;
        return $this;
    }

}