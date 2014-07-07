<?php

namespace CodeMonkeysRu\GCM;

/**
 * Class DefaultSendJob
 *
 * @package CodeMonkeysRu\GCM
 * @author Steve Tauber <taubers@gmail.com>
 */
abstract class DefaultSendJob {

    public $job;

    public $args;

    public $queue;

    /**
     * @var Response
     */
    public $response;

    public function perform() {
        if(isset($this->args['delay'])) {
            $now = new \DateTime();
            $delay = \DateTime::createFromFormat('U', $this->args['delay']);
            if($delay > $now) {
                Client::enqueueFromJobArgs($this->args);
                return;
            }
        }

        $response = Sender::send(
            Message::fromArray($this->args['message']),
            $this->args['serverApiKey'],
            $this->args['gcmUrl'],
            isset($this->args['nextDelay']) ? $this->args['nextDelay'] : 1
        );

        /**
         * Response can be:
         *  - DateTime: When to next retry sending the message.
         *  - Response: Valid response that must be processed.
         *  - Integer:  Exponential Back off.
         */
        if($response instanceof \DateTime) {
            $this->args['delay'] = $response->format('U');
            Client::enqueueFromJobArgs($this->args);
        } elseif($response instanceof Response) {
            $failed = $response->getFailedIds();
            foreach($failed as $error => $group) {
                switch($error) {
                    case 'Unavailable':
                        $message = $this->args['message'];
                        $message['registration_ids'] = array();
                        foreach($group as $id => $item) {
                            $message['registration_ids'][] = $id;
                        }
                        Client::enqueueFromJobArgs(array(
                            'serverApiKey' => $this->args['serverApiKey'],
                            'gcmUrl' => $this->args['gcmUrl'],
                            'message' => $message
                        ));
                        break;
                    case 'InternalServerError':
                        foreach($group as $item) {
                            throw new Exception('GCM\DefaultSendJob->perform - Unknown Error: ' . $item, Exception::UNKNOWN_ERROR);
                        }
                        break;
                    default:
                        /**
                         * The following error messages should remove the Registration IDs from records.
                         *  - InvalidRegistration
                         *  - NotRegistered
                         */

                        /**
                         * The following error messages are malformed requests:
                         *  - DeviceQuotaExceeded
                         *  - InvalidDataKey
                         *  - InvalidPackageName
                         *  - MismatchSenderId
                         *  - MissingRegistration
                         *  - QuotaExceeded
                         */

                        /**
                         * The follow error messages should never occur since they are explicitly tested for:
                         *  - InvalidTtl
                         *  - MessageTooBig
                         */
                        break;
                }
            }
            $this->response = $response;
        } elseif(is_numeric($response)) {
            $this->args['delay'] = \DateTime::createFromFormat('U', strtotime('now +' . (int) $response . ' seconds'));
            $this->args['nextDelay'] = $response * 2;
            Client::enqueueFromJobArgs($this->args);
        }
    }

    /**
     * Check for invalid ids and remove from records.
     * Check for new registered Ids and update in DB.
     * Handle malformed requests.
     *
     * Example:
     *
     *   $failed = $this->response->getFailedIds();
     *   foreach($failed['NotRegistered'] as $f) { ... }
     *
     * Example:
     *   $newIds = $this->response->getNewRegistrationIds();
     *   foreach($newIds as $n) { ... }
     *
     * @return mixed
     */
    public abstract function tearDown();

}