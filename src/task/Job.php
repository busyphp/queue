<?php

namespace BusyPHP\queue\task;

/**
 * 任务处理参数
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/8 下午1:59 Job.php $
 */
class Job
{
    /**
     * 累计重试次数
     * @var int
     */
    private $retry = 0;
    
    /**
     * 延迟执行秒数
     * @var int
     */
    private $delay = 0;
    
    /**
     * 是否删除
     * @var bool
     */
    private $destroy = false;
    
    /**
     * @var string
     */
    private $handler;
    
    /**
     * 队列数据
     * @var mixed
     */
    private $data;
    
    
    /**
     * Job constructor.
     * @param string $handler 处理器
     * @param mixed  $data 处理数据
     */
    public function __construct(string $handler, $data)
    {
        $this->handler = $handler;
        $this->data    = $data;
    }
    
    
    /**
     * 设为销毁
     * @return $this
     */
    public function setDestroy() : self
    {
        $this->destroy = true;
        
        return $this;
    }
    
    
    /**
     * 是否销毁
     * @return bool
     */
    public function isDestroy() : bool
    {
        return $this->destroy;
    }
    
    
    /**
     * 设置延迟执行秒数
     * @param int $second
     * @return $this
     */
    public function setDelay(int $second) : self
    {
        $this->delay = $second;
        
        return $this;
    }
    
    
    /**
     * 获取延迟执行秒数
     * @return int
     */
    public function getDelay() : int
    {
        return $this->delay;
    }
    
    
    /**
     * 获取处理的数据
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
    
    
    /**
     * 获取处理器
     * @return string
     */
    public function getHandler() : string
    {
        return $this->handler;
    }
    
    
    /**
     * 获取错误次数
     * @return int
     */
    public function getRetry() : int
    {
        return $this->retry;
    }
    
    
    /**
     * 设置重试次数
     * @param int $step
     */
    protected function setRetry(int $step = 1) : void
    {
        $this->retry += $step;
    }
}