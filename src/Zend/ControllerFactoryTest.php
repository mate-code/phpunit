<?php

namespace mate\PhpUnit\Zend;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\ServiceManager;

/**
 * @package mate\PhpUnit\Zend
 */
abstract class ControllerFactoryTest extends FactoryTest
{
    /**
     * @var ServiceManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceManagerMock;

    /**
     * test if the configuration to link the service name with its factory was set
     */
    public function testFactoryIsAttachedToServiceManager()
    {
        $serviceName = $this->getServiceName();
        $controllerManager = $this->getRealServiceManager()->get('ControllerManager');
        $factoryClass = get_class($this->getFactory());
        $this->assertTrue($controllerManager->has($serviceName),
            "$factoryClass was not linked to controller name $serviceName in controller configuration");
    }

    protected function prepareServiceDependencies()
    {
        $controllerManagerMock = $this->getServiceLocatorMock();
        $this->setServiceLocatorMock($this->getServiceManagerMock());
        parent::prepareServiceDependencies();
        $this->setServiceLocatorMock($controllerManagerMock);

    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ControllerManager
     */
    public function getServiceLocatorMock()
    {
        if(!isset($this->serviceLocatorMock)) {
            $this->serviceLocatorMock = $this->getMockBuilder(ControllerManager::class)
                ->disableOriginalConstructor()->getMock();
            $this->serviceLocatorMock->method("getServiceLocator")
                ->will($this->returnValue($this->getServiceManagerMock()));
        }
        return $this->serviceLocatorMock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ServiceManager
     */
    public function getServiceManagerMock()
    {
        if(!isset($this->serviceManagerMock)) {
            $this->serviceManagerMock = $this->getMockBuilder(ServiceManager::class)
                ->disableOriginalConstructor()->getMock();
        }
        return $this->serviceManagerMock;
    }

}