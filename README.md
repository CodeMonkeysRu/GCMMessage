Google Cloud Messaging (GCM) PHP Server Library
--------------------------------------------

A PHP library to send messages to devices registered through Google Cloud Messaging.

See:
http://developer.android.com/guide/google/gcm/index.html

Example usage
-----------------------
```php
$sender = new \CodeMonkeysRu\GCM\Sender("YOUR GOOGLE API KEY");

$message = new \CodeMonkeysRu\GCM\Message(
    array("device_registration_id1", "device_registration_id2"),
    array("data1" => "123", "data2" => "string")
);

try{
    $response = $sender->send($message);

    $newRegistrationIds = $response->getNewRegistrationIds();
    foreach($newRegistrationIds as $oldRegistrationId => $newRegistrationId){
        //Update $oldRegistrationId to $newRegistrationId in DB
        ...
    }

    if($response->getFailureCount() > 0){
        //Remove invalid registration ids from DB
        $invalidRegIds = $response->getInvalidRegistrationIds();
        ...

        //Schedule to resend messages to unavailable
        $unavailableIds = $response->getUnavailableRegistrationIds();
        ...
    }

}catch(\CodeMonkeysRu\GCM\Exception $e){

    switch($e->getCode()){
        case \CodeMonkeysRu\GCM\Exception::ILLEGAL_API_KEY:
        case \CodeMonkeysRu\GCM\Exception::AUTHENTICATION_ERROR:
        case \CodeMonkeysRu\GCM\Exception::MALFORMED_REQUEST:
        case \CodeMonkeysRu\GCM\Exception::UNKNOWN_ERROR:
        case \CodeMonkeysRu\GCM\Exception::MALFORMED_RESPONSE:
            //Deal with it
            break;
    }

}

```

Licensed under MIT license.