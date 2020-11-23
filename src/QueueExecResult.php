<?php

namespace BusyPHP\queue;

/**
 * 列队执行返回结果
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/12 下午10:11 下午 QueueExecResult.php $
 */
class QueueExecResult
{
    /**
     * 执行数据
     * @var mixed
     */
    private $data;
    
    /**
     * 执行时间
     * @var int
     */
    private $execTime = 0;
    
    
    /**
     * QueueExecResult constructor.
     * @param mixed $data 执行数据
     * @param int   $execTime 执行时间
     */
    public function __construct($data, $execTime = 0)
    {
        $this->setData($data);
        $this->setExecTime($execTime);
    }
    
    
    /**
     * 快速实例化
     * @param mixed $data 执行数据
     * @param int   $execTime 执行时间
     * @return $this
     */
    public static function init($data, $execTime = 0)
    {
        return new static($data, $execTime);
    }
    
    
    /**
     * 设置执行数据
     * @param mixed $data
     * @return $this
     */
    public function setData($data) : self
    {
        $this->data = $data;
        
        return $this;
    }
    
    
    /**
     * 设置执行时间
     * @param int $execTime
     * @return $this
     */
    public function setExecTime(int $execTime) : self
    {
        $this->execTime = $execTime;
        
        return $this;
    }
    
    
    /**
     * 获取执行内容
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
    
    
    /**
     * 获取执行时间
     * @return int
     */
    public function getExecTime() : int
    {
        return $this->execTime;
    }
}