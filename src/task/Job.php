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
     * 是否重新发布
     * @var bool
     */
    private $release = false;
    
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
    public function destroy() : self
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
    public function data()
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
    public function retry() : int
    {
        return $this->retry;
    }
    
    
    /**
     * 是否需要重新发布
     * @return bool
     */
    public function isRelease() : bool
    {
        return $this->release;
    }
    
    
    /**
     * 设置重新发布到队列
     * - 警告: 这行代码以后不要抛出异常
     * @param int $delay
     * @return $this
     */
    public function release(int $delay = 0) : self
    {
        $this->delay   = $delay;
        $this->release = true;
        $this->retry++;
        
        return $this;
    }
}