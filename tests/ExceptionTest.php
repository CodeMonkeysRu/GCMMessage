<?php
namespace CodeMonkeysRu\GCM;

/**
 * Class ExceptionTest
 */
class ExceptionTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \CodeMonkeysRu\GCM\Exception
     */

    private $exception;

    protected function setUp()
    {
        $exception = new \CodeMonkeysRu\GCM\Exception("Unknown error.", Exception::UNKNOWN_ERROR);
        $exception->setMustRetry(true);
        $exception->setWaitSeconds(120);

        $this->exception = $exception;
    }
    
    public function testGetMustRetry()
    {
        $this->assertEquals(true, $this->exception->getMustRetry());
    }

    public function testGetWaitSeconds()
    {
        $this->assertEquals(120, $this->exception->getWaitSeconds());
    }
}
