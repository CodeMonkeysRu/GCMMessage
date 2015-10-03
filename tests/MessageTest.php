<?php

class MessageTest extends PHPUnit_Framework_TestCase
{
    private $sender;
    
    protected function setUp()
    {
        $this->sender = new \CodeMonkeysRu\GCM\Sender('YOUR GOOGLE API KEY');
    }
    
    /**
     * @dataProvider providerFields
     */
    public function testCheckOfFieldsOnSavingOfData($field, $value)
    {
        $setter = 'set' . ucfirst($field);
        $getter = 'get' . ucfirst($field);
        
        $message = $this->sender->buildMessage()
            ->setRegistrationIds(array('REISTRATION ID'))
            ->$setter($value)
            ->build();
        $this->assertEquals($value, $message->$getter());
    }
    
    public function providerFields()
    {
        return array(
            array('collapseKey', 'COLLAPSE KEY'),
            array('priority', 'high'),
            array('contentAvailable', true),
            array('delayWhileIdle', true),
            array('timeToLive', 60 * 60),
            array('restrictedPackageName', 'com.example.example'),
            array('dryRun', true),
            array('data', 'Additional information'),
        );
    }
    
    public function testCheckJsonMessage()
    {
        $message = $this->sender->buildMessage()
            ->setRegistrationIds(array('REISTRATION ID'))
            ->setTimeToLive(60 * 60)
            ->build();
        
        $arr = array(
            'registration_ids' => array('REISTRATION ID'),
            'time_to_live' => 3600
        );
        $this->assertJsonStringEqualsJsonString(json_encode($arr), $message->asJson());
    }
    
    /**
     * @dataProvider providerFieldsNotification
     */
    public function testChechOfFieldsOnNotification($field, $value)
    {
        $setter = 'setNotification' . ucfirst($field);
        
        $message = $this->sender->buildMessage()
            ->setRegistrationIds(array('REISTRATION ID'))
            ->$setter($value)
            ->build();
        $this->assertEquals($value, $message->getNotification()[$field]);
    }
    
    public function providerFieldsNotification()
    {
        return array(
            array('icon', 'Icon'),
            array('sound', 'sound'),
            array('badge', 'Badge'),
            array('tag', 'Tag'),
            array('color', 'Color'),
        );
    }
    
    public function testChechOfFieldsOnNotificationClickAction()
    {   
        $message = $this->sender->buildMessage()
            ->setRegistrationIds(array('REISTRATION ID'))
            ->setNotificationClickAction('Click Action')
            ->build();
        $this->assertEquals('Click Action', $message->getNotification()['click_action']);
    }
 
    public function testChechOfFieldsOnNotificationMessage()
    {
        $message = $this->sender->buildMessage()
            ->setRegistrationIds(array('REISTRATION ID'))
            ->setNotificationMessage('Title', 'Super super text')
            ->build();
        $this->assertEquals('Title', $message->getNotification()['title']);
        $this->assertEquals('Super super text', $message->getNotification()['body']);
    }
    
    public function testChechOfFieldsOnNotificationBodyLoc()
    {
        $message = $this->sender->buildMessage()
            ->setRegistrationIds(array('REISTRATION ID'))
            ->setNotificationBodyLoc('ru-RU', array('green', 'yelow'))
            ->build();
        $this->assertEquals('ru-RU', $message->getNotification()['body_loc_key']);
        $this->assertEquals(array('green', 'yelow'), $message->getNotification()['body_loc_args']);
    }
    
    public function testChechOfFieldsOnNotificationTitleLoc()
    {
        $message = $this->sender->buildMessage()
            ->setRegistrationIds(array('REISTRATION ID'))
            ->setNotificationTitleLoc('ru-RU', array('red', 'yelow'))
            ->build();
        $this->assertEquals('ru-RU', $message->getNotification()['title_loc_key']);
        $this->assertEquals(array('red', 'yelow'), $message->getNotification()['title_loc_args']);
    }
}

