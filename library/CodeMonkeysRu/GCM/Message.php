<?php
namespace CodeMonkeysRu\GCM;

/**
 * Message for GCM server
 *
 * @author Vladimir Savenkov <ivariable@gmail.com>
 */
class Message
{

    /**
     * A string array with the list of devices (registration IDs) receiving the message.
     * It must contain at least 1 and at most 1000 registration IDs.
     *
     * Required.
     *
     * @var array
     */
    private $registrationIds = array();

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
    private $collapseKey = null;

    /**
     * Message payload data.
     * If present, the payload data it will be included in the Intent as application data,
     * with the key being the extra's name.
     *
     * Optional.
     *
     * @var array|null
     */
    private $data = null;

    /**
     * Indicates that the message should not be sent immediately if the device is idle.
     * The server will wait for the device to become active, and then only the last message
     * for each collapse_key value will be sent.
     *
     * Optional.
     *
     * @var boolean
     */
    private $delayWhileIdle = false;

    /**
     * How long (in seconds) the message should be kept on GCM storage if the device is offline.
     *
     * Optional (default time-to-live is 4 weeks).
     *
     * @var int
     */
    private $ttl = null;

    /**
     * A string containing the package name of your application.
     * When set, messages will only be sent to registration IDs that match the package name.
     *
     * Optional.
     *
     * @var string|null
     */
    private $restrictedPackageName = null;

    /**
     * Allows developers to test their request without actually sending a message.
     *
     * Optional.
     *
     * @var boolean
     */
    private $dryRun = false;

    public function __construct($registrationIds = null, $data = null, $collapseKey = null)
    {
        $this->bulkSet($registrationIds, $data, $collapseKey);
    }

    public function bulkSet($registrationIds = null, $data = null, $collapseKey = null)
    {
        $this->setRegistrationIds($registrationIds);
        $this->setData($data);
        $this->setCollapseKey($collapseKey);
    }

    public function getRegistrationIds()
    {
        return $this->registrationIds;
    }

    public function setRegistrationIds($registrationIds)
    {
        $this->registrationIds = $registrationIds;
        return $this;
    }

    public function getCollapseKey()
    {
        return $this->collapseKey;
    }

    public function setCollapseKey($collapseKey)
    {
        $this->collapseKey = $collapseKey;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getDelayWhileIdle()
    {
        return $this->delayWhileIdle;
    }

    public function setDelayWhileIdle($delayWhileIdle)
    {
        $this->delayWhileIdle = $delayWhileIdle;
        return $this;
    }

    public function getTtl()
    {
        return $this->ttl;
    }

    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
        return $this;
    }

    public function getRestrictedPackageName()
    {
        return $this->restrictedPackageName;
    }

    public function setRestrictedPackageName($restrictedPackageName)
    {
        $this->restrictedPackageName = $restrictedPackageName;
        return $this;
    }

    public function getDryRun()
    {
        return $this->dryRun;
    }

    public function setDryRun($dryRun)
    {
        $this->dryRun = $dryRun;
        return $this;
    }

}