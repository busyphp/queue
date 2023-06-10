<?php

namespace BusyPHP\queue;

use think\helper\Arr;
use think\helper\Str;
use BusyPHP\queue\command\FlushFailed;
use BusyPHP\queue\command\ForgetFailed;
use BusyPHP\queue\command\Listen;
use BusyPHP\queue\command\ListFailed;
use BusyPHP\queue\command\Restart;
use BusyPHP\queue\command\Retry;
use BusyPHP\queue\command\Work;

/**
 * 服务类
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午5:57 Service.php $
 */
class Service extends \think\Service
{
    use WithQueueConfig;
    
    public function register()
    {
        $this->app->bind('busy.queue', Queue::class);
        $this->app->bind('busy.queue.failer', function() {
            $config = $this->getQueueConfig('failed', []);
            
            $type = Arr::pull($config, 'type', 'none');
            
            $class = str_contains($type, '\\') ? $type : '\\BusyPHP\\queue\\failed\\' . Str::studly($type);
            
            return $this->app->invokeClass($class, [$config]);
        });
    }
    
    
    public function boot()
    {
        $this->commands([
            FlushFailed::class,
            ForgetFailed::class,
            ListFailed::class,
            Retry::class,
            Work::class,
            Restart::class,
            Listen::class,
        ]);
    }
}
