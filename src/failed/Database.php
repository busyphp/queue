<?php

namespace BusyPHP\queue\failed;

use BusyPHP\helper\ArrayHelper;
use Carbon\Carbon;
use think\Db;
use BusyPHP\queue\FailedJob;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;

/**
 * 任务失败处理类 - 存入数据库
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午7:02 Database.php $
 */
class Database extends FailedJob
{
    /** @var Db */
    protected $db;
    
    /**
     * The database table.
     *
     * @var string
     */
    protected $table;
    
    
    public function __construct(Db $db, $table)
    {
        $this->db    = $db;
        $this->table = $table;
    }
    
    
    public static function __make(Db $db, $config)
    {
        return new self($db, ArrayHelper::get($config, 'table') ?: 'plugin_queue_jobs_failed');
    }
    
    
    /**
     * Log a failed job into storage.
     *
     * @param string     $connection
     * @param string     $queue
     * @param string     $payload
     * @param \Exception $exception
     * @return int|null
     */
    public function log($connection, $queue, $payload, $exception)
    {
        $fail_time = Carbon::now()->toDateTimeString();
        
        $exception = (string) $exception;
        
        return $this->getTable()->insertGetId(compact('connection', 'queue', 'payload', 'exception', 'fail_time'));
    }
    
    
    /**
     * Get a list of all of the failed jobs.
     *
     * @return array
     */
    public function all()
    {
        return collect($this->getTable()->order('id', 'desc')->select())->all();
    }
    
    
    /**
     * Get a single failed job.
     * @param mixed $id
     * @return object|null
     * @throws DbException
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     */
    public function find($id)
    {
        return $this->getTable()->find($id);
    }
    
    
    /**
     * Delete a single failed job from storage.
     * @param mixed $id
     * @return bool
     * @throws DbException
     */
    public function forget($id)
    {
        return $this->getTable()->where('id', $id)->delete() > 0;
    }
    
    
    /**
     * Flush all of the failed jobs from storage.
     * @return void
     * @throws DbException
     */
    public function flush()
    {
        $this->getTable()->delete(true);
    }
    
    
    protected function getTable()
    {
        return $this->db->name($this->table);
    }
}
