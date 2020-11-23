<?php

namespace BusyPHP\queue\drives;

use BusyPHP\exception\SQLException;
use BusyPHP\Model;
use BusyPHP\queue\interfaces\QueueDriveInterface;
use BusyPHP\queue\Queue;

/**
 * 数据库消息列队
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/7/17 下午9:18 下午 SystemQueue.php $
 */
class Db extends Model implements QueueDriveInterface
{
    public $name = 'SystemQueue';
    
    
    /**
     * 入队
     * @param string $handler 任务处理类名
     * @param mixed  $data 执行的数据
     * @param int    $execTime 执行时间，0为立即执行
     * @return int
     * @throws SQLException
     */
    public function joinQueue($handler, $data, $execTime = 0)
    {
        if (!$insertId = $this->addData([
            'create_time' => time(),
            'exec_time'   => $execTime,
            'params'      => serialize([
                'handler' => $handler,
                'data'    => $data,
            ])
        ])) {
            throw new SQLException('插入消息列队失败', $this);
        }
        
        return $insertId;
    }
    
    
    /**
     * 取出一批列队
     * @param int $limit 列队数量
     * @return array
     */
    public function takeQueueList($limit = 100) : array
    {
        $this->startTrans();
        try {
            $list = $this->field('id,params')
                ->lock(true)
                ->where('exec_time', '<', time())
                ->order('id ASC')
                ->limit($limit)
                ->selectList();
            
            // 删除列队
            if ($list) {
                if (false === $this->where('id', 'in', array_column($list, 'id'))->deleteData()) {
                    throw new SQLException('删除消息列队失败', $this);
                }
            }
            
            $this->commit();
            
            return $list;
        } catch (SQLException $e) {
            $this->rollback();
            
            Queue::log("取出列队失败, Message: {$e->getMessage()}, SQL: {$e->getLastSQL()} ErrorSQL: {$e->getErrorSQL()}");
            
            return [];
        }
    }
    
    
    public static function parseList($list)
    {
        return parent::parseList($list, function($list) {
            foreach ($list as $i => $r) {
                $info = unserialize($r['params']);
                unset($r['params']);
                $r['handler'] = $info['handler'];
                $r['data']    = $info['data'];
                $list[$i]     = $r;
            }
            
            return $list;
        });
    }
}