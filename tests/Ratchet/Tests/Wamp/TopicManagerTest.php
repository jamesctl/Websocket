<?php
namespace Ratchet\Tests\Wamp;
use Ratchet\Wamp\TopicManager;

/**
 * @covers Ratchet\Wamp\TopicManager
 */
class TopicManagerTest extends \PHPUnit_Framework_TestCase {
    private $mock;
    private $mngr;
    private $conn;

    public function setUp() {
        $this->conn = $this->getMock('\\Ratchet\\ConnectionInterface');
        $this->mock = $this->getMock('\\Ratchet\\Wamp\\WampServerInterface');
        $this->mngr = new TopicManager($this->mock);

        $this->mngr->onOpen($this->conn);
    }

    public function isTopic() {
        return new \PHPUnit_Framework_Constraint_IsInstanceOf('\\Ratchet\\Wamp\\Topic');
    }

    public function testGetTopicReturnsTopicObject() {
        $class  = new \ReflectionClass('\\Ratchet\\Wamp\\TopicManager');
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->mngr, array('The Topic'));

        $this->assertInstanceOf('\\Ratchet\\Wamp\\Topic', $topic);
    }

    public function testGetTopicCreatesTopicWithSameName() {
        $name = 'The Topic';

        $class  = new \ReflectionClass('\\Ratchet\\Wamp\\TopicManager');
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->mngr, array($name));

        $this->assertEquals($name, $topic->getId());
    }

    public function testGetTopicReturnsSameObject() {
        $class  = new \ReflectionClass('\\Ratchet\\Wamp\\TopicManager');
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->mngr, array('No copy'));
        $again = $method->invokeArgs($this->mngr, array('No copy'));

        $this->assertSame($topic, $again);
    }

    public function testOnOpen() {
        $this->mock->expects($this->once())->method('onOpen');
        $this->mngr->onOpen($this->conn);
    }

    public function testOnCall() {
        $id = uniqid();

        $this->mock->expects($this->once())->method('onCall')->with(
            $this->conn
          , $id
          , $this->isTopic()
          , array()
        );

        $this->mngr->onCall($this->conn, $id, 'new topic', array());
    }

    public function testOnSubscribeCreatesTopicObject() {
        $this->mock->expects($this->once())->method('onSubscribe')->with(
            $this->conn, $this->isTopic()
        );

        $this->mngr->onSubscribe($this->conn, 'new topic');
    }

    public function testTopicIsInConnection() {
        $name = 'New Topic';

        $class  = new \ReflectionClass('\\Ratchet\\Wamp\\TopicManager');
        $method = $class->getMethod('getTopic');
        $method->setAccessible(true);

        $topic = $method->invokeArgs($this->mngr, array($name));

        $this->mngr->onSubscribe($this->conn, $name);

        $this->assertTrue($this->conn->WAMP->topics->contains($topic));
    }
}