<?php


namespace mate\PhpUnit;


trait AccessPropertyTrait
{

    /**
     * @param $object
     * @param $property
     * @param $value
     * @return \ReflectionProperty
     */
    protected function accessSetProperty($object, $property, $value)
    {
        $reflectedClass = new \ReflectionClass($object);
        $sinceProperty = $reflectedClass->getProperty($property);
        $sinceProperty->setAccessible(true);
        $sinceProperty->setValue($object, $value);
        return $sinceProperty;
    }

    /**
     * @param $object
     * @param $property
     * @return \ReflectionProperty
     */
    protected function accessGetProperty($object, $property)
    {
        $reflectedClass = new \ReflectionClass($object);
        $sinceProperty = $reflectedClass->getProperty($property);
        $sinceProperty->setAccessible(true);
        $propertyValue = $sinceProperty->getValue($object);
        return $propertyValue;
    }

}