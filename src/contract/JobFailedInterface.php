<?php

namespace BusyPHP\queue\contract;

use Throwable;

/**
 * QueueJob作业失败接口类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午2:13 JobFailedInterface.php $
 */
interface JobFailedInterface
{
    /**
     * 执行任务达到最大重试次数后失败
     * @param mixed     $data 发布任务时自定义的数据
     * @param Throwable $e 异常
     */
    public function failed($data, Throwable $e) : void;
}