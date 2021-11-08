<?php
/**
 * 消息列队配置
 */

return [
    // 驱动，目前仅支持 db
    'type'       => 'db',
    
    // 队列异常最大重试次数
    'fail_retry' => 3,
    
    // 队列异常重试延迟秒数
    'fail_delay' => 60,
];