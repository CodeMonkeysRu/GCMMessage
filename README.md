Google Cloud Messaging (GCM) PHP Server Library
--------------------------------------------

A PHP library for sending messages to devices registered through Google Cloud Messaging.

Requirements:
 - PHP >=5.3.2
 - Redis database

Libraries used:
 - chrisboulton/php-resque 1.2.x (MIT)
 - php-curl-class/php-curl-class 2.1.x (No License)

See:
http://developer.android.com/guide/google/gcm/index.html

Example usage
-----------------------

```php
<?php
use \CodeMonkeysRu\GCM;

class YourClass {

    public function someFunction() {
        /* The second param is our class that extends DefaultSendJob.php */
        GCM\Client::configure("YOUR GOOGLE API KEY", 'MyQueueJob');

        $message = GCM\Message::fromArray(array(
            'registration_ids' => array('device_registration_id1', 'device_registration_id2'),
            'data' => array('data1' => 123, 'data2' => 'string'),
        ));

        /* This can all be set in the original fromArray call. */
        $message
            ->setCollapseKey('collapse_key')
            ->setDelayWhileIdle(true)
            ->setTimeToLive(123)
            ->setRestrictedPackageName("com.example.trololo")
            ->setDryRun(true);
        
        /* Enqueues the message. php-resque will process via a worker. */
        GCM\Client::send($message);
    }

}
```

MyQueueJob.php
```php
<?php
class MyQueueJob extends \CodeMonkeysRu\GCM\DefaultSendJob {

    /* See DefaultSendJob for all the possible statuses */
    public function tearDown() {
        if($this->response) {
            $failed = $this->response->getFailedIds();
            if(!empty($failed['InvalidRegistration'])) {
                foreach($failed['InvalidRegistration'] as $f) {
                    //remove from DB records
                }
            }
            if(!empty($failed['NotRegistered'])) {
                foreach($failed['NotRegistered'] as $f) {
                    //remove from DB records
                }
            }

            $newIds = $this->response->getNewRegistrationIds();
            if(!empty($newIds)) {
                foreach($newIds as $n) {
                    //Update DB records
                }
            }
        }
    }
}

```

job_app_include.php
```php
<?php
require_once "vendor/autoload.php"
require_once "MyQueueJob.php"
```

Command line:
```bash
$ QUEUE=gcmDefault LOGGING=2 APP_INCLUDE=job_app_include.php php vendor/chrisboulton/php-resque/resque.php
```


ChangeLog
----------------------
* v0.1 - Initial release

Licensed under MIT license.
