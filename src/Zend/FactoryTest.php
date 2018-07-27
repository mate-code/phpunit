<?php

namespace mate\PhpUnit\Zend;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AbstractFactoryTest
 * @package DatabaseTest\Factory
 * @author Marius Teller <marius.teller@modotex.com>
 */
abstract class FactoryTest extends \PHPUnit_Framework_TestCase
{
    use ServiceLocatorMockTrait;

    /**
     * factory class to create service
     * @var FactoryInterface
     */
    protected $factory;
    /**
     * actual service manager to check if configuration was set
     * @var ServiceManager
     */
    protected $realServiceManager;
    /**
     * class of service to create
     * @var string
     */
    protected $serviceClass;
    /**
     * service name for service locators get() method
     * will be equal to serviceClass if not set
     * @var string
     */
    protected $serviceName;
    /**
     * needs to be initialized set as class property or in __construct() because
     * data providers are executed before setUp()
     *
     * expected injected dependencies data:
     *
     * 0 string : service property to read the dependency from through a getter
     * 1 string : full class name of injected class
     * 2 string : (optional) service name for calling the dependencies factory
     *
     * @var array
     */
    protected $serviceDependencies = array();

    /**
     * test if the factory implements Zend\ServiceManager\FactoryInterface
     */
    public function testFactoryImplementsFactoryInterface()
    {
        $factory = $this->getFactory();
        $factoryClass = get_class($factory);
        $this->assertInstanceOf('Zend\ServiceManager\FactoryInterface', $this->getFactory(),
            "$factoryClass must implement Zend\\ServiceManager\\FactoryInterface");
    }

    /**
     * test if the configuration to link the service name with its factory was set
     */
    public function testFactoryIsAttachedToServiceManager()
    {
        $serviceName = $this->getServiceName();
        $serviceManager = $this->getRealServiceManager();
        $factoryClass = get_class($this->getFactory());
        $this->assertTrue($serviceManager->has($serviceName),
            "$factoryClass was not linked to service name $serviceName in configuration array");
    }

    /**
     * test if the factory creates an instance of serviceClass
     *
     * @depends testFactoryImplementsFactoryInterface
     */
    public function testCreateService()
    {
        $service = $this->createService();
        $factoryClass = get_class($this->getFactory());
        $serviceClass = $this->getServiceClass();
        $this->assertInstanceOf($serviceClass, $service,
            "$factoryClass::createService() does not return an instance of $serviceClass");
    }

    /**
     * call createService method of factory
     *
     * @return mixed
     */
    protected function createService()
    {
        $this->prepareCreateService();
        $this->prepareServiceDependencies();
        $factory = $this->getFactory();
        $serviceLocator = $this->getServiceLocatorMock();
        return $factory->createService($serviceLocator);
    }

    /**
     * overwrite to configure mocks etc. before calling factory::createService
     */
    protected function prepareCreateService() { }

    /**
     * will set a return value map to serviceLocatorMock including the service calls
     * for all defined dependencies
     */
    protected function prepareServiceDependencies()
    {
        $valueMap = array();
        $serviceLocatorMock = $this->getServiceLocatorMock();

        foreach ($this->serviceDependencies as $dependency) {
            $dependentClassName = $dependency[1];
            $dependentServiceName = isset($dependency[2]) ? $dependency[2] : $dependentClassName;

            $dependencyMock = $this->getMockBuilder($dependentClassName)
                ->disableOriginalConstructor()->getMock();

            if($serviceLocatorMock instanceof ServiceManager) {
                $valueMap[] = [$dependentServiceName, true, $dependencyMock];
            } else {
                $valueMap[] = [$dependentServiceName, $dependencyMock];
            }
        }

        if($valueMap) {
            $serviceLocatorMock->method("get")
                ->will($this->returnValueMap($valueMap));
        }
    }

    /**
     * provides testServiceDependencies
     *
     * 0 string : service property to read the dependency from through a getter
     * 1 string : full class name of injected class
     * 2 string : (optional) service name for calling the dependencies factory
     *
     * @return array
     */
    public function provideTestServiceDependencies()
    {
        return $this->serviceDependencies;
    }

    /**
     * @dataProvider provideTestServiceDependencies
     *
     * @param string $serviceProperty
     * @param string $dependentClassName
     * @return null|void
     */
    public function testServiceDependencies($serviceProperty = null, $dependentClassName = null)
    {
        if($serviceProperty === null) {
            return null;
        }
        $service = $this->createService();
        $getter = "get".ucfirst($serviceProperty);
        $serviceClass = get_class($service);
        $this->assertTrue(method_exists($service, $getter),
            "Method $serviceClass::$getter does not exist");
        $service = $service->$getter();
        $this->assertInstanceOf($dependentClassName, $service,
            "No instance of $dependentClassName injected in service property $serviceProperty");
    }

    /**
     * factory class to create service
     *
     * @return FactoryInterface
     * @throws \RuntimeException if factory was not set
     */
    public function getFactory()
    {
        if(!isset($this->factory)) {
            throw new \RuntimeException("No factory object provided in setUp()");
        }
        return $this->factory;
    }

    /**
     * factory class to create service
     *
     * @param FactoryInterface $factory
     */
    public function setFactory($factory)
    {
        $this->factory = $factory;
    }

    /**
     * actual service manager to check if configuration was set
     *
     * @return ServiceManager
     * @throws \RuntimeException if realServiceManager was not set
     */
    public function getRealServiceManager()
    {
        if(!isset($this->realServiceManager)) {
            throw new \RuntimeException("No realServiceManager provided in setUp()");
        }
        return $this->realServiceManager;
    }

    /**
     * actual service manager to check if configuration was set
     *
     * @param ServiceManager $realServiceManager
     */
    public function setRealServiceManager($realServiceManager)
    {
        $this->realServiceManager = $realServiceManager;
    }

    /**
     * class of service to create
     *
     * @return string
     */
    public function getServiceClass()
    {
        if(!isset($this->serviceClass)) {
            throw new \RuntimeException("No serviceClass provided in setUp()");
        }
        return $this->serviceClass;
    }

    /**
     * class of service to create
     *
     * @param string $serviceClass
     */
    public function setServiceClass($serviceClass)
    {
        $this->serviceClass = $serviceClass;
    }

    /**
     * service name for service locators get() method
     * will be equal to serviceClass if not set
     *
     * @return string
     */
    public function getServiceName()
    {
        if(!isset($this->serviceName)) {
            $this->serviceName = $this->getServiceClass();
        }
        return $this->serviceName;
    }

    /**
     * service name for service locators get() method
     * will be equal to serviceClass if not set
     *
     * @param string $serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
    }

}