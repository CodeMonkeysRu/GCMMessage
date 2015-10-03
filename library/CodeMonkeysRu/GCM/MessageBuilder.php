<?php
namespace CodeMonkeysRu\GCM;

/**
 * Message for GCM server
 *
 * @author Vladimir Savenkov <ivariable@gmail.com>
 */
class MessageBuilder
{

    private $registrationIds;

    private $collapseKey;

    private $priority;

    private $contentAvailable;

    private $delayWhileIdle;

    private $timeToLive;

    private $restrictedPackageName;

    private $dryRun;

    private $data;

    private $notification;

    public function __construct()
    {}

    /**
     * This parameter specifies a list of devices (registration IDs) receiving a multicast message.
     * It must contain at least 1 and at most 1000 registration tokens.
     * 
     * Required.
     * 
     * @param array $array
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setRegistrationIds(array $array)
    {
        $this->registrationIds = $array;
        return $this;
    }

    /**
     * This parameter identifies a group of messages that can be collapsed,
     * so that only the last message gets sent when delivery can be resumed.
     * 
     * @param string $string
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setCollapseKey($string)
    {
        $this->collapseKey = (string) $string;
        return $this;
    }
    
    /**
     * Sets the priority of the message. Valid values are "normal" and "high".
     * On iOS, these correspond to APNs priority 5 and 10.
     * 
     * @param string $string
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setPriority($string)
    {
        if (strcasecmp($string, 'normal') || strcasecmp($string, 'high')) {
            $this->priority = strtolower($string);
        }
        return $this;
    }

    /**
     * On iOS, use this field to represent content-available in the APNS payload.
     * When a notification or message is sent and this is set to true, an inactive client app is awoken.
     * On Android, data messages wake the app by default. On Chrome, currently not supported.
     * 
     * @param boolean $bool
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setContentAvailable($bool)
    {
        $this->contentAvailable = (bool) $bool;
        return $this;
    }

    /**
     * When this parameter is set to true, it indicates that the message should not be sent
     * until the device becomes active. 
     * 
     * The default value is false.
     * 
     * @param boolean $bool
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setDelayWhileIdle($bool)
    {
        $this->delayWhileIdle = (bool) $bool;
        return $this;
    }

    /**
     * This parameter specifies how long (in seconds) the message should be kept in GCM storage 
     * if the device is offline. The maximum time to live supported is 4 weeks.
     * 
     * The default value is 4 weeks. 
     * 
     * @param integer $integer
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setTimeToLive($integer)
    {
        $this->timeToLive = (int) $integer;
        return $this;
    }

    /**
     * This parameter specifies the package name of the application 
     * where the registration tokens must match in order to receive the message.
     * 
     * @param string $string
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setRestrictedPackageName($string)
    {
        $this->restrictedPackageName = (string) $string;
        return $this;
    }

    /**
     * This parameter, when set to true, allows developers to test a request without actually sending a message. 
     * 
     * The default value is false.
     * 
     * @param unknown $bool
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setDryRun($bool)
    {
        $this->dryRun = (bool) $bool;
        return $this;
    }

    /**
     * This parameter specifies the custom key-value pairs of the message's payload.
     * See https://developers.google.com/cloud-messaging/http-server-ref
     * 
     * @param mixed $mixed
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setData($mixed)
    {
        $this->data = $mixed;
        return $this;
    }
    
    /**
     * Indicates notification icon. On Android: sets value to myicon for drawable resource myicon.
     * Only Android.
     * 
     * @param string $string
     */
    public function setNotificationIcon($string)
    {
        $this->notification['icon'] = (string) $string;
        return $this;
    }
    
    /**
     * Indicates sound to be played. Supports only default currently.
     * 
     * @param unknown $string
     */
    public function setNotificationSound($string)
    {
        $this->notification['sound'] = (string) $string;
        return $this;
    }
    
    /**
     * Indicates the badge on client app home icon. Only IOS.
     * 
     * @param string $string
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setNotificationBadge($string)
    {
        $this->notification['badge'] = (string) $string;
        return $this;
    }
    
    /**
     * Indicates whether each notification message results in a new entry on the notification center on Android.
     * If not set, each request creates a new notification.
     * If set, and a notification with the same tag is already being shown,
     * the new notification replaces the existing one in notification center.
     * Only Android. 
     * 
     * @param string $string
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setNotificationTag($string)
    {
        $this->notification['tag'] = (string) $string;
        return $this;
    }
    
    /**
     * Indicates color of the icon, expressed in #rrggbb format. Only Android. 
     * @param string $string
     */
    public function setNotificationColor($string)
    {
        $this->notification['color'] = (string) $string;
        return $this;
    }
    
    /**
     * The action associated with a user click on the notification.
     * 
     * @param string $string
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setNotificationClickAction($string)
    {
        $this->notification['click_action'] = (string) $string;
        return $this;
    }
    
    /**
     * Indicates notification title. This field is not visible on iOS phones and tablets.
     * 
     * Indicates notification body text.
     * 
     * @param string $title
     * @param string $text
     */
    public function setNotificationMessage($title, $text)
    {
        $this->notification['title'] = (string) $title;
        $this->notification['body'] = (string) $text;
        return $this;
    }
    
    /**
     * Indicates the key to the body string for localization. On iOS, this corresponds to "loc-key" in APNS payload. 
     * On Android, use the key in the app's string resources when populating this value.
     * 
     * Indicates the string value to replace format specifiers in body string for localization. 
     * On iOS, this corresponds to "loc-args" in APNS payload.
     * On Android, these are the format arguments for the string resource. For more information, 
     * see http://developer.android.com/guide/topics/resources/string-resource.html#FormattingAndStyling
     * 
     * @param string $key
     * @param array $args
     */
    public function setNotificationBodyLoc($key, $args)
    {
        $this->notification['body_loc_key'] = (string) $key;
        $this->notification['body_loc_args'] = $args;
        return $this;
    }
    
    /**
     * Indicates the key to the title string for localization. On iOS, this corresponds to "title-loc-key" in APNS payload.
     * On Android, use the key in the app's string resources when populating this value.
     * 
     * Indicates the string value to replace format specifiers in title string for localization.
     * On iOS, this corresponds to "title-loc-args" in APNS payload.
     * On Android, these are the format arguments for the string resource. For more information,
     * see http://developer.android.com/guide/topics/resources/string-resource.html#FormattingAndStyling
     * 
     * @param string $key
     * @param array $args
     * @return \CodeMonkeysRu\GCM\MessageBuilder
     */
    public function setNotificationTitleLoc($key, $args)
    {
        $this->notification['title_loc_key'] = (string) $key;
        $this->notification['title_loc_args'] = $args;
        return $this;
    }
    
    /**
     * To construct ready object the message
     * 
     * @return \CodeMonkeysRu\GCM\Message
     */
    public function build()
    {
        return new Message(
            $this->registrationIds,
            $this->data,
            $this->collapseKey,
            $this->priority,
            $this->timeToLive,
            $this->notification,
            $this->contentAvailable,
            $this->delayWhileIdle,
            $this->restrictedPackageName,
            $this->dryRun
        );
    }
}