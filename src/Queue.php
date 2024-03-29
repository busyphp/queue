<?php

namespace BusyPHP\queue;

use BusyPHP\App;
use think\Manager;
use BusyPHP\queue\connector\Database;
use BusyPHP\queue\connector\Redis;

/**
 * 队列
 * @author busy^life <busy.life@qq.com>
 * @author yunwuxin <448901948@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午12:21 Queue.php $
 * @mixin Database
 * @mixin Redis
 * @property App $app
 */
class Queue extends Manager
{
    use WithQueueConfig;
    
    protected $namespace = '\\BusyPHP\\queue\\connector\\';
    
    
    protected function resolveType(string $name)
    {
        return $this->getQueueConfig("connections.{$name}.type", 'sync');
    }
    
    
    protected function resolveConfig(string $name)
    {
        return $this->getQueueConfig("connections.{$name}");
    }
    
    
    protected function createDriver(string $name)
    {
        /** @var Connector $driver */
        $driver = parent::createDriver($name);
        
        return $driver->setApp($this->app)->setConnection($name);
    }
    
    
    /**
     * 切换队列连接器名称
     * @param null|string $name
     * @return Connector
     */
    public function connection(?string $name = null) : Connector
    {
        return $this->driver($name);
    }
    
    
    /**
     * 默认驱动
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->getQueueConfig('default');
    }
}
