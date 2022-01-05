<?php
/**
 * 队列配置
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午12:36 config.php $
 */

return [
    // 默认队列连接器
    'default'     => 'sync',
    
    // 队列连接器配置
    'connections' => [
        // 同步
        'sync'     => [
            'type' => 'sync',
        ],
        
        // 数据库
        'database' => [
            'type'       => 'database',
            'queue'      => 'default',
            'table'      => 'system_jobs',
            'connection' => null,
        ],
        
        // redis
        'redis'    => [
            'type'       => 'redis',
            'queue'      => 'default',
            'host'       => '127.0.0.1',
            'port'       => 6379,
            'password'   => '',
            'select'     => 0,
            'timeout'    => 0,
            'persistent' => false,
            'prefix'     => '',
        ],
        
        // 更多驱动...
    ],
    
    // 任务超过尝试次数上限的处理方式
    'failed'      => [
        'type'  => 'database',
        'table' => 'system_jobs_failed',
    ],
];
