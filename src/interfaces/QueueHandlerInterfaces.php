<?php

namespace BusyPHP\queue\interfaces;

/**
 * 列队处理接口
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2019 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2020/11/12 下午9:59 下午 QueueHandlerInterfaces.php $
 */
interface QueueHandlerInterfaces
{
    /**
     * 执行列队
     * 警告：该方法不要抛出异常，请自行处理异常
     * 如需打印错误日志请使用{@see QueueHandlerInterfaces::getErrors()} 返回
     * @param mixed $data 要处理的数据
     * @return bool 返回true执行完毕，返回$data则将该$data数据重新插入列队
     */
    public function handle($data);
    
    
    /**
     * 获取执行错误信息
     * 日志中会以英文逗号组合该信息
     * @return array
     */
    public function getErrors() : array;
}