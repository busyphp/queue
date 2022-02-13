消费队列模块
===============

## 安装方式

```shell script
composer require busyphp/queue
```

> 安装完成后可以通过后台管理 > 开发模式 > 插件管理进行 `安装/卸载/管理`

## 命令行

`cd` 到到项目根目录下执行

### `bp:queue:work` 命令

> 该命令将启动一个 work 进程来处理消息队列

```shell script
php think bp:queue:work
```

| 参数 | 默认值 | 说明 |
| :----- | :-----: | :----- |
| connection | sync  | 设置队列连接器名称，默认依据 `config/queue.php` 中的 `default` 确定  |
| --queue | default  | 设置执行的队列名称 |
| --once | -  | 仅处理队列上的下一个任务后就退出 |
| --delay | 0  |  如果本次任务执行抛出异常且任务未被删除时，设置其下次执行前延迟多少秒 |
| --memory | 128  | 该进程允许使用的内存上限，以 M 为单位 |
| --timeout | 60  | 该进程的允许执行的最长时间，以秒为单位 |
| --sleep | 3 | 如果队列中无任务，则多长时间后重新检查 |
| --tries | 0 | 如果任务已经超过尝试次数上限，0为不限，则触发当前任务类型下的failed()方法 |

### `bp:queue:listen` 命令

> listen命令所在的父进程会创建一个单次执行模式的work子进程，并通过该work子进程来处理队列中的下一个消息，当这个work子进程退出之后，listen命令所在的父进程会监听到该子进程的退出信号，并重新创建一个新的单次执行的work子进程

```shell script
php think bp:queue:listen
```

| 参数 | 默认值 | 说明 |
| :----- | :-----: | :----- |
| connection | sync  | 设置队列连接器名称，默认依据 `config/queue.php` 中的 `default` 确定  |
| --queue | default  | 设置执行的队列名称 |
| --delay | 0  |  如果本次任务执行抛出异常且任务未被删除时，设置其下次执行前延迟多少秒 |
| --memory | 128  | 子进程允许使用的内存上限，以 M 为单位 |
| --timeout | 60  | 子进程的允许执行的最长时间，以秒为单位 |
| --sleep | 3 | 如果队列中无任务，则多长时间后重新检查 |
| --tries | 0 | 如果任务已经超过尝试次数上限，0为不限，则触发当前任务类型下的failed()方法 |

### `bp:queue:failed` 列出所有失败的任务

```shell script
php think bp:queue:failed
```
### `bp:queue:flush` 刷新所有失败的任务

```shell script
php think bp:queue:flush
```

### `bp:queue:forget` 强制执行一条失败的任务
```shell script
php think bp:queue:forget id 1(失败任务ID)
```

### `bp:queue:retry` 将一批失败的任务进行重试
```shell script
php think bp:queue:forget id 1,2,3
```

### `bp:queue:restart` 重启进程
```shell script
php think bp:queue:restart
```


## 配置 `config/busy-queue.php`

```php
<?php
return [
    // 默认驱动
    'default'     => 'sync',
    
    // 队列驱动配置
    'connections' => [
        // 同步驱动
        'sync'     => [
            'type' => 'sync',
        ],
        
        // 数据库驱动
        'database' => [
            'type'       => 'database',
            'queue'      => 'default',
            'table'      => 'system_jobs',
            'connection' => null,
        ],
        
        // redis驱动
        'redis'    => [
            'type'       => 'redis',
            'queue'      => 'default',
            'host'       => '127.0.0.1',
            'port'       => 6379,
            'password'   => '',
            'select'     => 0,
            'timeout'    => 0,
            'persistent' => false,
        ],
        
        // 更多驱动...
    ],
    
    // 任务失败
    'failed' => [
        'type'  => 'none',
        'table' => 'system_jobs_failed',
    ],
];
```

### 创建任务类

```php
<?php
use BusyPHP\queue\contract\JobFailedInterface;
use BusyPHP\queue\contract\JobInterface;
use BusyPHP\queue\Job;

class TestJob implements JobInterface, JobFailedInterface
{
    /**
     * 执行任务
     * @param Job   $job 任务对象
     * @param mixed $data 发布任务时自定义的数据
     */
    public function fire(Job $job, $data) : void
    {
        //....这里执行具体的任务 
        
        if ($job->attempts() > 3) {
             //通过这个方法可以检查这个任务已经重试了几次了
        }
        
        
        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        $job->delete();
        
        // 也可以重新发布这个任务
        $job->release($delay); //$delay为延迟时间
    }
    
    
    /**
     * 执行任务达到最大重试次数后失败
     * @param mixed     $data 发布任务时自定义的数据
     * @param Throwable $e 异常
     */
    public function failed($data, Throwable $e) : void
    {
        // ...任务达到最大重试次数后，失败了
    }
}
```

### 发布任务

```php
<?php
// 发布一条任务到队列中
\BusyPHP\queue\facade\Queue::push($job, $data); 

// 发布一条延迟执行的任务到队列中
\BusyPHP\queue\facade\Queue::later(10, $job, $data); 

// 向 database 队列连接器中发布一条任务
\BusyPHP\queue\facade\Queue::connection('database')->push($job, $data);

// 向 redis 队列连接器中发布一条任务
\BusyPHP\queue\facade\Queue::connection('redis')->push($job, $data);
```