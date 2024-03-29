<?php

namespace BusyPHP\queue\command;

use BusyPHP\queue\WithQueueConfig;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use BusyPHP\queue\Listener;

/**
 * 监听任务命令
 * @author busy^life <busy.life@qq.com>
 * @author yunwuxin <448901948@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午2:51 Listen.php $
 */
class Listen extends Command
{
    use WithQueueConfig;
    
    /** @var  Listener */
    protected $listener;
    
    
    public function __construct(Listener $listener)
    {
        parent::__construct();
        $this->listener = $listener;
        $this->listener->setOutputHandler(function($type, $line) {
            $this->output->write($line);
        });
    }
    
    
    protected function configure()
    {
        $this->setName('queue:listen')
            ->addArgument('connection', Argument::OPTIONAL, 'The name of the queue connection to work', null)
            ->addOption('queue', null, Option::VALUE_OPTIONAL, 'The queue to listen on', null)
            ->addOption('delay', null, Option::VALUE_OPTIONAL, 'Amount of time to delay failed jobs', 0)
            ->addOption('memory', null, Option::VALUE_OPTIONAL, 'The memory limit in megabytes', 128)
            ->addOption('timeout', null, Option::VALUE_OPTIONAL, 'Seconds a job may run before timing out', 60)
            ->addOption('sleep', null, Option::VALUE_OPTIONAL, 'Seconds to wait before checking queue for jobs', 3)
            ->addOption('tries', null, Option::VALUE_OPTIONAL, 'Number of times to attempt a job before logging it failed', 0)
            ->setDescription('Listen to a given queue');
    }
    
    
    public function execute(Input $input, Output $output)
    {
        $connection = $input->getArgument('connection') ?: $this->getQueueConfig('default');
        
        $queue   = $input->getOption('queue') ?: $this->getQueueConfig("connections.{$connection}.queue", 'default');
        $delay   = $input->getOption('delay');
        $memory  = $input->getOption('memory');
        $timeout = $input->getOption('timeout');
        $sleep   = $input->getOption('sleep');
        $tries   = $input->getOption('tries');
        
        $this->listener->listen($connection, $queue, $delay, $sleep, $tries, $memory, $timeout);
    }
}
