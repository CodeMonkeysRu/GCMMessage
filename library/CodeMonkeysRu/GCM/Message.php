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
     * Specifies the recipient of a message
     *
     * How to use:
     * String - message topic if sent for all devices
     * String - registration token/device id if message is sent to one device
     * Array - list of registration tokens/device ids if message is sent to multiple devices
     *          It must contain at least 1 and at most 1000 registration IDs.
     *
     * Required.
     *
     * @var string|array
     */
    private $recipients = '';

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
     * Allows developers to test their request without actually sending a message.
     *
     * Optional.
     *
     * @var boolean
     */
    private $dryRun = false;

    /**
     * On iOS, use this field to represent content-available in the APNS payload. When a notification or message is
     * sent and this is set to true, an inactive client app is awoken. On Android, data messages wake the app by
     * default. On Chrome, currently not supported.
     *
     * Optional
     *
     * @var bool
     */
    private $contentAvailable = true;

    /**
     * Sets the priority of the message. Valid values are "normal" and "high." On iOS, these correspond to APNs
     * priority 5 and 10.
     *
     * Optional
     *
     * @var string
     */
    private $priority = 'high';

    /**
     * @param string|array $recipients
     * @param array        $data
     * @param null         $collapseKey
     */
    public function __construct($recipients = null, $data = null, $collapseKey = null)
    {

        $this->bulkSet($recipients, $data, $collapseKey);
    }

    /**
     * Set multiple fields at once.
     *
     * @param string|array $recipients
     * @param array|null   $data
     * @param string|null  $collapseKey
     */
    public function bulkSet($recipients = '', $data = null, $collapseKey = null)
    {

        $this->setRecipients($recipients);
        $this->setData($data);
        $this->setCollapseKey($collapseKey);
    }

    /**
     * @return array|string
     */
    public function getRecipients()
    {

        return $this->recipients;
    }

    /**
     * @param string|array $recipients
     *
     * @return $this
     */
    public function setRecipients($recipients)
    {

        $this->recipients = $recipients;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getCollapseKey()
    {

        return $this->collapseKey;
    }

    /**
     * @param string $collapseKey
     *
     * @return $this
     */
    public function setCollapseKey($collapseKey)
    {

        $this->collapseKey = $collapseKey;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getData()
    {

        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {

        $this->data = $data;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getNotification()
    {

        return $this->notification;
    }

    /**
     * @param array $notification
     *
     * @return $this
     */
    public function setNotification($notification)
    {

        $this->notification = $notification;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDelayWhileIdle()
    {

        return $this->delayWhileIdle;
    }

    /**
     * @param bool $delayWhileIdle
     *
     * @return $this
     */
    public function setDelayWhileIdle($delayWhileIdle)
    {

        $this->delayWhileIdle = $delayWhileIdle;
        return $this;
    }

    /**
     * @return int
     */
    public function getTtl()
    {

        return $this->ttl;
    }

    /**
     * @param int $ttl
     *
     * @return $this
     */
    public function setTtl($ttl)
    {

        $this->ttl = $ttl;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getRestrictedPackageName()
    {

        return $this->restrictedPackageName;
    }

    /**
     * @param string $restrictedPackageName
     *
     * @return $this
     */
    public function setRestrictedPackageName($restrictedPackageName)
    {

        $this->restrictedPackageName = $restrictedPackageName;
        return $this;
    }

    /**
     * @return bool
     */
    public function getDryRun()
    {

        return $this->dryRun;
    }

    /**
     * @param bool $dryRun
     *
     * @return $this
     */
    public function setDryRun($dryRun)
    {

        $this->dryRun = $dryRun;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getbbContentAvailable()
    {

        return $this->contentAvailable;
    }

    /**
     * @param boolean $contentAvailable
     */
    public function setContentAvailable($contentAvailable)
    {

        $this->contentAvailable = $contentAvailable;
    }

    /**
     * @return string
     */
    public function getPriority()
    {

        return $this->priority;
    }

    /**
     * @param string $priority
     */
    public function setPriority($priority)
    {

        $this->priority = $priority;
    }
}
