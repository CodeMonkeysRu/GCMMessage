<?php
namespace CodeMonkeysRu\GCM;
use Curl\Curl;

/**
 * Messages sender to GCM servers
 *
 * @package CodeMonkeysRu\GCM
 * @author Vladimir Savenkov <ivariable@gmail.com>
 * @author Steve Tauber <taubers@gmail.com>
 */
class Sender
{

    /**
     * Max retry time for exponential back off.
     */
    const MAX_RETRY_TIME = 32;

    /**
     * Send message to GCM
     *
     * @param Message $message The Message to send.
     * @param string  $serverApiKey Server API Key.
     * @param string  $gcmUrl GCM URL.
     * @param integer $nextDelay Next exponential back off delay.
     *
     * @return Response
     * @throws Exception When
     */
    public static function send(Message $message, $serverApiKey, $gcmUrl, $nextDelay = 1) {
        $curl = self::initCurl();
        self::configCurl($curl, $serverApiKey);
        self::postCurl($curl, $gcmUrl, $message->toJson());
        self::closeCurl($curl);

        if ($curl->error) {
            switch($curl->http_status_code) {
                case 400:
                    throw new Exception('GCM\Sender->send - Malformed Request: ' . $curl->raw_response, Exception::MALFORMED_REQUEST);
                    break;
                case 401:
                    throw new Exception('GCM\Sender->send - Authentication Error', Exception::AUTHENTICATION_ERROR);
                    break;
                default:
                    $retry = $curl->response_headers['retry-after'];
                    if($retry) {
                        if((int) $retry) {
                            //in seconds: 120
                            $retry = \DateTime::createFromFormat('U', strtotime('now +' . (int) $retry . ' seconds'));
                        } else {
                            //absolute: Fri, 31 Dec 1999 23:59:59 GMT
                            $retry = \DateTime::createFromFormat('U', strtotime($retry));
                        }
                        return $retry;
                    } else {
                        //Timeout
                        if($curl->http_status_code >= 501 && $curl->http_status_code <= 599) {
                            if($nextDelay < self::MAX_RETRY_TIME) {
                                return $nextDelay;
                            }
                        }
                        throw new Exception('GCM\Sender->send - Unknown Error: ' . $curl->raw_response, Exception::UNKNOWN_ERROR);
                    }
                    break;
            }
        }

        return new Response($message, $curl->response);
    }

    /**
     * Init Curl.
     *
     * @return Curl
     */
    protected static function initCurl() {
        return new Curl();
    }

    /**
     * Configure Curl.
     *
     * @param Curl $curl
     * @param string $serverApiKey
     */
    protected static function configCurl(&$curl, $serverApiKey) {
        $curl->setUserAgent('CodeMonkeysRu\GCMMessage');
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, 1);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, 2);
        $curl->setHeader('Authorization', 'key=' . $serverApiKey);
        $curl->setHeader('Content-Type', 'application/json');
    }

    /**
     * Post message.
     *
     * @param Curl $curl
     * @param string $gcmUrl
     * @param string $json
     */
    protected static function postCurl(&$curl, $gcmUrl, $json) {
        $curl->post($gcmUrl, $json);
    }

    /**
     * Close.
     *
     * @param Curl $curl
     */
    protected static function closeCurl(&$curl) {
        $curl->close();
    }

}
