<?php

namespace BusyPHP\queue;

use BusyPHP\App;
use BusyPHP\exception\ClassNotFoundException;
use BusyPHP\exception\ClassNotImplementsException;
use BusyPHP\helper\StringHelper;
use BusyPHP\queue\contract\QueueDriveInterface;
use BusyPHP\queue\contract\QueueJobInterfaces;
use BusyPHP\queue\task\Job;
use RuntimeException;
use think\facade\Log;

/**
 * 列队类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/12 下午10:11 下午 Queue.php $
 */
class Queue
{
    use QueueConfig;
    
    protected $namespace = 'BusyPHP\\queue\\drives\\';
    
    /**
     * @var App
     */
    protected $app;
    
    /**
     * @var QueueDriveInterface
     */
    protected $drives = [];
    
    
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->app->bind(QueueDriveInterface::class, function() {
            return $this->getDrives();
        });
    }
    
    
    /**
     * 入队
     * @param string|object $handler 队列处理类名或自定义处理器
     * @param mixed         $data 要处理的数据
     * @param int           $delaySecond 延迟执行秒数
     * @return string
     */
    public function push($handler, $data = null, int $delaySecond = 0)
    {
        if (!is_subclass_of($handler, QueueJobInterfaces::class)) {
            throw new ClassNotImplementsException($handler, QueueJobInterfaces::class);
        }
        
        if (is_object($handler)) {
            $payload = $handler;
        } else {
            $payload = new Job($handler, $data);
        }
        
        return $this->getDrives()->push(serialize($payload), $delaySecond);
    }
    
    
    /**
     * 取出一批列队
     * @param int $limit 取出数量
     * @return array
     */
    public function pull($limit = 100) : array
    {
        return $this->getDrives()->pull($limit);
    }
    
    
    /**
     * 获取一条队列信息
     * @return array
     */
    public function get() : array
    {
        return $this->pull(1)[0] ?? [];
    }
    
    
    /**
     * 执行一条队列
     * @param array $info
     */
    public function run(array $info)
    {
        $info['payload'] = $info['payload'] ?? null;
        if (!$info['payload']) {
            return;
        }
        
        // 推到事件中执行
        if (!$info['payload'] instanceof Job) {
            $this->app->event->trigger('busyphp.queue.run', [$info['payload'], $info['id'] ?? null]);
            
            return;
        }
        
        $job     = $info['payload'];
        $handler = $job->getHandler();
        if (!$handler) {
            throw new RuntimeException('Queue processing class not defined');
        }
        
        if (!class_exists($handler)) {
            throw new ClassNotFoundException($handler);
        }
        
        if (!is_subclass_of($handler, QueueJobInterfaces::class)) {
            throw new ClassNotImplementsException($handler, QueueJobInterfaces::class);
        }
        
        /** @var QueueJobInterfaces $object */
        $object = $this->app->invokeClass($handler);
        
        /** @see Job::setRetry() */
        $this->app->invokeMethod([$job, 'setRetry'], [1], true);
        $object->run($job);
        
        // 标记删除
        if ($job->isDestroy()) {
            return;
        }
        
        $this->getDrives()->push(serialize($job), $job->getDelay());
    }
    
    
    /**
     * 批量执行一批队列
     * @param array $list 队列数据由{@see Queue::pull()}取出来的队列
     */
    public function batch(array $list)
    {
        foreach ($list as $item) {
            $this->run($item);
        }
    }
    
    
    /**
     * 获取列队驱动
     * @param string $type
     * @return QueueDriveInterface
     */
    public function getDrives($type = '') : QueueDriveInterface
    {
        if (isset($this->drives[$type])) {
            return $this->drives[$type];
        }
        
        $type = $this->getQueueConfig('type', '') ?: 'db';
        if (false === strpos($type, '\\')) {
            $type = $this->namespace . ucfirst(StringHelper::camel($type));
        }
        
        if (!class_exists($type)) {
            throw new ClassNotFoundException($type);
        }
        
        if (!is_subclass_of($type, QueueDriveInterface::class)) {
            throw new ClassNotImplementsException($type, QueueJobInterfaces::class);
        }
        
        $this->drives[$type] = new $type;
        
        return $this->drives[$type];
    }
    
    
    /**
     * 记录日志
     * @param string $message
     * @param string $type
     */
    public static function log($message, $type = 'error')
    {
        Log::record("busyphp/queue {$message}", $type);
    }
}