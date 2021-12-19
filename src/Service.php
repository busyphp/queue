<?php

namespace BusyPHP\queue;

use think\helper\Arr;
use think\helper\Str;
use BusyPHP\queue\command\FailedTable;
use BusyPHP\queue\command\FlushFailed;
use BusyPHP\queue\command\ForgetFailed;
use BusyPHP\queue\command\Listen;
use BusyPHP\queue\command\ListFailed;
use BusyPHP\queue\command\Restart;
use BusyPHP\queue\command\Retry;
use BusyPHP\queue\command\Table;
use BusyPHP\queue\command\Work;

class Service extends \think\Service
{
    public function register()
    {
        $this->app->bind('queue', Queue::class);
        $this->app->bind('queue.failer', function() {
            $config = $this->app->config->get('queue.failed', []);
            
            $type = Arr::pull($config, 'type', 'none');
            
            $class = false !== strpos($type, '\\') ? $type : '\\think\\queue\\failed\\' . Str::studly($type);
            
            return $this->app->invokeClass($class, [$config]);
        });
    }
    
    
    public function boot()
    {
        $this->commands([
            FailedJob::class,
            Table::class,
            FlushFailed::class,
            ForgetFailed::class,
            ListFailed::class,
            Retry::class,
            Work::class,
            Restart::class,
            Listen::class,
            FailedTable::class,
        ]);
    }
}
