Google Cloud Messaging (GCM) PHP Server Library
--------------------------------------------

A PHP library for sending messages to devices registered through Google Cloud Messaging.

See:
http://developer.android.com/guide/google/gcm/index.html

Example usage
-----------------------
```php

use \CodeMonkeysRu\GCM;

$sender = new GCM\Sender("YOUR GOOGLE API KEY");

$message = new GCM\Message(
        array("device_registration_id1", "device_registration_id2"),
        array("data1" => "123", "data2" => "string")
);

$message
    ->setCollapseKey("collapse_key")
    ->setDelayWhileIdle(true)
    ->setTtl(123)
    ->setRestrictedPackageName("com.example.trololo")
    ->setDryRun(true)
;

try {
    $response = $sender->send($message);

    if ($response->getNewRegistrationIdsCount() > 0) {
        $newRegistrationIds = $response->getNewRegistrationIds();
        foreach ($newRegistrationIds as $oldRegistrationId => $newRegistrationId){
            //Update $oldRegistrationId to $newRegistrationId in DB
            //TODO
        }
    }

    if ($response->getFailureCount() > 0) {
        $invalidRegistrationIds = $GCMresponse->getInvalidRegistrationIds();
        foreach($invalidRegistrationIds as $invalidRegistrationId) {
            //Remove invalid registration Ids from DB
            //TODO
        }

        //Schedule to resend messages to unavailable devices
        $unavailableIds = $response->getUnavailableRegistrationIds();
        //TODO
    }
} catch (GCM\Exception $e) {

    switch ($e->getCode()) {
        case GCM\Exception::ILLEGAL_API_KEY:
        case GCM\Exception::AUTHENTICATION_ERROR:
        case GCM\Exception::MALFORMED_REQUEST:
        case GCM\Exception::UNKNOWN_ERROR:
        case GCM\Exception::MALFORMED_RESPONSE:
            //Deal with it
            break;
    }
}

```

Also indirect message API available

```php

use \CodeMonkeysRu\GCM;

$sender = new GCM\Sender("YOUR GOOGLE API KEY");

try {
    $response = $sender->sendMessage(
        array("device_registration_id1", "device_registration_id2"),
        array("data1" => "123", "data2" => "string"),
        "collapse_key"
    );

    if ($response->getNewRegistrationIdsCount() > 0) {
        $newRegistrationIds = $response->getNewRegistrationIds();
        foreach ($newRegistrationIds as $oldRegistrationId => $newRegistrationId){
            //Update $oldRegistrationId to $newRegistrationId in DB
            //TODO
        }
    }

    if ($response->getFailureCount() > 0) {
        $invalidRegistrationIds = $GCMresponse->getInvalidRegistrationIds();
        foreach($invalidRegistrationIds as $invalidRegistrationId) {
            //Remove invalid registration Ids from DB
            //TODO
        }

        //Schedule to resend messages to unavailable devices
        $unavailableIds = $response->getUnavailableRegistrationIds();
        //TODO
    }
} catch (GCM\Exception $e) {

    switch ($e->getCode()) {
        case GCM\Exception::ILLEGAL_API_KEY:
        case GCM\Exception::AUTHENTICATION_ERROR:
        case GCM\Exception::MALFORMED_REQUEST:
        case GCM\Exception::UNKNOWN_ERROR:
        case GCM\Exception::MALFORMED_RESPONSE:
            //Deal with it
            break;
    }
}

```


ChangeLog
----------------------

* v0.1 - Initial release

Licensed under MIT license.
