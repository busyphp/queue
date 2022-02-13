<?php

namespace BusyPHP\queue;

use BusyPHP\App;

/**
 *
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午6:47 InteractsWithFailed.php $
 * @property App $app
 */
trait InteractsWithFailed
{
    /**
     * 获取失败处理对象
     * @return FailedJob|object
     */
    public function getQueueFailed()
    {
        return $this->app->get('busy.queue.failer');
    }
}