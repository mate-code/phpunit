<?php
namespace mate\PhpUnit\Zend;


use mate\PhpUnit\AccessMethodTrait;
use mate\PhpUnit\AccessPropertyTrait;
use Zend\Console\Request;
use Zend\Console\Response;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Console\RouteMatch;
use Zend\Mvc\Router\Console\SimpleRouteStack;

/**
 * @deprecated
 * @package mate\PhpUnit\Zend
 */
class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $constructorArgs;
    /**
     * @var string
     */
    protected $sourceClass;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var RouteMatch
     */
    protected $routeMatch;
    /**
     * @var MvcEvent
     */
    protected $event;
    /**
     * @var array
     */
    private $reflectedClasses = [];

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
    }

    public function testIsDeprecated()
    {
        $thisClass = self::class;
        $accessMethodTrait = AccessMethodTrait::class;
        $accessPropertyTrait = AccessPropertyTrait::class;
        trigger_error(
            "$thisClass is deprecated. Use $accessMethodTrait and $accessPropertyTrait instead"
        );
    }

    protected function createInstance(array $args, $sourceClass = null)
    {
        $sourceClass = $sourceClass ?: $this->sourceClass;
        $reflectedClass = new \ReflectionClass($sourceClass);
        return $reflectedClass->newInstanceArgs($args);
    }

    /**
     * @param array $mockMethods
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockForSourceClass(array $mockMethods = [], $sourceClass = null)
    {
        $sourceClass = $sourceClass ?: $this->sourceClass;
        /** @var \PHPUnit_Framework_MockObject_MockObject $invocationMocker */
        $invocationMocker = $this->getMockBuilder($sourceClass)
            ->setMethods(array_keys($mockMethods))
            ->setConstructorArgs($this->constructorArgs)
            ->getMock();

        foreach ($mockMethods as $methodName => $methodConfig) {
            $mockMethod = $invocationMocker;
            if(isset($methodConfig['expects'])) {
                $mockMethod = $mockMethod
                    ->expects($methodConfig['expects']);
            }
            $mockMethod = $mockMethod
                ->method($methodName);
            if(isset($methodConfig['with'])) {
                $mockMethod = $mockMethod->with($methodConfig['with']);
            }
            $mockMethod
                ->will($methodConfig['will']);
        }

        return $invocationMocker;
    }

    protected function setUpController(&$controllerInstance)
    {
        $this->request = new Request();
        $this->response = new Response();
        $this->event = new MvcEvent();
        $this->routeMatch = new RouteMatch(array('controller' => $this->sourceClass));
        $routerConfig = isset($this->config['console']['router']) ? $this->config['console']['router'] : array();
        unset($routerConfig['routes']['doctrine_cli']);
        $router = SimpleRouteStack::factory($routerConfig);

        $this->event->setRouter($router);
        $this->event->setRouteMatch($this->routeMatch);
        $controllerInstance->setEvent($this->event);
    }

    protected function getReflectedClass($className)
    {
        return (isset($this->reflectedClasses[$className]))
            ? $this->reflectedClasses[$className]
            : new \ReflectionClass($className);
    }

    protected function getReflectedMethod($methodName, $sourceClass = null)
    {
        $reflectedClass = $this->getReflectedClass($sourceClass ?: $this->sourceClass);
        $reflectedMethod = $reflectedClass->getMethod($methodName);
        $reflectedMethod->setAccessible(true);
        return $reflectedMethod;
    }

    protected function getReflectedProperty($propertyName, $sourceClass = null)
    {
        $reflectedClass = $this->getReflectedClass($sourceClass ?: $this->sourceClass);
        $reflectedProperty = $reflectedClass->getProperty($propertyName);
        $reflectedProperty->setAccessible(true);
        return $reflectedProperty;
    }
}