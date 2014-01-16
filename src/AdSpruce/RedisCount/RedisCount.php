<?php
/**
 * User: craigpatrick
 * Date: 14/01/2014
 */

namespace AdSpruce\RedisCount;

use Predis\Client;

/**
 * Class RedisCount
 *
 * @package RedisCount
 */
class RedisCount
{

    /**
     * @var mixed
     */
    protected $settings;

    /**
     * @var Client()
     */
    protected $redis;

    /**
     * @var \Predis\Pipeline\PipelineContext
     */
    protected $pipeLine;

    /**
     *
     */
    public function __construct()
    {
        $this->settings = $this->getSettings();
        $this->redis = $this->setConnection($this->getSettings());
        $this->pipeLine = $this->setRedisQueue($this->redis->pipeline());
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        if(isset($this->settings)) {
            return $this->settings;
        }
        $settings = require_once(__DIR__ . '/../../../config/rediscount.local.php');
        return $settings;
    }

    /**
     * @param $settings
     *
     * @return Client
     */
    public function setConnection($settings)
    {
        if (!isset($this->redis)) {
            $this->redis = new Client($settings['redis']);
        }

        return $this->redis;
    }

    /**
     * @return Client
     */
    public function getConnection()
    {
        return $this->setConnection($this->settings);
    }

    /**
     * @param null $pipe
     *
     * @return null|\Predis\Pipeline\PipelineContext
     */
    public function setRedisQueue($pipe)
    {
        $this->pipeLine = $pipe;

        return $this->pipeLine;
    }

    /**
     * @return null|\Predis\Pipeline\PipelineContext
     */
    public function getRedisQueue()
    {
        return $this->pipeLine;
    }

    /**
     * @param null|int $database
     * @codeCoverageIgnore
     */
    public function selectDatabase($database = null)
    {
        if($database === null) {
            // Select default:
            $this->redis->select($this->settings['database']);
        } else {
            $this->redis->select($database);
        }
    }

    /**
     * @param null|string $key
     * @param int         $inc
     *
     * @return bool
     */
    public function incrementStat($key = null, $inc = 1)
    {
        if ($key === null) {
            return false;
        }

        // Do our incrementing thing here:
        return $this->pipeLine->incrby($key, $inc);

    }

    /**
     * @return array
     * @throws \Exception
     */
    public function writeToRedis()
    {
        if (!isset($this->pipeLine)){
            throw new \Exception('No Redis Pipeline Set');
        }

        $results = $this->pipeLine->execute();

        return $results;
    }


}