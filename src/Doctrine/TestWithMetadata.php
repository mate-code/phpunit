<?php

namespace mate\PhpUnit\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Tools\Setup;

/**
 * Class AbstractTestWithMetadata
 * @package mate\Doctrine\Test
 * @author Marius Teller <marius.teller@modotex.com>
 */
abstract class TestWithMetadata extends \PHPUnit_Framework_TestCase
{

    const EXCEPTION_NO_ENTITY_PATHS_SET = "At least one entity path must be set";

    const EXCEPTION_NO_PLATFORM_SET = "An instance of Doctrine\\DBAL\\Platforms\\AbstractPlatform must be set";

    /**
     * @var array
     */
    protected $entityPaths = [];
    /**
     * @var AbstractPlatform
     */
    protected $platform;
    /**
     * @var EntityManager
     */
    protected $emMock;
    /**
     * @var Connection
     */
    protected $connectionMock;
    /**
     * @var Configuration
     */
    protected $configuration;
    /**
     * @var EventManager
     */
    protected $eventManager;
    /**
     * @var ClassMetadataFactory
     */
    protected $classMetadataFactory;


    /**
     * @return array
     * @throws \Exception
     */
    public function getEntityPaths()
    {
        if($this->entityPaths === []) {
            throw new \Exception(self::EXCEPTION_NO_ENTITY_PATHS_SET);
        }
        return $this->entityPaths;
    }

    /**
     * @param array $entityPaths
     */
    public function setEntityPaths(array $entityPaths)
    {
        $this->entityPaths = $entityPaths;
    }

    /**
     * add an entity path
     * @param string $path
     */
    public function addEntityPath($path)
    {
        $this->entityPaths[] = $path;
    }

    /**
     * @return AbstractPlatform
     * @throws \Exception
     */
    public function getPlatform()
    {
        if(!isset($this->platform)) {
            throw new \Exception(self::EXCEPTION_NO_PLATFORM_SET);
        }
        return $this->platform;
    }

    /**
     * @param AbstractPlatform $platform
     */
    public function setPlatform(AbstractPlatform $platform)
    {
        $this->platform = $platform;
    }

    /**
     * @return EntityManager
     */
    public function getEmMock()
    {
        if(!isset($this->emMock)) {
            /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject $emMock */
            $emMock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                ->disableOriginalConstructor()
                ->getMock();

            $config = $this->getConfiguration();
            $connectionMock = $this->getConnectionMock();
            $eventManager = $this->getEventManager();
            $classMetadataFactory = $this->getClassMetadataFactory();
            $classMetadataFactory->setEntityManager($emMock);

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
                ->will($this->returnCallback(function($class) use ($classMetadataFactory){
                    return $classMetadataFactory->getMetadataFor($class);
                }));
            $this->setEmMock($emMock);
        }
        return $this->emMock;
    }

    /**
     * @param EntityManager $emMock
     */
    public function setEmMock($emMock)
    {
        $this->emMock = $emMock;
    }

    /**
     * @return Connection
     */
    public function getConnectionMock()
    {
        if(!isset($this->connectionMock)) {
            $platform = $this->getPlatform();
            /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $connectionMock */
            $connectionMock = $this->getMockBuilder('Doctrine\DBAL\Connection')
                ->disableOriginalConstructor()
                ->getMock();
            $connectionMock->expects($this->any())
                ->method('getDatabasePlatform')
                ->will($this->returnValue($platform));
            $this->setConnectionMock($connectionMock);
        }
        return $this->connectionMock;
    }

    /**
     * @param Connection $connectionMock
     */
    public function setConnectionMock($connectionMock)
    {
        $this->connectionMock = $connectionMock;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        if(!isset($this->configuration)) {
            $config = Setup::createAnnotationMetadataConfiguration($this->getEntityPaths(), true);
            $config->setMetadataDriverImpl(new AnnotationDriver(new AnnotationReader()));
            $this->setConfiguration($config);
        }
        return $this->configuration;
    }

    /**
     * @param Configuration $configuration
     */
    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return EventManager
     */
    public function getEventManager()
    {
        if(!isset($this->eventManager)) {
            $this->setEventManager(new EventManager());
        }
        return $this->eventManager;
    }

    /**
     * @param EventManager $eventManager
     */
    public function setEventManager($eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @return ClassMetadataFactory
     */
    public function getClassMetadataFactory()
    {
        if(!isset($this->classMetadataFactory)) {
            $this->setClassMetadataFactory(new ClassMetadataFactory());
        }
        return $this->classMetadataFactory;
    }

    /**
     * @param ClassMetadataFactory $classMetadataFactory
     */
    public function setClassMetadataFactory(ClassMetadataFactory $classMetadataFactory)
    {
        $this->classMetadataFactory = $classMetadataFactory;
    }
}