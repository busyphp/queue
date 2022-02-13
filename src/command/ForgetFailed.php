<?php

namespace BusyPHP\queue\command;

use BusyPHP\queue\InteractsWithFailed;
use think\console\Command;
use think\console\input\Argument;

/**
 * 强制执行失败任务命令
 * @author busy^life <busy.life@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午2:50 ForgetFailed.php $
 */
class ForgetFailed extends Command
{
    use InteractsWithFailed;
    
    protected function configure()
    {
        $this->setName('bp:queue:forget')
            ->addArgument('id', Argument::REQUIRED, 'The ID of the failed job')
            ->setDescription('Delete a failed queue job');
    }
    
    
    public function handle()
    {
        if ($this->getQueueFailed()->forget($this->input->getArgument('id'))) {
            $this->output->info('Failed job deleted successfully!');
        } else {
            $this->output->error('No failed job matches the given ID.');
        }
    }
}
