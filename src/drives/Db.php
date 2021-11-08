<?php

namespace BusyPHP\queue\drives;

use BusyPHP\app\admin\model\system\lock\SystemLock;
use BusyPHP\Model;
use BusyPHP\queue\contract\QueueDriveInterface;
use Exception;
use think\db\exception\DbException;

/**
 * 数据库消息列队
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午2:27 Db.php $
 */
class Db extends Model implements QueueDriveInterface
{
    public $name = 'SystemQueue';
    
    
    /**
     * 入队
     * @param string $payload 队列数据
     * @param int    $delaySecond 延迟执行秒数
     * @return string 队列ID
     * @throws DbException
     */
    public function push(string $payload, $delaySecond = 0)
    {
        $time = time();
        
        return $this->addData([
            'create_time' => $time,
            'delay_time'  => $time + $delaySecond,
            'payload'     => $payload
        ]);
    }
    
    
    /**
     * 取出一批列队
     * @param int $limit 列队数量
     * @return array
     * @throws Exception
     */
    public function pull($limit = 100) : array
    {
        return SystemLock::init()->do("plugins_queue_take_list", function() use ($limit) {
            $list = $this->field('id,payload')
                ->lock(true)
                ->where('delay_time', '<', time())
                ->order('id', 'asc')
                ->limit($limit)
                ->selectList();
            
            // 删除
            if ($list) {
                $this->where('id', 'in', array_column($list, 'id'))->delete();
            }
            
            return $list;
        }, 'busyphp/queue取出队列锁');
    }
    
    
    protected function onParseBindList(array &$list)
    {
        foreach ($list as $i => $item) {
            $item['payload'] = unserialize($item['payload']) ?: null;
            $list[$i]        = $item;
        }
    }
}