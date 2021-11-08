<?php

namespace BusyPHP\queue;

use BusyPHP\queue\task\Task;

/**
 * 服务
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午1:29 Service.php $
 */
class Service extends \think\Service
{
    public function boot()
    {
        // 注入任务
        $swoole                    = $this->app->config->get('swoole', []);
        $swoole['task']['workers'] = $swoole['task']['workers'] ?? [];
        if (!in_array(Task::class, $swoole['task']['workers'])) {
            $swoole['task']['workers'][] = Task::class;
            $this->app->config->set($swoole, 'swoole');
        }
    }
}