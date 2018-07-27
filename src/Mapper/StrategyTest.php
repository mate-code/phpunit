<?php


namespace mate\PhpUnit\Mapper;

use Zend\Hydrator\Strategy\StrategyInterface;


/**
 * Class AbstractStrategyTest
 * @package OrderTest\Mapper\Strategy
 * @author Marius Teller <marius.teller@modotex.com>
 */
abstract class StrategyTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var StrategyInterface
     */
    protected $strategy;


    public function testInstanceOfStrategyInterface()
    {
        $interface = StrategyInterface::class;
        $class = is_object($this->strategy) ? get_class($this->strategy) : gettype($this->strategy);
        $this->assertInstanceOf($interface, $this->strategy,
            "$class does not implement $interface");
    }

    /**
     * @return StrategyInterface
     */
    public function getStrategy()
    {
        return $this->strategy;
    }

    /**
     * @param StrategyInterface $strategy
     */
    public function setStrategy(StrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

}
