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
        $this->validatePayloadSize($rawData, 'data', 4096);
        $this->validatePayloadSize($rawData, 'notification', 2048);
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
        
        curl_setopt($ch, CURLOPT_HEADER, 1); // return HTTP headers with response
        
        
        if ($this->caInfoPath !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_CAINFO, $this->caInfoPath);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $resp = curl_exec($ch);
 
        if ($resp === FALSE) {
            throw new Exception('cURL error: '. curl_error($ch), Exception::CURL_ERROR);
         
        }
        
        list($responseHeaders, $resultBody) = explode("\r\n\r\n", $resp, 2);
        // $headers now has a string of the HTTP headers
        // $resultBody is the body of the HTTP response

        $responseHeaders = explode("\n", $responseHeaders);
        
        $resultHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);
        
        if ($resultHttpCode == "200") {
            ;//check for errors wrt individual devices later with the Response object.
        }
        
        elseif ($resultHttpCode == "400") {
            throw new Exception('Malformed request. '. $resultBody, Exception::MALFORMED_REQUEST);
        }
        
        elseif ($resultHttpCode == "401") {
            throw new Exception('Authentication Error. '. $resultBody, Exception::AUTHENTICATION_ERROR);
        }
        
        elseif ($resultHttpCode == "500") {
            throw new Exception('Internal Server Error. ' . $resultBody, Exception::INTERNAL_SERVER_ERROR);
        }
        
        elseif ((600 > (int) $resultHttpCode) && ((int) $resultHttpCode > 500)) {
            throw new Exception('Service Unavailable. ' . $resultBody, Exception::SERVICE_UNAVAILABLE);
        }
        
        else {
            throw new Exception("Unknown error. ". $resultBody, Exception::UNKNOWN_ERROR);
        }
        
       
        
        return new Response($message, $resultBody, $responseHeaders);
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
            'content_available' => 'getContentAvailable'
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
    private function validatePayloadSize(array $rawData, $fieldName, $maxSize)
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

/*
Copyright (c) 2014 Vladimir Savenkov

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
