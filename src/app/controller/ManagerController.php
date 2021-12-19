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
        'jobs' => "CREATE TABLE `#__table_prefix__#system_jobs` (
  `id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `queue` VARCHAR(255) NOT NULL COMMENT '队列名称',
  `payload` LONGTEXT NOT NULL COMMENT '任务数据',
  `attempts` INT(11) UNSIGNED NOT NULL COMMENT '重试次数',
  `reserve_time` INT(11) UNSIGNED DEFAULT NULL COMMENT '保留时间',
  `available_time` INT(11) UNSIGNED NOT NULL COMMENT '延迟执行时间',
  `create_time` INT(11) UNSIGNED NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `queue` (`queue`),
  KEY `reserve_time` (`reserve_time`),
  KEY `available_time` (`available_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='任务列队表'",
        
        'failed' => "CREATE TABLE `#__table_prefix__#system_jobs_failed` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `connection` VARCHAR(255) NOT NULL COMMENT '连接器名称',
  `queue` VARCHAR(255) NOT NULL COMMENT '队列名称',
  `payload` LONGTEXT NOT NULL COMMENT '任务数据',
  `exception` LONGTEXT NOT NULL COMMENT '异常信息',
  `fail_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '失败时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='任务队列失败表'"
    ];
    
    /**
     * 删除表SQL
     * @var string[]
     */
    private $deleteTableSql = [
        "DROP TABLE IF EXISTS `#__table_prefix__#system_jobs`",
        "DROP TABLE IF EXISTS `#__table_prefix__#system_jobs_failed`",
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