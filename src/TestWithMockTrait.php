<?php

namespace mate\PhpUnit;

trait TestWithMockTrait
{
    /**
     * Full class name of the class you want to test
     * @var string
     */
    protected $testClass;

    /**
     * Instance you want to test, created by self::createInstanceToTest
     * @var object
     */
    protected $testInstance;

    /**
     * constructor arguments of the class you want to test
     * @var array
     */
    protected $constructorArgs = array();

    /**
     * Will create a mock for testing purposes, that only mocks the given methods
     *
     * @param array $mockedMethods
     * @return \PHPUnit_Framework_MockObject_MockObject|object
     */
    public function createInstanceToTest($mockedMethods = array())
    {
        $self = $this;
        if(!$self instanceof \PHPUnit_Framework_TestCase) {
            throw new \RuntimeException(self::class . " may only be used with classes extending PHPUnit_Framework_TestCase");
        }

        $testClass = $this->getTestClass();
        $constructorArgs = $this->getConstructorArgs();

        $testInstance = $self->getMockBuilder($testClass)
            ->setConstructorArgs($constructorArgs)
            ->setMethods($mockedMethods)
            ->getMock();
        $this->setTestInstance($testInstance);
        return $testInstance;
    }

    /**
     * @return string
     */
    public function getTestClass()
    {
        if(!$this->testClass) {
            throw new \RuntimeException("No testClass provided in setUp()");
        }
        return $this->testClass;
    }

    /**
     * @param string $testClass
     */
    public function setTestClass($testClass)
    {
        $this->testClass = $testClass;
    }

    /**
     * @return object
     */
    public function getTestInstance()
    {
        if(!$this->testInstance) {
            $this->createInstanceToTest();
        }
        return $this->testInstance;
    }

    /**
     * @param object $testInstance
     */
    public function setTestInstance($testInstance)
    {
        $this->testInstance = $testInstance;
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
    public function setConstructorArgs($constructorArgs)
    {
        $this->constructorArgs = $constructorArgs;
    }

}