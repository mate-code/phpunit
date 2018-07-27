<?php

namespace mate\PhpUnit\Zend;

use mate\PhpUnit\AccessPropertyTrait;
use Zend\Console\Request;
use Zend\Console\Response;
use Zend\Mvc\Application;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\Console\SimpleRouteStack;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;

/**
 * @package mate\PhpUnit\Zend
 */
abstract class ControllerTest extends \PHPUnit_Framework_TestCase
{

    use AccessPropertyTrait;

    /**
     * @var array
     */
    private $constructorArgs = array();

    /**
     * @var string
     */
    private $controllerClass;

    /**
     * @var ServiceManager
     */
    private $realServiceManager;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var AbstractController
     */
    protected $controller;

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
     * @deprecated access via event
     */
    protected $routeMatch;

    /**
     * @var MvcEvent
     */
    protected $event;

    /**
     * reset controller
     */
    public function tearDown()
    {
        $this->request = null;
        $this->response = null;
        $this->routeMatch = null;
        $this->event = null;
        $this->controller = null;
    }

    /**
     * @param array $mockedMethods
     * @return \PHPUnit_Framework_MockObject_MockObject|AbstractController
     */
    protected function createController($mockedMethods = array())
    {
        $controllerClass = $this->getControllerClass();
        $constructorArgs = $this->getConstructorArgs();

        if(!$mockedMethods) {
            $this->controller =  new $controllerClass(...$constructorArgs);
        } else {
            $this->controller = $this->getMockBuilder($controllerClass)
                ->setMethods($mockedMethods)
                ->setConstructorArgs($constructorArgs)
                ->getMock();
        }

        $this->controller->setEvent($this->getEvent());

        return $this->controller;
    }

    /**
     * @param string $action
     * @param array $params
     * @return mixed|\Zend\Stdlib\ResponseInterface
     */
    public function dispatchAction($action, array $params = array())
    {
        $routeMatch = $this->getEvent()->getRouteMatch();
        $request = $this->getRequest();
        $response = $this->getResponse();

        foreach ($params as $paramName => $paramValue) {
            $routeMatch->setParam($paramName, $paramValue);
        }
        $routeMatch->setParam("action", $action);
        return $this->controller->dispatch($request, $response);
    }

    /**
     * @return array
     */
    public function getConstructorArgs()
    {
        return $this->constructorArgs;
    }

    /**
     * @param array $constructorArgs
     */
    public function setConstructorArgs(array $constructorArgs)
    {
        $this->constructorArgs = $constructorArgs;
    }

    /**
     * @return string
     */
    public function getControllerClass()
    {
        if(!$this->controllerClass) {
            throw new \RuntimeException("No controllerClass provided in setUp()");
        }
        return $this->controllerClass;
    }

    /**
     * @param string $controllerClass
     */
    public function setControllerClass($controllerClass)
    {
        $this->controllerClass = $controllerClass;
    }

    /**
     * @return ServiceManager
     */
    public function getRealServiceManager()
    {
        if(!$this->realServiceManager) {
            throw new \RuntimeException("No realServiceManager provided in setUp()");
        }
        return $this->realServiceManager;
    }

    /**
     * @param ServiceManager $realServiceManager
     */
    public function setRealServiceManager(ServiceManager $realServiceManager)
    {
        $this->realServiceManager = $realServiceManager;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        if(!isset($this->config)) {
            $this->config = $this->getRealServiceManager()->get("Config");
        }
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        if(!isset($this->request)) {
            $this->request = new Request();
            $this->getEvent()->setRequest($this->request);
        }
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->getEvent()->setRequest($request);
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        if(!isset($this->response)) {
            $this->response = new Response();
            $this->getEvent()->setResponse($this->response);
        }
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
        $this->getEvent()->setResponse($response);
    }

    /**
     * @return MvcEvent
     */
    public function getEvent()
    {
        if(!isset($this->event)) {
            $this->event = new MvcEvent();

            $config = $this->getConfig();
            $routerConfig = isset($config['console']['router']) ? $config['console']['router'] : array();
            unset($routerConfig['routes']['doctrine_cli']);
            $router = SimpleRouteStack::factory($routerConfig);
            $this->event->setRouter($router);

            $this->routeMatch = new RouteMatch(array());
            $this->event->setRouteMatch($this->routeMatch);

            $this->request = $this->getRequest();
            $this->event->setRequest($this->request);

            $serviceManager = $this->getRealServiceManager();
            /** @var Application $application */
            $application = $serviceManager->get("Application");
            $this->accessSetProperty($application, "event", $this->event);
        }
        return $this->event;
    }

    /**
     * @param MvcEvent $event
     */
    public function setEvent(MvcEvent $event)
    {
        $this->event = $event;
    }
}