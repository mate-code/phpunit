<?php

namespace mate\PhpUnit\Mapper;

use mate\Resource\ArrayResource;
use mate\Resource\ResourceInterface;

/**
 * Class AbstractConditionTest
 * @package OrderTest\Mapper\Condition
 * @author Marius Teller <marius.teller@modotex.com>
 */
abstract class ConditionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var object
     */
    protected $condition;

    /**
     * @var ArrayResource
     */
    protected $data;

    /**
     * @var ResourceInterface
     */
    protected $source;

    /**
     * @var ResourceInterface
     */
    protected $resource;

    public function setUp()
    {
        $resArray = array();
        $this->setResource(new ArrayResource($resArray));
        $sourceArray = array();
        $this->setSource(new ArrayResource($sourceArray));
        $data = array();
        $this->setData(new ArrayResource($data));
    }

    public function testInstanceOfConditionInterface()
    {
        $interface = 'mate\Mapper\Condition\ConditionInterface';
        $class = is_object($this->condition) ? get_class($this->condition) : gettype($this->condition);
        $this->assertInstanceOf($interface, $this->condition,
            "$class does not implement $interface");
    }

    protected function triggerVerifyHydrate($value)
    {
        return $this->getCondition()->verifyHydrate(
            $value,
            $this->getData(),
            $this->getResource()
        );
    }

    protected function triggerVerifyExtract($value)
    {
        return $this->getCondition()->verifyExtract(
            $value,
            $this->getSource(),
            $this->getResource()
        );
    }

    /**
     * @return object
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * @param object $condition
     */
    public function setCondition($condition)
    {
        $this->condition = $condition;
    }

    /**
     * @return ArrayResource
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param ArrayResource $data
     */
    public function setData(ArrayResource $data)
    {
        $this->data = $data;
    }

    /**
     * @return ResourceInterface
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param ResourceInterface $source
     */
    public function setSource(ResourceInterface $source)
    {
        $this->source = $source;
    }

    /**
     * @return ResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param ResourceInterface $resource
     */
    public function setResource(ResourceInterface $resource)
    {
        $this->resource = $resource;
    }

}
