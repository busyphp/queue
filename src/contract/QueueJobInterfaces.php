<?php

namespace BusyPHP\queue\contract;

use BusyPHP\queue\task\Job;

/**
 * 列队处理接口
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/12 下午9:59 下午 QueueHandlerInterfaces.php $
 */
interface QueueJobInterfaces
{
    /**
     * 处理队列数据
     * @param Job $job
     */
    public function run(Job $job) : void;
}