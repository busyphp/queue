<?php

namespace BusyPHP\queue;

use BusyPHP\App;
use BusyPHP\helper\util\Arr;
use BusyPHP\helper\util\Str;
use BusyPHP\queue\interfaces\QueueDriveInterface;
use BusyPHP\queue\interfaces\QueueHandlerInterfaces;
use Exception;
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
    
    
    public function __construct()
    {
        $this->app = app();
        $this->app->bind(QueueDriveInterface::class, function() {
            return $this->getDrives();
        });
    }
    
    
    /**
     * 入队
     * @param string $handler 单列处理类名
     * @param mixed  $data 要处理的数据
     * @param int    $execTime 开始执行时间，默认立即执行
     * @return mixed|void
     * @throws QueueException
     */
    public function join(string $handler, $data, $execTime = 0)
    {
        $face = QueueHandlerInterfaces::class;
        if (!is_subclass_of($handler, $face)) {
            throw new QueueException("列队处理类 [{$handler}] 必须集成 [{$face}] 接口");
        }
        
        return $this->getDrives()->joinQueue($handler, $data, $execTime);
    }
    
    
    /**
     * 取出一批列队
     * @param int $limit 取出数量
     * @return array
     */
    public function takeList($limit = 100) : array
    {
        try {
            return $this->getDrives()->takeQueueList($limit);
        } catch (QueueException $e) {
            self::log($e->getMessage());
            
            return [];
        }
    }
    
    
    /**
     * 执行一条队列
     * @param string|mixed $handler 单列处理类名或数据
     * @param mixed        $data 要处理的数据
     * @return bool
     */
    public function exec($handler, $data = null)
    {
        if (is_array($handler) && is_null($data)) {
            if (!Arr::isAssoc($handler)) {
                self::log("处理数据必须是键值对数组");
                
                return false;
            }
            
            $data    = $handler['data'];
            $handler = $handler['handler'];
        }
        
        $api = null;
        try {
            if (!$handler || !class_exists($handler)) {
                throw new QueueException("单列处理类 [{$handler}] 不存在");
            }
            
            $api = new $handler();
            if (!is_subclass_of($api, QueueHandlerInterfaces::class)) {
                $face = QueueHandlerInterfaces::class;
                throw new QueueException("单列处理类 [{$handler}] 没有集成 [{$face}] 接口");
            }
        } catch (Exception $e) {
            self::log("列队执行失败: {$e->getMessage()}");
            
            return false;
        }
        
        
        // 执行列队任务
        $data = $api->handle($data);
        if ($data === true) {
            return true;
        }
        
        $execTime = 0;
        if ($data instanceof QueueExecResult) {
            $execTime = $data->getExecTime();
            $data     = $data->getData();
        }
        
        // 重新入队
        try {
            $errors = implode(', ', $api->getErrors());
            $errors = $errors ?: '--';
            self::log("列队重新入队, 原因: {$errors}");
            $this->join($handler, $data, $execTime);
        } catch (Exception $e) {
            self::log("列队入队失败: {$e->getMessage()}");
        }
        
        return false;
    }
    
    
    /**
     * 批量执行一批队列
     * @param array $list 队列数据由{@see Queue::takeList()}取出来的队列
     */
    public function batch(array $list)
    {
        if (Arr::isAssoc($list)) {
            self::log("批量执行的数据必须是数字索引数组");
            
            return;
        }
        
        foreach ($list as $item) {
            $this->exec($item);
        }
    }
    
    
    /**
     * 获取列队驱动
     * @param string $type
     * @return QueueDriveInterface
     * @throws QueueException
     */
    public function getDrives($type = '') : QueueDriveInterface
    {
        if (isset($this->drives[$type])) {
            return $this->drives[$type];
        }
        
        $type = $this->getQueueConfig('type', '');
        $type = $type ?: 'db';
        if (false === strpos($type, '\\')) {
            $type = $this->namespace . ucfirst(Str::camel($type));
        }
        
        if (!class_exists($type)) {
            throw new QueueException("列队驱动不存在: {$type}");
        }
        
        if (!is_subclass_of($type, QueueDriveInterface::class)) {
            $face = QueueDriveInterface::class;
            throw new QueueException("列队驱动类 [{$type}] 必须集成 [{$face}] 接口");
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