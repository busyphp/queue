<?php
/**
 * 辅助类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午7:06 helper.php $
 */

use BusyPHP\queue\facade\Queue;

if (!function_exists('queue')) {
    /**
     * 添加到队列
     * @param mixed                 $job 任务Job
     * @param mixed                 $data 任务数据
     * @param int|DateTimeInterface $delay 延迟执行秒数
     * @param string|null           $queue 队列名称
     */
    function queue($job, $data = '', $delay = 0, ?string $queue = null)
    {
        if ($delay > 0) {
            Queue::later($delay, $job, $data, $queue);
        } else {
            Queue::push($job, $data, $queue);
        }
    }
}
