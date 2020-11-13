<?php

namespace BusyPHP\queue;

use BusyPHP\App;

/**
 * 列队配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/12 下午10:11 下午 Queue.php $
 * @property App $app;
 */
trait QueueConfig
{
    private $isLoad = false;
    
    
    /**
     * 获取配置
     * @param string $name 配置名称
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function getQueueConfig($name, $default = null)
    {
        if (!$this->isLoad) {
            $this->app->config->load($this->app->getRootPath() . 'config' . DIRECTORY_SEPARATOR . 'extend' . DIRECTORY_SEPARATOR . 'queue.php', 'queue');
            
            $this->isLoad = true;
        }
        
        return $this->app->config->get('queue.' . $name, $default);
    }
}