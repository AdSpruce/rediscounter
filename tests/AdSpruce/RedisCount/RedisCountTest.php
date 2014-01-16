<?php
/**
 * User: craigpatrick
 * Date: 14/01/2014
 */

namespace tests\AdSpruce\RedisCount;

use AdSpruce\RedisCount\RedisCount;
use PHPUnit_Framework_TestCase;
use Predis\Client;

/**
 * Class RedisCountTest
 *
 * @package tests\AdSpruce\RedisCount
 */
class RedisCountTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var RedisCount $redisCount
     */
    protected $redisCount;

    /**
     * @var mixed
     */
    protected $settings;

    /**
     * @var Client()
     */
    protected $redis;

    /**
     * @var Client $mockPredis
     */
    protected $mockPredis;

    /**
     * @var \Predis\Pipeline\PipelineContext
     */
    protected $pipeLine;


    /**
     *
     */
    public function setUp()
    {
        // Create mock Predis Class:
        $this->mockPredis = $this->getMockBuilder('Predis\Client')
            ->setMethods(
                array(
                     'pipeline'
                )
            )
            ->getMock();
        $this->redis = new Client();
        $this->redisCount = new RedisCount();
    }

    /**
     *
     */
    public function tearDown()
    {
        $this->redisCount = null;
        $this->redis = null;
    }

    /**
     *
     */
    public function testAssertionsForSetup()
    {
        $this->assertInstanceOf(
            'AdSpruce\RedisCount\RedisCount', $this->redisCount, '$this->redisCount is not an instance of RedisCount'
        );
        $this->assertArrayHasKey(
            'redis', $this->redisCount->getSettings(), 'Redis key not found in configuration array'
        );
        $this->assertInstanceOf(
            'Predis\Client', $this->redisCount->setConnection($this->redisCount->getSettings()),
            '$this->redis is not an instance of Predis Client'
        );
        $this->assertInstanceOf(
            'Predis\Pipeline\PipelineContext', $this->redisCount->setRedisQueue($this->redis->pipeline()),
            '$this->pipeLine is not an instance of the Predis Pipe'
        );
    }

    /**
     *
     */
    public function testSetConnectionMethod()
    {
        $result = $this->redisCount->setConnection($this->redisCount->getSettings());
        $this->assertInstanceOf('Predis\Client', $result, 'setConnection() did not return an instance of Predis');
    }

    /**
     *
     */
    public function testGetConnectionMethod()
    {
        $result = $this->redisCount->getConnection();
        $this->assertInstanceOf('Predis\Client', $result, 'getConnection() did not return an instance of Predis');
    }

    /**
     *
     */
    public function testSetRedisQueueMethod()
    {
        $results = $this->redisCount->setRedisQueue($this->redis->pipeline());
        $this->assertInstanceOf(
            'Predis\Pipeline\PipelineContext', $results, '$this->pipeLine is not an instance of the Predis Pipe'
        );
    }

    /**
     *
     */
    public function testIncrementStatMethodWithNullValue()
    {
        $result = $this->redisCount->incrementStat();

        $this->assertEquals(false, $result, 'incrementStat method with NULL value did not return FALSE');
    }

    /**
     *
     */
    public function testIncrementStatMethodWithKey()
    {
        $result = $this->redisCount->incrementStat('test.key');

        $this->assertInstanceOf(
            'Predis\Pipeline\PipelineContext',
            $result,
            'incrementStat method with KEY VALUE value did not return an instance of PipelineContext');
    }

    public function testGetRedisQueueMethod()
    {
        $result = $this->redisCount->getRedisQueue();

        $this->assertInstanceOf(
            'Predis\Pipeline\PipelineContext',
            $result,
            'getRedisQueue did not return an instance of PipelineContext');
    }

    /**
     * Write Method test - should return an array if successful, or string if failed
     */
    public function testWriteToRedisMethodWithActivePipe()
    {
        $result = $this->redisCount->writeToRedis();
        $this->assertThat($result, $this->isType('array'), 'writeToRedis method did not return an array');
    }

    /**
     * Write Method test - should return an array if successful, or string if failed
     */
    public function testWriteToRedisMethodWithNoPipe()
    {
        // Set redis to null
        $this->redisCount->setRedisQueue(null);
        $this->setExpectedException('Exception', 'No Redis Pipeline Set');
        $this->redisCount->writeToRedis();
    }


    // ** ------------ DATA PROVIDER FUNCTIONS --------------- ** //
    // -------------------------------------------------------------

    /**
     * @return array
     */
    public function providerForFunction()
    {
        return array(
            array(null, false, 'error message in here'),
        );
    }
}
 