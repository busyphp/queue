<?php

namespace BusyPHP\queue;

use BusyPHP\App;
use DateTimeInterface;
use InvalidArgumentException;

/**
 * 驱动连接器基本类
 * @author busy^life <busy.life@qq.com>
 * @author yunwuxin <448901948@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 上午11:02 Connector.php $
 */
abstract class Connector
{
    /**
     * @var App
     */
    protected $app;
    
    /**
     * 队列连接器名称
     * @var string
     */
    protected $connection;
    
    protected $options = [];
    
    
    /**
     * 获取队列长度
     * @param string|null $queue 队列名称
     * @return int
     */
    abstract public function size($queue = null);
    
    
    /**
     * 发布一条任务到队列中
     * @param mixed       $job 消费Job
     * @param mixed       $data 消费的数据
     * @param string|null $queue 队列名称，默认为default
     * @return mixed
     */
    abstract public function push($job, $data = '', $queue = null);
    
    
    /**
     * 向某个队列中发布一条任务
     * @param string $queue 队列名称
     * @param mixed  $job 消费Job
     * @param mixed  $data 消费的数据
     * @return mixed
     */
    public function pushOn($queue, $job, $data = '')
    {
        return $this->push($job, $data, $queue);
    }
    
    
    /**
     * 发布一条自定义数据的任务到队列中
     * @param string      $payload 自定义队列数据
     * @param string|null $queue 队列名称，默认为default
     * @param array       $options 队列配置
     * @return mixed
     */
    abstract public function pushRaw($payload, $queue = null, array $options = []);
    
    
    /**
     * 发布一条延迟执行的任务到队列中
     * @param int|DateTimeInterface $delay 延迟执行秒数
     * @param mixed                 $job 任务Job
     * @param string                $data 任务数据
     * @param string|null           $queue 队列名称
     * @return mixed
     */
    abstract public function later($delay, $job, $data = '', $queue = null);
    
    
    /**
     * 向某个队列发布一条延迟执行的任务
     * @param string                $queue 队列名称
     * @param int|DateTimeInterface $delay 延迟执行秒数
     * @param string                $job 任务Job
     * @param mixed                 $data 队列名称
     * @return mixed
     */
    public function laterOn($queue, $delay, $job, $data = '')
    {
        return $this->later($delay, $job, $data, $queue);
    }
    
    
    /**
     * 将任务数据批量分配到不同的任务Job中
     * @param array       $jobs 任务JOB集合
     * @param mixed       $data 任务数据
     * @param string|null $queue 队列名称
     */
    public function bulk($jobs, $data = '', $queue = null)
    {
        foreach ((array) $jobs as $job) {
            $this->push($job, $data, $queue);
        }
    }
    
    
    /**
     * 取出一条任务
     * @param string|null $queue 队列名称
     * @return mixed
     */
    abstract public function pop($queue = null);
    
    
    protected function createPayload($job, $data = '')
    {
        $payload = $this->createPayloadArray($job, $data);
        
        $payload = json_encode($payload);
        
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('Unable to create payload: ' . json_last_error_msg());
        }
        
        return $payload;
    }
    
    
    protected function createPayloadArray($job, $data = '')
    {
        return is_object($job) ? $this->createObjectPayload($job) : $this->createPlainPayload($job, $data);
    }
    
    
    protected function createPlainPayload($job, $data)
    {
        return [
            'job'      => $job,
            'maxTries' => null,
            'timeout'  => null,
            'data'     => $data,
        ];
    }
    
    
    protected function createObjectPayload($job)
    {
        return [
            'job'       => 'BusyPHP\queue\CallQueuedHandler@call',
            'maxTries'  => $job->tries ?? null,
            'timeout'   => $job->timeout ?? null,
            'timeoutAt' => $this->getJobExpiration($job),
            'data'      => [
                'commandName' => get_class($job),
                'command'     => serialize(clone $job),
            ],
        ];
    }
    
    
    public function getJobExpiration($job)
    {
        if (!method_exists($job, 'retryUntil') && !isset($job->timeoutAt)) {
            return;
        }
        
        $expiration = $job->timeoutAt ?? $job->retryUntil();
        
        return $expiration instanceof DateTimeInterface ? $expiration->getTimestamp() : $expiration;
    }
    
    
    protected function setMeta($payload, $key, $value)
    {
        $payload       = json_decode($payload, true);
        $payload[$key] = $value;
        $payload       = json_encode($payload);
        
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new InvalidArgumentException('Unable to create payload: ' . json_last_error_msg());
        }
        
        return $payload;
    }
    
    
    public function setApp(App $app)
    {
        $this->app = $app;
        
        return $this;
    }
    
    
    /**
     * Get the connector name for the queue.
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    
    /**
     * Set the connector name for the queue.
     *
     * @param string $name
     * @return $this
     */
    public function setConnection($name)
    {
        $this->connection = $name;
        
        return $this;
    }
}
