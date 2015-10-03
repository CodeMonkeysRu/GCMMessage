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
     * it is possible to look at information on all values 
     * on the site https://developers.google.com/cloud-messaging/http-server-ref
     */
    
    private $registration_ids;

    private $collapse_key;
    
    private $priority;
    
    private $content_available;
    
    private $delay_while_idle;
    
    private $time_to_live;
    
    private $restricted_package_name;
    
    private $dry_run;    

    private $data;

    private $notification;


    /**
     * @param array $registrationIds
     * @param mixed $data
     * @param string $collapseKey
     * @param string $priority Only value "normal" and "high"
     * @param integer $timeToLive Max value 2419200
     * @param array $notification
     * @param boolean $contentAvailable
     * @param boolean $delayWhileIdle
     * @param string $restrictedPackageName
     * @param boolean $dryRun
     * @throws Exception
     */
    public function __construct($registrationIds, $data = null, $collapseKey = null)
    {
        $inData = func_get_args();
        $inData = array_merge($inData, array_fill(0, 10, null));
        list(
            $registrationIds,
            $this->data,
            $this->collapse_key,
            $this->priority,
            $this->time_to_live,
            $this->notification,
            $this->content_available,
            $this->delay_while_idle,
            $this->restricted_package_name,
            $this->dry_run
        ) = $inData;
        if (empty($registrationIds)) {
            throw new Exception('Registration_ids is required and should be not empty', Exception::MALFORMED_REQUEST);
        } else {
            $this->registration_ids = $registrationIds;
        }
    }
    
    /**
     * A string array with the list of devices (registration IDs) receiving the message.
     * It must contain at least 1 and at most 1000 registration IDs.
     *
     * Required.
     * 
     * @return array
     */
    public function getRegistrationIds()
    {
        return $this->registration_ids;
    }
    
    /**
     * An arbitrary string (such as "Updates Available") that is used to collapse a group of like messages
     * when the device is offline, so that only the last message gets sent to the client.
     * This is intended to avoid sending too many messages to the phone when it comes back online.
     * Note that since there is no guarantee of the order in which messages get sent, the "last" message
     * may not actually be the last message sent by the application server.
     *
     * Optional.
     *
     * @return string
     */
    public function getCollapseKey()
    {
        return $this->collapse_key;
    }
    
    /**
     * Gets the priority of the message. Valid values are "normal" and "high".
     * On iOS, these correspond to APNs priority 5 and 10.
     * 
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }
   
    /**
     * On iOS, use this field to represent content-available in the APNS payload.
     * When a notification or message is sent and this is set to true, an inactive client app is awoken.
     * On Android, data messages wake the app by default. On Chrome, currently not supported.
     * 
     * @return boolean
     */
    public function getContentAvailable()
    {
        return $this->content_available;
    }
    
    /**
     * When this parameter is set to true, it indicates that the message should not be sent
     * until the device becomes active. 
     * 
     * The default value is false.
     * 
     * @return boolean
     */
    public function getDelayWhileIdle()
    {
        return $this->delay_while_idle;
    }
    
    /**
     * How long (in seconds) the message should be kept on GCM storage if the device is offline.
     *
     * Optional (default time-to-live is 4 weeks).
     * 
     * @return integer
     */
    public function getTimeToLive()
    {
        return $this->time_to_live;
    }
    
    /**
     * A string containing the package name of your application.
     * When set, messages will only be sent to registration IDs that match the package name.
     *
     * Optional.
     * 
     * @return string
     */
    public function getRestrictedPackageName()
    {
        return $this->restricted_package_name;
    }
    
    /**
     * Allows developers to test their request without actually sending a message.
     *
     * Optional.
     * 
     * @return boolean
     */
    public function getDryRun()
    {
        return $this->dry_run;
    }
    
    /**
     * Message payload data.
     * If present, the payload data it will be included in the Intent as application data,
     * with the key being the extra's name.
     *
     * Optional.
     * 
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * This parameter specifies the predefined, user-visible key-value pairs 
     * of the notification payload. See Notification payload support for detail.
     * For more information about notification message and data message options,
     * see https://developers.google.com/cloud-messaging/concept-options#notifications_and_data_messages
     * 
     *  @return array
     */
    public function getNotification()
    {
        return $this->notification;
    }
    
    /**
     * Returns the current object in the form of the array
     * 
     * @return array
     */
    public function asArray()
    {
        $arr = array();
        foreach ($this as $key => $val) {
            if(isset($val)) {
                $arr[$key] = $val;
            }
        }
        return $arr;
    }
    
    /**
     * Returns the current object in the form of the json
     * 
     * @return string JSON
     */
    public function asJson()
    {
        return json_encode($this->asArray());
    }
}