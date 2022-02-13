<?php

namespace BusyPHP\queue;

use BusyPHP\App;

/**
 * WithQueueConfig
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2022 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2022/2/13 2:02 PM WithQueueConfig.php $
 * @property App $app
 */
trait WithQueueConfig
{
    /**
     * 获取队列配置
     * @param string $name
     * @param mixed  $default
     * @return mixed
     */
    public function getQueueConfig(string $name, $default = null)
    {
        if (isset($this->app)) {
            $app = $this->app;
        } else {
            $app = App::getInstance();
        }
        
        return $app->config->get("busy-queue.{$name}", $default);
    }
}