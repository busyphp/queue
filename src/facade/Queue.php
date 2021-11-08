<?php

namespace BusyPHP\queue\facade;

use think\Facade;

/**
 * Queue工厂类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/12 下午10:34 下午 Queue.php $
 * @mixin \BusyPHP\queue\Queue
 * @see \BusyPHP\queue\Queue
 * @method static string push(string|object $handler, mixed $data, int $delaySecond = 0) 将数据加入队列
 * @method static array pull(int $limit = 100) 取出一批队列
 * @method static array get() 取出一条队列
 * @method static bool run($handler, $data = null) 执行一条队列
 * @method static void batch(array $list) 批量执行一批队列
 */
class Queue extends Facade
{
    protected static function getFacadeClass()
    {
        return \BusyPHP\queue\Queue::class;
    }
}