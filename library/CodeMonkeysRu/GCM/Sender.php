<?php
namespace CodeMonkeysRu\GCM;

/**
 * Messages sender to GCM servers
 *
 * @author Vladimir Savenkov <ivariable@gmail.com>
 */
class Sender
{

    /**
     * GCM endpoint
     *
     * @var string
     */
    private $gcmUrl = 'https://android.googleapis.com/gcm/send';

    /**
     * Path to CA file (due to cURL 7.10 changes; you can get it from here: http://curl.haxx.se/docs/caextract.html)
     * 
     * @var string
     */
    private $caInfoPath = false;

    /**
     * An API key that gives the application server authorized access to Google services.
     *
     * @var string
     */
    private $serverApiKey = false;

    public function __construct($serverApiKey, $gcmUrl = false, $caInfoPath = false)
    {
        $this->serverApiKey = $serverApiKey;
        if ($gcmUrl) {
            $this->gcmUrl = $gcmUrl;
        }
        if ($caInfoPath) {
            $this->caInfoPath = $caInfoPath;
        }
    }

    /**
     * Send message to GCM without explicitly created message
     *
     * @param string[] $registrationIds
     * @param array|null $data
     * @param string|null $collapseKey
     *
     * @throws \CodeMonkeysRu\GCM\Exception
     * @return \CodeMonkeysRu\GCM\Response
     */
    public function sendMessage()
    {
        $message = new \CodeMonkeysRu\GCM\Message();
        call_user_func_array(array($message, 'bulkSet'), func_get_args());
        return $this->send($message);
    }

    /**
     * Send message to GCM
     *
     * @param \CodeMonkeysRu\GCM\Message $message
     * @throws \CodeMonkeysRu\GCM\Exception
     * @return \CodeMonkeysRu\GCM\Response
     */
    public function send(Message $message)
    {

        if (!$this->serverApiKey) {
            throw new Exception("Server API Key not set", Exception::ILLEGAL_API_KEY);
        }

        //GCM response: Number of messages on bulk (1001) exceeds maximum allowed (1000)
        if (count($message->getRegistrationIds()) > 1000) {
            throw new Exception("Malformed request: Registration Ids exceed the GCM imposed limit of 1000", Exception::MALFORMED_REQUEST);
        }

        $rawData = $this->formMessageData($message);
        static::validatePayloadSize($rawData, 'data', 4096);
        static::validatePayloadSize($rawData, 'notification', 2048);
        $data = json_encode($rawData);

        $headers = array(
            'Authorization: key='.$this->serverApiKey,
            'Content-Type: application/json'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->gcmUrl);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($this->caInfoPath !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CAINFO, $this->caInfoPath);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resultBody = curl_exec($ch);
        $resultHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        switch ($resultHttpCode) {
            case "200":
                //All fine. Continue response processing.
                break;

            case "400":
                throw new Exception('Malformed request. '.$resultBody, Exception::MALFORMED_REQUEST);
                break;

            case "401":
                throw new Exception('Authentication Error. '.$resultBody, Exception::AUTHENTICATION_ERROR);
                break;

            default:
                //TODO: Retry-after
                throw new Exception("Unknown error. ".$resultBody, Exception::UNKNOWN_ERROR);
                break;
        }

        return new Response($message, $resultBody);
    }

    /**
     * Form raw message data for sending to GCM
     *
     * @param \CodeMonkeysRu\GCM\Message $message
     * @return array
     */
    private function formMessageData(Message $message)
    {
        $data = array(
            'registration_ids' => $message->getRegistrationIds(),
        );

        $dataFields = array(
            'registration_ids' => 'getRegistrationIds',
            'collapse_key' => 'getCollapseKey',
            'data' => 'getData',
            'notification' => 'getNotification',
            'delay_while_idle' => 'getDelayWhileIdle',
            'time_to_live' => 'getTtl',
            'restricted_package_name' => 'getRestrictedPackageName',
            'dry_run' => 'getDryRun',
        );

        foreach ($dataFields as $fieldName => $getter) {
            if ($message->$getter() != null) {
                $data[$fieldName] = $message->$getter();
            }
        }

        return $data;
    }

    /**
     * Validate size of json representation of passed payload
     *
     * @param array $rawData
     * @param string $fieldName
     * @param int $maxSize
     * @throws \CodeMonkeysRu\GCM\Exception
     * @return void
     */
    private static function validatePayloadSize(array $rawData, $fieldName, $maxSize)
    {
        if (!isset($rawData[$fieldName])) return;
        if (strlen(json_encode($rawData[$fieldName])) > $maxSize) {
            throw new Exception(
                ucfirst($fieldName)." payload is to big (max {$maxSize} bytes)",
                Exception::MALFORMED_REQUEST
            );
        }
    }

}
