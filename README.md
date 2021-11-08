消费队列模块
===============

## 安装方式

```shell script
composer require busyphp/queue
```

> 安装完成后可以通过后台管理 > 开发模式 > 插件管理进行 `安装/卸载/管理`

## 启动队列

`cd` 到到项目根目录下执行

### 启动命令

```shell script
php think swoole
```

### 停止命令
```shell script
php think swoole stop
```

### 重启命令
```shell script
php think swoole restart
```

### 在`www`用户下运行

```shell script
su -c "php think swoole start|stop|restart" -s /bin/sh www
```

### 配置 `config/extend/queue.php`

```php
<?php
return [
    // 是否启用
    'enable' => false,

    // 驱动，目前仅支持 db
    'type' => 'db',

    // 队列异常最大重试次数
    'fail_retry' => 3,

    // 队列异常重试延迟秒数
    'fail_delay' => 60,
];
```

### 创建任务类

```php
<?php
use BusyPHP\queue\contract\QueueJobInterfaces;
use BusyPHP\queue\task\Job;

class QueueDemo implements QueueJobInterfaces
{
    /**
     * 执行列队
     * @param Job $job 要处理的数据
     */
    public function run(Job $job) : void
    {
        // 通过这个方法可以检查这个任务已经重试了几次了
        if ($job->retry() > 3) {
            // ...
        }
        
        // 处理数据
        $data = $job->data();

        // 处理业务成功要记得销毁队列
        if (true) {
            $job->destroy();
        } else {
            // 重新发布
            // 注意：release之后不能在抛出异常
            $job->release(); 
        } 
    }
}
```