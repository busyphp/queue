<?php

namespace BusyPHP\queue\interfaces;

/**
 * 列队驱动接口
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/12 下午9:38 下午 QueueDriveInterface.php $
 */
interface QueueDriveInterface
{
    /**
     * 入队
     * @param string $handler 任务处理类名
     * @param mixed  $data 存储的序列化数据
     * @return mixed|void
     */
    public function joinQueue($handler, $data);
    
    
    /**
     * 取出一批列队
     * @param int $limit 列队数量
     * @return array
     */
    public function takeQueueList($limit = 100) : array;
}