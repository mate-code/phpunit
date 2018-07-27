<?php

namespace mate\PhpUnit\Zend;

use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AbstractFactoryTest
 * @package DatabaseTest\Factory
 * @author Marius Teller <marius.teller@modotex.com>
 */
abstract class AbstractFactoryTest extends \PHPUnit_Framework_TestCase
{
    use ServiceLocatorMockTrait;

    const EXCEPTION_PROPERTY_NOT_SET = "Property %s not provided in setUp()";

    /**
     * @var AbstractFactoryInterface
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
     * @var string
     */
    protected $validServiceName;
    /**
     * invalid service name for testing canCreateServiceWithName
     * @var string
     */
    protected $invalidServiceName;
    /**
     * service dependencies to prepare by setting returnValueMap for mocked ServiceLocator::get()
     * 0 = property name ('name' resolves to 'getName()')
     * 1 = service class (used for auto creating injected mock)
     * 2 = service name (for ServiceLocator::get(), optional, service class by default)
     * 3 = service mock (optional, auto creation from service class by default)
     * @var array
     */
    protected $serviceDependencies = array();


    /**
     * test if the factory implements Zend\ServiceManager\AbstractFactoryInterface
     */
    public function testIsAbstractApiFactory()
    {
        $factory = $this->getFactory();
        $factoryClass = get_class($factory);
        $this->assertInstanceOf('Zend\ServiceManager\AbstractFactoryInterface', $factory,
            "$factoryClass must extend AbstractApiFactory");
    }

    /**
     * @depends testIsAbstractApiFactory
     */
    public function testCanCreateServiceWithValidName()
    {
        $requestName = $this->getValidServiceName();
        $canCreateService = $this->canCreateServiceWithName($requestName);
        $factoryClass = get_class($this->getFactory());
        $this->assertTrue($canCreateService,
            "$factoryClass::canCreateServiceWithName does not return true if the valid name $requestName was given");
    }

    /**
     * @depends testIsAbstractApiFactory
     */
    public function testCanCreateServiceWithInvalidName()
    {
        $requestName = $this->getInvalidServiceName();
        $canCreateService = $this->canCreateServiceWithName($requestName);
        $factoryClass = get_class($this->getFactory());
        $this->assertFalse($canCreateService,
            "$factoryClass::canCreateServiceWithName does not return false if the invalid name $requestName was given");
    }

    /**
     * @depends testIsAbstractApiFactory
     */
    public function testCreateServiceWithName()
    {
        $requestName = $this->getValidServiceName();
        $factoryClass = get_class($this->getFactory());
        $service = $this->createServiceWithName($requestName);
        $serviceClass = $this->getServiceClass();
        $this->assertInstanceOf($serviceClass, $service,
            "$factoryClass::createServiceWithName() does not return an instance of $serviceClass");
    }

    /**
     * test if the configuration fot the abstract factory was properly set
     *
     * @depends testCanCreateServiceWithValidName
     */
    public function testFactoryIsAttachedToServiceManager()
    {
        $serviceName = $this->getValidServiceName();
        $serviceManager = $this->getRealServiceManager();
        $factoryClass = get_class($this->getFactory());
        $this->assertTrue($serviceManager->has($serviceName),
            "Factory class $factoryClass was not linked to service name $serviceName in configuration array");
    }

    /**
     * triggers createServiceWithName of the tested abstract factory and returns
     * its result
     * $requestName will be equal to $name if it is not set
     *
     * @param string $name
     * @param null $requestName
     * @return bool
     * @throws \Exception
     */
    protected function createServiceWithName($name, $requestName = NULL)
    {
        $this->prepareServiceDependencies();
        $this->prepareCreateServiceWithName();
        $requestName = !isset($requestName) ? $name : $requestName;
        $factory = $this->getFactory();
        $serviceLocator = $this->getServiceLocatorMock();
        return $factory->createServiceWithName($serviceLocator, $name, $requestName);
    }

    /**
     * overwrite to configure mocks etc. before calling factory::canCreateServiceWithName
     */
    protected function prepareCreateServiceWithName() { }

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

            if(isset($dependency[3])) {
                $dependencyMock = $dependency[3];
            } else {
                $dependencyMock = $this->getMockBuilder($dependentClassName)
                    ->disableOriginalConstructor()->getMock();
            }

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
        $service = $this->createServiceWithName($this->getValidServiceName());
        $getter = "get".ucfirst($serviceProperty);
        $serviceClass = get_class($service);
        $this->assertTrue(method_exists($service, $getter),
            "Method $serviceClass::$getter does not exist");
        $service = $service->$getter();
        $this->assertInstanceOf($dependentClassName, $service,
            "No instance of $dependentClassName injected in service property $serviceProperty");
    }

    /**
     * triggers canCreateServiceWithName of the tested abstract factory and returns
     * its result
     * $requestName will be equal to $name if it is not set
     *
     * @param string $name
     * @param null $requestName
     * @return bool
     * @throws \Exception
     */
    protected function canCreateServiceWithName($name, $requestName = NULL)
    {
        $this->prepareCanCreateServiceWithName();
        $requestName = !isset($requestName) ? $name : $requestName;
        $factory = $this->getFactory();
        $serviceLocator = $this->getServiceLocatorMock();
        return $factory->canCreateServiceWithName($serviceLocator, $name, $requestName);
    }

    /**
     * overwrite to configure mocks etc. before calling factory::createServiceWithName
     */
    protected function prepareCanCreateServiceWithName() { }

    /**
     * @return AbstractFactoryInterface
     * @throws \RuntimeException if factory was not set
     */
    public function getFactory()
    {
        if(!isset($this->factory)) {
            throw new \RuntimeException(sprintf(self::EXCEPTION_PROPERTY_NOT_SET, "factory"));
        }
        return $this->factory;
    }

    /**
     * @param AbstractFactoryInterface $factory
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
            throw new \RuntimeException(sprintf(self::EXCEPTION_PROPERTY_NOT_SET, "realServiceManaer"));
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
     * @throws \RuntimeException
     */
    public function getServiceClass()
    {
        if(!isset($this->serviceClass)) {
            throw new \RuntimeException(sprintf(self::EXCEPTION_PROPERTY_NOT_SET, "serviceClass"));
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
    public function getValidServiceName()
    {
        if(!isset($this->validServiceName)) {
            throw new \RuntimeException(sprintf(self::EXCEPTION_PROPERTY_NOT_SET, "validServiceName"));
        }
        return $this->validServiceName;
    }

    /**
     * service name for service locators get() method
     * will be equal to serviceClass if not set
     *
     * @param string $validServiceName
     */
    public function setValidServiceName($validServiceName)
    {
        $this->validServiceName = $validServiceName;
    }

    /**
     * invalid service name for testing canCreateServiceWithName
     * @return string
     */
    public function getInvalidServiceName()
    {
        if(!isset($this->invalidServiceName)) {
            throw new \RuntimeException(sprintf(self::EXCEPTION_PROPERTY_NOT_SET, "invalidServiceName"));
        }
        return $this->invalidServiceName;
    }

    /**
     * invalid service name for testing canCreateServiceWithName
     * @param string $invalidServiceName
     */
    public function setInvalidServiceName($invalidServiceName)
    {
        $this->invalidServiceName = $invalidServiceName;
    }

}