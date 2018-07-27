<?php


namespace mate\PhpUnit\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\ORM\Configuration;
use Zend\ServiceManager\ServiceLocatorInterface;


/**
 * Check for getter, setter, adder and remover methods in entities
 * @package DatabaseTest\Entity
 * @author Marius Teller <marius.teller@modotex.com>
 */
abstract class DynamicEntityTest extends \PHPUnit_Framework_TestCase
{

    protected $entities = array();

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * sets an entity manager that does not create a connection while still being
     * able to return metadata
     * @param ServiceLocatorInterface $serviceManager
     * @param AbstractPlatform $platform
     */
    protected function createEntityManagerWithoutConnection(
        ServiceLocatorInterface $serviceManager,
        AbstractPlatform $platform)
    {
        $doctrineEventManager = new EventManager();

        /** @var Connection|\PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();
        $connection->expects($this->any())
            ->method("getEventManager")
            ->will($this->returnValue($doctrineEventManager));
        $connection->expects($this->any())
            ->method("getDatabasePlatform")
            ->will($this->returnValue($platform));

        /** @var Configuration $config */
        $config = $serviceManager->get("doctrine.configuration.orm_default");
        $this->em = EntityManager::create($connection, $config, $doctrineEventManager);
    }

    public function testFieldGetters()
    {
        $missing = array();
        foreach ($this->entities as $entity) {
            $metadata = $this->em->getClassMetadata($entity);
            $fields = $metadata->getFieldNames();
            foreach ($fields as $field) {
                $getter = 'get' . ucfirst($field);
                $entityObj = new $entity();
                if(!method_exists($entityObj, $getter)) {
                    $missing[] = $entity . '::' . $getter;
                }
            }
        }
        $this->assertEmpty($missing, "There are missing getters:" . $this->getMethodString($missing));
    }

    public function testFieldSetters()
    {
        $missing = array();
        foreach ($this->entities as $entity) {
            $metadata = $this->em->getClassMetadata($entity);
            $fields = $metadata->getFieldNames();
            foreach ($fields as $field) {
                if(!$metadata->isIdentifier($field)) {
                    $setter = 'set' . ucfirst($field);
                    $entityObj = new $entity();
                    if(!method_exists($entityObj, $setter)) {
                        $missing[] = $entity . '::' . $setter;
                    }
                }
            }
        }
        $this->assertEmpty($missing, "There are missing setters:" . $this->getMethodString($missing));
    }

    public function testAssociationGetters()
    {
        $missing = array();
        foreach ($this->entities as $entity) {
            $metadata = $this->em->getClassMetadata($entity);
            $fields = $metadata->getAssociationNames();
            foreach ($fields as $field) {
                $getter = 'get' . ucfirst($field);
                $entityObj = new $entity();
                if(!method_exists($entityObj, $getter)) {
                    $missing[] = $entity . '::' . $getter;
                }
            }
        }
        $this->assertEmpty($missing, "There are missing getters for associations:" . $this->getMethodString($missing));
    }

    public function testAssociationSetters()
    {
        $missing = array();
        foreach ($this->entities as $entity) {
            $metadata = $this->em->getClassMetadata($entity);
            $fields = $metadata->getAssociationNames();
            foreach ($fields as $field) {
                $setter = 'set' . ucfirst($field);
                $entityObj = new $entity();
                if(!method_exists($entityObj, $setter)) {
                    $missing[] = $entity . '::' . $setter;
                }
            }
        }
        $this->assertEmpty($missing, "There are missing setters for associations:" . $this->getMethodString($missing));
    }

    public function testAssociationAdders()
    {
        $missing = array();
        foreach ($this->entities as $entity) {
            $metadata = $this->em->getClassMetadata($entity);
            $fields = $metadata->getAssociationNames();
            foreach ($fields as $field) {
                if(!$metadata->isSingleValuedAssociation($field)) {
                    $adder = 'add' . ucfirst($field);
                    $entityObj = new $entity();
                    if(!method_exists($entityObj, $adder)) {
                        $missing[] = $entity . '::' . $adder;
                    }
                }
            }
        }
        $this->assertEmpty($missing, "There are missing adders for associations:" . $this->getMethodString($missing));
    }

    public function testAssociationRemovers()
    {
        $missing = array();
        foreach ($this->entities as $entity) {
            $metadata = $this->em->getClassMetadata($entity);
            $fields = $metadata->getAssociationNames();
            foreach ($fields as $field) {
                if(!$metadata->isSingleValuedAssociation($field)) {
                    $remover = 'remove' . ucfirst($field);
                    $entityObj = new $entity();
                    if(!method_exists($entityObj, $remover)) {
                        $missing[] = $entity . '::' . $remover;
                    }
                }
            }
        }
        $this->assertEmpty($missing, "There are missing removers for associations:" . $this->getMethodString($missing));
    }

    protected function getMethodString(array $missing)
    {
        return "\n\n\033[0;30m\033[47m" . implode("\033[0m\n\033[0;30m\033[47m", $missing) . "\033[0m\n";
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @param EntityManager $em
     */
    public function setEm($em)
    {
        $this->em = $em;
    }

    /**
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param array $entities
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
    }

}
