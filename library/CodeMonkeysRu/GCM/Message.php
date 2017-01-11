<?php
namespace CodeMonkeysRu\GCM;

/**
 * Message for GCM server
 *
 * @author Vladimir Savenkov <ivariable@gmail.com>
 */
class Message
{
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH   = 'high';

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
     * Notification payload.
     * This parameter specifies the key-value pairs of the notification payload.
     * See Notification payload support for more information
     * (https://developers.google.com/cloud-messaging/server-ref#notification-payload-support).
     *
     * Optional.
     *
     * @var array|null
     */
    private $notification = null;

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
     * On iOS, use this field to represent content-available in the APNS payload. When a
     * notification or message is sent and this is set to true, an inactive client app is
     * awoken. On Android, data messages wake the app by default.
     *
     * Optional.
     *
     * @var bool
     */
    private $contentAvailable = true;

    /**
     * Sets the priority of the message. Valid values are "normal" and "high." On iOS, these
     * correspond to APNs priority 5 and 10. By default, messages are sent with normal
     * priority. Normal priority optimizes the client app's battery consumption, and should
     * be used unless immediate delivery is required. For messages with normal priority, the
     * app may receive the message with unspecified delay.
     * When a message is sent with high priority, it is sent immediately, and the app can wake
     * a sleeping device and open a network connection to your server.
     *
     * Optional.
     *
     * @var string
     */
    private $priority = self::PRIORITY_NORMAL;

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

    /**
     * Set multiple fields at once.
     *
     * @param string[] $registrationIds
     * @param array|null $data
     * @param string|null $collapseKey
     */
    public function bulkSet($registrationIds = array(), $data = null, $collapseKey = null)
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

    public function getNotification()
    {
        return $this->notification;
    }

    public function setNotification($notification)
    {
        $this->notification = $notification;
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

    public function getContentAvailable()
    {
        return $this->contentAvailable;
    }

    public function setContentAvailable($contentAvailable)
    {
        $this->contentAvailable = $contentAvailable;
        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority)
    {
        $allowedPriorities = array(self::PRIORITY_HIGH, self::PRIORITY_NORMAL);

        if (!in_array($priority, $allowedPriorities, true)) {
            throw new \InvalidArgumentException(
                'Invalid priority "' . $priority . '", allowed are only these: ' . implode(', ', $allowedPriorities)
            );
        }

        $this->priority = $priority;
        return $this;
    }
}
