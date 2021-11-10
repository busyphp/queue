<?php

namespace BusyPHP\queue\app\controller;

use BusyPHP\app\admin\model\system\plugin\SystemPlugin;
use BusyPHP\contract\abstracts\PluginManager;
use Exception;
use think\Response;

/**
 * 插件管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/4 下午2:11 ManagerController.php $
 */
class ManagerController extends PluginManager
{
    /**
     * 创建表SQL
     * @var string[]
     */
    private $createTableSql = [
        'queue' => "CREATE TABLE `#__table_prefix__#member_oauth` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` INT(11) NOT NULL DEFAULT '0' COMMENT '会员ID',
  `type` SMALLINT(2) NOT NULL DEFAULT '0' COMMENT '登录类型',
  `union_type` SMALLINT(2) NOT NULL DEFAULT '0' COMMENT '厂商类型',
  `openid` VARCHAR(60) NOT NULL DEFAULT '' COMMENT 'openid',
  `unionid` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '同登录类型唯一值',
  `create_time` INT(11) NOT NULL DEFAULT '0' COMMENT '绑定时间',
  `update_time` INT(11) NOT NULL DEFAULT '0' COMMENT '更新时间',
  `login_total` INT(11) NOT NULL DEFAULT '0' COMMENT '登录次数',
  `login_ip` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '本次登录IP',
  `last_ip` VARCHAR(45) NOT NULL DEFAULT '' COMMENT '上次登录IP',
  `login_time` INT(11) NOT NULL DEFAULT '0' COMMENT '本次登录时间',
  `last_time` INT(11) NOT NULL DEFAULT '0' COMMENT '上次登录时间',
  `nickname` VARCHAR(60) NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '头像',
  `sex` TINYINT(1) NOT NULL DEFAULT '0' COMMENT '性别',
  `user_info` TEXT NOT NULL COMMENT '登录数据',
   PRIMARY KEY (`id`),
   KEY `user_id` (`user_id`),
   KEY `type` (`type`),
   KEY `openid` (`openid`),
   KEY `unionid` (`unionid`),
   KEY `union_type` (`union_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='OAuth登录'",
    ];
    
    /**
     * 删除表SQL
     * @var string[]
     */
    private $deleteTableSql = [
        "DROP TABLE IF EXISTS `#__table_prefix__#system_queue`",
    ];
    
    
    /**
     * 返回模板路径
     * @return string
     */
    protected function viewPath() : string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR;
    }
    
    
    /**
     * 安装插件
     * @return Response
     * @throws Exception
     */
    public function install() : Response
    {
        $model = SystemPlugin::init();
        $model->startTrans();
        try {
            foreach ($this->deleteTableSql as $item) {
                $this->executeSQL($item);
            }
            
            foreach ($this->createTableSql as $item) {
                $this->executeSQL($item);
            }
            
            $model->setInstall($this->info->package);
            
            $model->commit();
        } catch (Exception $e) {
            $model->rollback();
            
            throw $e;
        }
        
        $this->updateCache();
        $this->logInstall();
        
        return $this->success('安装成功');
    }
    
    
    /**
     * 卸载插件
     * @return Response
     * @throws Exception
     */
    public function uninstall() : Response
    {
        $model = SystemPlugin::init();
        $model->startTrans();
        try {
            foreach ($this->deleteTableSql as $item) {
                $this->executeSQL($item);
            }
            
            $model->setUninstall($this->info->package);
            
            $model->commit();
        } catch (Exception $e) {
            $model->rollback();
            
            throw $e;
        }
        
        $this->updateCache();
        $this->logUninstall();
        
        return $this->success('卸载成功');
    }
    
    
    /**
     * 设置插件
     * @return Response
     * @return Exception
     */
    public function setting() : Response
    {
        return response();
    }
}