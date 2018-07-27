<?php

namespace mate\PhpUnit\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;

trait TestWithMetadataTrait
{
    /**
     * @var array
     */
    protected $entityPaths = [];
    /**
     * @var AbstractPlatform
     */
    protected $platform;

    /**
     * @param array $entityPaths
     * @param AbstractPlatform $platform
     * @return EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getEmMock(array $entityPaths = NULL, AbstractPlatform $platform = NULL)
    {
        $entityPaths = $entityPaths === NULL ? $this->entityPaths : $entityPaths;
        $platform = $platform === NULL ? $this->platform : $platform;

        $config = Setup::createAnnotationMetadataConfiguration($entityPaths, true);
        $eventManager = new EventManager();
        $metadataFactory = new ClassMetadataFactory();
        $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));

        /** @var \PHPUnit_Framework_TestCase $this */
        
        $connectionMock = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionMock->expects($this->any())
            ->method('getDatabasePlatform')
            ->will($this->returnValue($platform));

        /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject $emMock */
        $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $metadataFactory->setEntityManager($emMock);
        $emMock->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($config));
        $emMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($connectionMock));
        $emMock->expects($this->any())
            ->method('getEventManager')
            ->will($this->returnValue($eventManager));
        $emMock->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnCallback(function($class) use ($metadataFactory){
                return $metadataFactory->getMetadataFor($class);
            }));
        return $emMock;
    }

}