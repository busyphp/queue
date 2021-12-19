<?php

namespace BusyPHP\queue\facade;

use BusyPHP\queue\Connector;
use think\Facade;

/**
 * 队列工厂类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午12:20 Queue.php $
 * @mixin \BusyPHP\queue\Queue
 * @method static int size(?string $queue = null) 获取队列长度
 * @method static mixed push(mixed $job, mixed $data = '', ?string $queue = null) 发布一条任务到队列中
 * @method static mixed pushOn(string $queue, mixed $job, mixed $data = '') 向某个队列中发布一条任务
 * @method static mixed pushRaw(string $payload, ?string $queue = null, array $options = []) 发布一条自定义任务到队列中
 * @method static mixed later(int|\DateTimeInterface $delay, mixed $job, mixed $data = '', ?string $queue = null) 发布一条延迟任务到队列中
 * @method static mixed laterOn(string $queue, int|\DateTimeInterface $delay, mixed $job, mixed $data = '') 向某个队列中发布一条延迟执行任务
 * @method static mixed bulk(array $jobs, mixed $data = '', ?string $queue = null) 批量发布任务数据到不同任务中
 * @method static mixed pop(?string $queue = null) 取一条任务出来
 * @method static Connector connection(?string $name = null) 切换队列连接器名称
 */
class Queue extends Facade
{
    protected static function getFacadeClass()
    {
        return \BusyPHP\queue\Queue::class;
    }
}
