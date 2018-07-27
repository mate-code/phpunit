<?php


namespace mate\PhpUnit;


trait AccessMethodTrait
{

    /**
     * calls a protected or private method
     * @param object $instance
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    protected function accessMethod($instance, $methodName, array $args = array())
    {
        if(!method_exists($instance, $methodName)) {
            throw new \RuntimeException("Method $methodName does not exist in class " . get_class($instance));
        }

        $reflectedMethod = new \ReflectionMethod($instance, $methodName);
        $reflectedMethod->setAccessible(true);
        return $reflectedMethod->invokeArgs($instance, $args);
    }

}