<?php
declare(strict_types = 1);

namespace BusyPHP\queue\app\controller;

use BusyPHP\app\admin\controller\develop\plugin\SystemPluginBaseController;
use BusyPHP\app\admin\model\system\plugin\SystemPlugin;
use Exception;
use RuntimeException;
use think\Response;
use Throwable;

/**
 * 插件管理
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/11/4 下午2:11 ManagerController.php $
 */
class ManagerController extends SystemPluginBaseController
{
    protected string $jobsSql       = <<<SQL
CREATE TABLE `#__table_prefix__#plugin_queue_jobs` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='任务列队表'
SQL;
    
    protected string $jobsFailedSql = <<<SQL
CREATE TABLE `#__table_prefix__#plugin_queue_jobs_failed` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `connection` VARCHAR(255) NOT NULL COMMENT '连接器名称',
  `queue` VARCHAR(255) NOT NULL COMMENT '队列名称',
  `payload` LONGTEXT NOT NULL COMMENT '任务数据',
  `exception` LONGTEXT NOT NULL COMMENT '异常信息',
  `fail_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '失败时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='任务队列失败表'
SQL;
    
    
    /**
     * 返回模板路径
     * @return string
     */
    protected function viewPath() : string
    {
        return '';
    }
    
    
    /**
     * 安装插件
     * @return Response
     * @throws Throwable
     */
    public function install() : Response
    {
        $model = SystemPlugin::init();
        $model->startTrans();
        try {
            if (!$this->hasTable('plugin_queue_jobs')) {
                $this->executeSQL($this->jobsSql);
            }
            if (!$this->hasTable('queue_jobs_failed')) {
                $this->executeSQL($this->jobsFailedSql);
            }
            
            $model->setInstall($this->info->package);
            
            $model->commit();
        } catch (Throwable $e) {
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
     */
    public function uninstall() : Response
    {
        throw new RuntimeException('不支持卸载');
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