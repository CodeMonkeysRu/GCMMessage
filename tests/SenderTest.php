<?php

class SenderTest extends PHPUnit_Framework_TestCase
{

    /**
     * @expectedException \CodeMonkeysRu\GCM\Exception
     * @expectedExceptionCode 1
     */
    public function testApiKeyCheck()
    {
        $sender = new \CodeMonkeysRu\GCM\Sender(null);
        $message = new \CodeMonkeysRu\GCM\Message(array('REG ID'));
        $sender->send($message);
    }

    /**
     * @expectedException \CodeMonkeysRu\GCM\Exception
     * @expectedExceptionCode 3
     */
    public function testPayloadDataSizeCheck()
    {
        $sender = new \CodeMonkeysRu\GCM\Sender("MY API KEY ))");
        $data = array();
        for ($i = 0; $i < 4096; $i++) {
            $data['key'.$i] = $i;
        }
        $message = new \CodeMonkeysRu\GCM\Message(array(), $data);
        $sender->send($message);
    }

    /**
     * @expectedException \CodeMonkeysRu\GCM\Exception
     * @expectedExceptionCode 3
     */
    public function testPayloadNotificationSizeCheck()
    {
        $sender = new \CodeMonkeysRu\GCM\Sender("MY API KEY ))");
        $notification = array('key' => str_repeat('x', 2048));
        $message = new \CodeMonkeysRu\GCM\Message('REG ID', null, null, null, null, $notification);
        $sender->send($message);
    }

}