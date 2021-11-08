<?php

namespace BusyPHP\queue\contract;

/**
 * 列队驱动接口
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午2:26 QueueDriveInterface.php $
 */
interface QueueDriveInterface
{
    /**
     * 入队
     * @param string $payload 队列数据
     * @param int    $delaySecond 延迟执行秒数
     * @return string 队列ID
     */
    public function push(string $payload, $delaySecond = 0);
    
    
    /**
     * 取出一批列队
     * @param int $limit 列队数量
     * @return array
     */
    public function pull($limit = 100) : array;
}