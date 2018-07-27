<?php

namespace mate\PhpUnit\Zend;

use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Trait to use in test classes extending \PHPUnit_Framework_TestCase
 * Includes a mock object of ServiceLocatorInterface and a simple method to add
 * service locator get() calls
 *
 * @package mate\PhpUnit\Zend
 */
trait ServiceLocatorMockTrait
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ServiceLocatorInterface
     */
    protected $serviceLocatorMock;

    /**
     * method call index of serviceLocatorMock
     * @var int
     */
    protected $serviceLocatorIndex = 0;

    /**
     * adds a get() call to serviceLocatorMock and counts up serviceLocatorIndex
     *
     * @param $serviceName
     * @param $returnValue
     */
    protected function addServiceManagerCall($serviceName, $returnValue)
    {
        $this->getServiceLocatorMock()
            ->expects($this->at($this->serviceLocatorIndex))
            ->method("get")
            ->with($serviceName)
            ->will($this->returnValue($returnValue));
        $this->serviceLocatorIndex++;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ServiceLocatorInterface
     */
    public function getServiceLocatorMock()
    {
        if(!isset($this->serviceLocatorMock)) {
            $this->serviceLocatorMock = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        }
        return $this->serviceLocatorMock;
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject|ServiceLocatorInterface $serviceLocatorMock
     */
    public function setServiceLocatorMock($serviceLocatorMock)
    {
        $this->serviceLocatorMock = $serviceLocatorMock;
    }

}