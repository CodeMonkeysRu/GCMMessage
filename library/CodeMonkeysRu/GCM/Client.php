<?php

namespace CodeMonkeysRu\GCM;

/**
 * Class Client
 *
 * @package CodeMonkeysRu\GCM
 * @author Steve Tauber <taubers@gmail.com>
 */
class Client {
    /**
     * GCM URL.
     *
     * @var string
     */
    protected static $gcmUrl = 'https://android.googleapis.com/gcm/send';

    /**
     * Queue Name.
     *
     * @var string
     */
    protected static $queueName = 'gcmDefault';

    /**
     * An API key that gives the application server authorized access to Google services.
     *
     * @var string
     */
    protected static $serverApiKey = '';

    /**
     * Class name of the Job that extends DefaultSendJob.
     *
     * @var string
     */
    protected static $sendJob = '';

    /**
     * @param string $serverApiKey An API key that gives the application server authorized access to Google services.
     * @param string $sendJob Class name of the Job that extends DefaultSendJob.
     * @param mixed  $server Host/port combination separated by a colon, DSN-formatted URI, or a nested array of
     *                       servers with host/port pairs.
     * @param int    $database ID of Redis Database to select.
     * @param string $queueName Queue Name
     * @param mixed  $gcmUrl GCM URL.
     */
    public static function configure($serverApiKey, $sendJob, $server = 'localhost:6379', $database = 0, $queueName = null, $gcmUrl = false) {
        \Resque::setBackend($server, $database);

        self::$serverApiKey = $serverApiKey;
        self::$sendJob = $sendJob;

        if($queueName) {
            self::$queueName = $queueName;
        }

        if ($gcmUrl) {
            self::$gcmUrl = $gcmUrl;
        }
    }

    /**
     * @param $args
     */
    public static function enqueueFromJobArgs($args) {
        \Resque::enqueue(
            self::$queueName,
            self::$sendJob,
            $args
        );
    }

    /**
     * Enqueue the message.
     *
     * @param \CodeMonkeysRu\GCM\Message $message Message to send.
     * @param \DateTime|boolean $delay When to send the message.
     */
    public static function send(Message $message, $delay = false) {
        $args = array(
            'serverApiKey' => self::$serverApiKey,
            'gcmUrl' => self::$gcmUrl,
            'message' => $message->toArray()
        );

        if($delay) {
            $args['delay'] = $delay->format('U');
        }

        \Resque::enqueue(
            self::$queueName,
            self::$sendJob,
            $args
        );
    }

    /**
     * @param string $gcmUrl
     */
    public static function setGcmUrl($gcmUrl)
    {
        self::$gcmUrl = $gcmUrl;
    }

    /**
     * @return string
     */
    public function getGcmUrl()
    {
        return self::$gcmUrl;
    }

    /**
     * @param string $queueName
     */
    public function setQueueName($queueName)
    {
        self::$queueName = $queueName;
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return self::$queueName;
    }

    /**
     * @param string $serverApiKey
     */
    public function setServerApiKey($serverApiKey)
    {
        self::$serverApiKey = $serverApiKey;
    }

    /**
     * @return string
     */
    public function getServerApiKey()
    {
        return self::$serverApiKey;
    }
}