<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace BusyPHP\queue;

use BusyPHP\queue\contract\JobFailedInterface;
use BusyPHP\queue\contract\JobInterface;
use BusyPHP\App;
use Throwable;

abstract class Job
{
    /**
     * The job handler instance.
     * @var mixed|JobInterface|JobFailedInterface
     */
    protected $instance;
    
    /**
     * @var App
     */
    protected $app;
    
    /**
     * The name of the queue the job belongs to.
     * @var string
     */
    protected $queue;
    
    /**
     * The name of the connection the job belongs to.
     */
    protected $connection;
    
    /**
     * Indicates if the job has been deleted.
     * @var bool
     */
    protected $deleted = false;
    
    /**
     * Indicates if the job has been released.
     * @var bool
     */
    protected $released = false;
    
    /**
     * Indicates if the job has failed.
     *
     * @var bool
     */
    protected $failed = false;
    
    
    /**
     * Get the decoded body of the job.
     *
     * @return array
     */
    public function payload()
    {
        return json_decode($this->getRawBody(), true);
    }
    
    
    /**
     * Fire the job.
     * @return void
     */
    public function fire()
    {
        $payload = $this->payload();
        
        [$class, $method] = $this->parseJob($payload['job']);
        
        $this->instance = $this->resolve($class);
        if ($this->instance) {
            $this->instance->{$method}($this, $payload['data']);
        }
    }
    
    
    /**
     * Delete the job from the queue.
     * @return void
     */
    public function delete()
    {
        $this->deleted = true;
    }
    
    
    /**
     * Determine if the job has been deleted.
     * @return bool
     */
    public function isDeleted()
    {
        return $this->deleted;
    }
    
    
    /**
     * Release the job back into the queue.
     * @param int $delay
     * @return void
     */
    public function release($delay = 0)
    {
        $this->released = true;
    }
    
    
    /**
     * Determine if the job was released back into the queue.
     * @return bool
     */
    public function isReleased()
    {
        return $this->released;
    }
    
    
    /**
     * Determine if the job has been deleted or released.
     * @return bool
     */
    public function isDeletedOrReleased()
    {
        return $this->isDeleted() || $this->isReleased();
    }
    
    
    /**
     * Get the job identifier.
     *
     * @return string
     */
    abstract public function getJobId();
    
    
    /**
     * Get the number of times the job has been attempted.
     * @return int
     */
    abstract public function attempts();
    
    
    /**
     * Get the raw body string for the job.
     * @return string
     */
    abstract public function getRawBody();
    
    
    /**
     * 解析任务类
     * @param string $job
     * @return array
     * @see JobInterface::fire()
     */
    protected function parseJob($job)
    {
        $segments = explode('@', $job);
        
        return count($segments) > 1 ? $segments : [$segments[0], 'fire'];
    }
    
    
    /**
     * 初始化任务类
     * @param string $name
     * @return mixed|JobInterface
     */
    protected function resolve($name)
    {
        if (strpos($name, '\\') === false) {
            if (strpos($name, '/') === false) {
                $app = '';
            } else {
                [$app, $name] = explode('/', $name, 2);
            }
            
            $name = ($this->app->config->get('app.app_namespace') ?: 'app\\') . ($app ? strtolower($app) . '\\' : '') . 'job\\' . $name;
        }
        
        return $this->app->make($name);
    }
    
    
    /**
     * Determine if the job has been marked as a failure.
     *
     * @return bool
     */
    public function hasFailed()
    {
        return $this->failed;
    }
    
    
    /**
     * Mark the job as "failed".
     *
     * @return void
     */
    public function markAsFailed()
    {
        $this->failed = true;
    }
    
    
    /**
     * Process an exception that caused the job to fail.
     * @param Throwable $e
     * @return void
     */
    public function failed(Throwable $e)
    {
        $this->markAsFailed();
        
        $payload = $this->payload();
        
        [$class, $method] = $this->parseJob($payload['job']);
        
        $this->instance = $this->resolve($class);
        if (method_exists($this->instance, 'failed')) {
            $this->instance->failed($payload['data'], $e);
        }
    }
    
    
    /**
     * Get the number of times to attempt a job.
     *
     * @return int|null
     */
    public function maxTries()
    {
        return $this->payload()['maxTries'] ?? null;
    }
    
    
    /**
     * Get the number of seconds the job can run.
     *
     * @return int|null
     */
    public function timeout()
    {
        return $this->payload()['timeout'] ?? null;
    }
    
    
    /**
     * Get the timestamp indicating when the job should timeout.
     *
     * @return int|null
     */
    public function timeoutAt()
    {
        return $this->payload()['timeoutAt'] ?? null;
    }
    
    
    /**
     * Get the name of the queued job class.
     *
     * @return string
     */
    public function getName()
    {
        return $this->payload()['job'];
    }
    
    
    /**
     * Get the name of the connection the job belongs to.
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }
    
    
    /**
     * Get the name of the queue the job belongs to.
     * @return string
     */
    public function getQueue()
    {
        return $this->queue;
    }
}
