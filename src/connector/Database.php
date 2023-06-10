<?php

namespace BusyPHP\queue\connector;

use BusyPHP\helper\ArrayHelper;
use Carbon\Carbon;
use stdClass;
use think\Db;
use think\db\ConnectionInterface;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\db\Query;
use BusyPHP\queue\Connector;
use BusyPHP\queue\InteractsWithTime;
use BusyPHP\queue\job\Database as DatabaseJob;

/**
 * 数据库驱动
 * @author busy^life <busy.life@qq.com>
 * @author yunwuxin <448901948@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 上午11:00 Database.php $
 */
class Database extends Connector
{
    use InteractsWithTime;
    
    protected $db;
    
    /**
     * 表名称
     * @var string
     */
    protected $table;
    
    /**
     * 默认队列名称
     * @var string
     */
    protected $default;
    
    /**
     * 最长作业时长(秒)
     * @var int
     */
    protected $retryAfter;
    
    
    public function __construct(ConnectionInterface $db, $table, $default = 'default', $retryAfter = 60)
    {
        $this->db         = $db;
        $this->table      = $table;
        $this->default    = $default;
        $this->retryAfter = $retryAfter;
    }
    
    
    public static function __make(Db $db, $config)
    {
        $connection = $db->connect(ArrayHelper::get($config, 'connection'));
        
        return new self(
            $connection,
            ArrayHelper::get($config, 'table') ?: 'plugin_queue_jobs',
            ArrayHelper::get($config, 'queue') ?: 'default',
            ArrayHelper::get($config, 'retry_after', 60) ?: 60
        );
    }
    
    
    public function size($queue = null)
    {
        return $this->db->name($this->table)->where('queue', $this->getQueue($queue))->count();
    }
    
    
    public function push($job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($queue, $this->createPayload($job, $data));
    }
    
    
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        return $this->pushToDatabase($queue, $payload);
    }
    
    
    public function later($delay, $job, $data = '', $queue = null)
    {
        return $this->pushToDatabase($queue, $this->createPayload($job, $data), $delay);
    }
    
    
    public function bulk($jobs, $data = '', $queue = null)
    {
        $queue = $this->getQueue($queue);
        
        $availableAt = $this->availableAt();
        
        return $this->db->name($this->table)->insertAll(collect((array) $jobs)
            ->map(function($job) use ($queue, $data, $availableAt) {
                return [
                    'queue'          => $queue,
                    'attempts'       => 0,
                    'reserve_time'   => null,
                    'available_time' => $availableAt,
                    'create_time'    => $this->currentTime(),
                    'payload'        => $this->createPayload($job, $data),
                ];
            })
            ->all());
    }
    
    
    /**
     * 重新发布任务
     *
     * @param string   $queue
     * @param StdClass $job
     * @param int      $delay
     * @return mixed
     */
    public function release($queue, $job, $delay)
    {
        return $this->pushToDatabase($queue, $job->payload, $delay, $job->attempts);
    }
    
    
    /**
     * Push a raw payload to the database with a given delay.
     *
     * @param \DateTime|int $delay
     * @param string|null   $queue
     * @param string        $payload
     * @param int           $attempts
     * @return mixed
     */
    protected function pushToDatabase($queue, $payload, $delay = 0, $attempts = 0)
    {
        return $this->db->name($this->table)->insertGetId([
            'queue'          => $this->getQueue($queue),
            'attempts'       => $attempts,
            'reserve_time'   => null,
            'available_time' => $this->availableAt($delay),
            'create_time'    => $this->currentTime(),
            'payload'        => $payload,
        ]);
    }
    
    
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);
        
        return $this->db->transaction(function() use ($queue) {
            if ($job = $this->getNextAvailableJob($queue)) {
                $job = $this->markJobAsReserved($job);
                
                return new DatabaseJob($this->app, $this, $job, $this->connection, $queue);
            }
        });
    }
    
    
    /**
     * 获取下个有效任务
     *
     * @param string|null $queue
     * @return StdClass|null
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    protected function getNextAvailableJob($queue)
    {
        $job = $this->db->name($this->table)
            ->lock(true)
            ->where('queue', $this->getQueue($queue))
            ->where(function(Query $query) {
                $query->where(function(Query $query) {
                    $query->whereNull('reserve_time')->where('available_time', '<=', $this->currentTime());
                });
                
                //超时任务重试
                $expiration = Carbon::now()->subSeconds($this->retryAfter)->getTimestamp();
                
                $query->whereOr(function(Query $query) use ($expiration) {
                    $query->where('reserve_time', '<=', $expiration);
                });
            })
            ->order('id', 'asc')
            ->find();
        
        return $job ? (object) $job : null;
    }
    
    
    /**
     * 标记任务正在执行.
     * @param stdClass $job
     * @return stdClass
     * @throws DbException
     */
    protected function markJobAsReserved($job)
    {
        $this->db->name($this->table)->where('id', $job->id)->update([
            'reserve_time' => $job->reserve_time = $this->currentTime(),
            'attempts'     => ++$job->attempts,
        ]);
        
        return $job;
    }
    
    
    /**
     * 删除任务
     * @param string $id
     */
    public function deleteReserved($id)
    {
        $this->db->transaction(function() use ($id) {
            if ($this->db->name($this->table)->lock(true)->find($id)) {
                $this->db->name($this->table)->where('id', $id)->delete();
            }
        });
    }
    
    
    protected function getQueue($queue)
    {
        return $queue ?: $this->default;
    }
}
