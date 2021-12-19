<?php

namespace BusyPHP\queue\failed;

use BusyPHP\queue\FailedJob;

/**
 * 任务失败处理类 - 无操作
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午7:02 None.php $
 */
class None extends FailedJob
{
    /**
     * Log a failed job into storage.
     *
     * @param string     $connection
     * @param string     $queue
     * @param string     $payload
     * @param \Exception $exception
     */
    public function log($connection, $queue, $payload, $exception)
    {
    }
    
    
    /**
     * Get a list of all of the failed jobs.
     *
     * @return array
     */
    public function all()
    {
        return [];
    }
    
    
    /**
     * Get a single failed job.
     *
     * @param mixed $id
     */
    public function find($id)
    {
    }
    
    
    /**
     * Delete a single failed job from storage.
     *
     * @param mixed $id
     * @return bool
     */
    public function forget($id)
    {
        return true;
    }
    
    
    /**
     * Flush all of the failed jobs from storage.
     *
     * @return void
     */
    public function flush()
    {
    }
}
