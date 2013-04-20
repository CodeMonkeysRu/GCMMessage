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
     * An API key that gives the application server authorized access to Google services.
     *
     * @var string
     */
    private $serverApiKey = false;

    public function __construct($serverApiKey, $gcmUrl = false)
    {
        $this->serverApiKey = $serverApiKey;
        if ($gcmUrl) {
            $this->gcmUrl = $gcmUrl;
        }
    }

    /**
     * Send message to GCM without explicitly created message
     *
     * @param mixed same params as in Message bulkSet
     *
     * @throws \UnexpectedValueException
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
     * @throws \UnexpectedValueException
     * @return \CodeMonkeysRu\GCM\Response
     */
    public function send(Message $message)
    {

        if (!$this->serverApiKey) {
            throw new Exception("Server API Key not set", Exception::ILLEGAL_API_KEY);
        }

        $rawData = $this->formMessageData($message);
        if(isset($rawData['data'])){
            if(strlen(json_encode($rawData['data'])) > 4096){
                throw new Exception("Data payload is to big (max 4096 bytes)", Exception::MALFORMED_REQUEST);
            }
        }
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

}