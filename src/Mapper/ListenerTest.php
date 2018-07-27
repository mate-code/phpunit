<?php


namespace mate\PhpUnit\Mapper;

use Zend\EventManager\EventManager;
use Zend\EventManager\ListenerAggregateInterface;


/**
 * Class AbstractListenerTest
 * @package OrderTest\Mapper\Listener
 * @author Marius Teller <marius.teller@modotex.com>
 */
abstract class ListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ListenerAggregateInterface
     */
    protected $listener;

    /**
     * provides testMethodIsAttached()
     *
     * 0 string : event name
     * 1 string : method name
     * 2 int    : priority (optional)
     *
     * @var array
     */
    protected $attachedMethods = array();

    public function testInstanceOfListenerAggregateInterface()
    {
        $interface = 'Zend\EventManager\ListenerAggregateInterface';
        $class = is_object($this->listener) ? get_class($this->listener) : gettype($this->listener);
        $this->assertInstanceOf($interface, $this->listener,
            "$class does not implement $interface");
    }

    /**
     * provides testMethodIsAttached()
     *
     * 0 string : event name
     * 1 string : method name
     * 2 int    : priority (optional)
     *
     * @return array
     */
    public function provideTestMethodIsAttached()
    {
        return $this->attachedMethods;
    }

    /**
     * @dataProvider provideTestMethodIsAttached
     *
     * @param string $event
     * @param string $method
     * @param null|int $priority
     */
    public function testMethodIsAttached($event = null, $method = null, $priority = 1)
    {
        if($event !== null ||$method !== null) {
            $callback = [$this->listener, $method];
            $this->assertTrue($this->eventIsAttached($event, $callback, $priority),
                "$method is not attached to event $event at priority $priority");
        }
    }

    /**
     * @return array
     */
    protected function getAttachedEvents()
    {
        $attached = array();
        /** @var EventManager|\PHPUnit_Framework_MockObject_MockObject $events */
        $events = $this->getMockBuilder('Zend\EventManager\EventManager')
            ->getMock();
        $events->expects($this->any())
            ->method("attach")
//            ->with(MvcEvent::EVENT_DISPATCH, array($this->listener, "countTasksUp"));
            ->will($this->returnCallback(function ($event, $callback, $priority = 1) use (&$attached) {
                $attached[] = array($event, $callback, $priority);
            }));
        $this->listener->attach($events);
        return $attached;
    }

    /**
     * @param string $event
     * @param array|callable|string $callback
     * @param int $priority
     * @return bool
     */
    protected function eventIsAttached($event, $callback, $priority = 1)
    {
        $attached = $this->getAttachedEvents();
        return in_array([$event, $callback, $priority], $attached);
    }

    /**
     * @param $event
     * @param $callback
     * @return int|false
     */
    protected function getPriority($event, $callback)
    {
        $attached = $this->getAttachedEvents();
        foreach ($attached as $eventArr) {
            if($eventArr[0] == $event && $eventArr[1] == $callback) {
                return $eventArr[2];
            }
        }
        return false;
    }

    /**
     * @return ListenerAggregateInterface
     */
    public function getListener()
    {
        return $this->listener;
    }

    /**
     * @param ListenerAggregateInterface $listener
     */
    public function setListener($listener)
    {
        $this->listener = $listener;
    }

}
