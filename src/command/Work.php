<?php

namespace BusyPHP\queue\command;

use BusyPHP\queue\InteractsWithFailed;
use BusyPHP\queue\WithQueueConfig;
use Exception;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use BusyPHP\queue\event\JobFailed;
use BusyPHP\queue\event\JobProcessed;
use BusyPHP\queue\event\JobProcessing;
use BusyPHP\queue\Job;
use BusyPHP\queue\Worker;

/**
 * 执行单个Work命令
 * @author busy^life <busy.life@qq.com>
 * @author  yunwuxin <448901948@qq.com>
 * @copyright (c) 2015--2021 ShanXi Han Tuo Technology Co.,Ltd. All rights reserved.
 * @version $Id: 2021/12/19 下午2:52 Work.php $
 */
class Work extends Command
{
    use InteractsWithFailed;
    use WithQueueConfig;
    
    /**
     * The queue worker instance.
     * @var Worker
     */
    protected $worker;
    
    
    public function __construct(Worker $worker)
    {
        parent::__construct();
        $this->worker = $worker;
    }
    
    
    protected function configure()
    {
        $this->setName('queue:work')
            ->addArgument('connection', Argument::OPTIONAL, 'The name of the queue connection to work', null)
            ->addOption('queue', null, Option::VALUE_OPTIONAL, 'The queue to listen on')
            ->addOption('once', null, Option::VALUE_NONE, 'Only process the next job on the queue')
            ->addOption('delay', null, Option::VALUE_OPTIONAL, 'Amount of time to delay failed jobs', 0)
            ->addOption('force', null, Option::VALUE_NONE, 'Force the worker to run even in maintenance mode')
            ->addOption('memory', null, Option::VALUE_OPTIONAL, 'The memory limit in megabytes', 128)
            ->addOption('timeout', null, Option::VALUE_OPTIONAL, 'The number of seconds a child process can run', 60)
            ->addOption('sleep', null, Option::VALUE_OPTIONAL, 'Number of seconds to sleep when no job is available', 3)
            ->addOption('tries', null, Option::VALUE_OPTIONAL, 'Number of times to attempt a job before logging it failed', 0)
            ->setDescription('Process the next job on a queue');
    }
    
    
    /**
     * Execute the console command.
     * @param Input  $input
     * @param Output $output
     * @return int|null|void
     * @throws Exception
     */
    public function execute(Input $input, Output $output)
    {
        $connection = $input->getArgument('connection') ?: $this->getQueueConfig('default');
        
        $queue = $input->getOption('queue') ?: $this->getQueueConfig("connections.{$connection}.queue", 'default');
        $delay = $input->getOption('delay');
        $sleep = $input->getOption('sleep');
        $tries = $input->getOption('tries');
        
        $this->listenForEvents();
        
        if ($input->getOption('once')) {
            $this->worker->runNextJob($connection, $queue, $delay, $sleep, $tries);
        } else {
            $memory  = $input->getOption('memory');
            $timeout = $input->getOption('timeout');
            $this->worker->daemon($connection, $queue, $delay, $sleep, $tries, $memory, $timeout);
        }
    }
    
    
    /**
     * 注册事件
     */
    protected function listenForEvents()
    {
        $this->app->event->listen(JobProcessing::class, function(JobProcessing $event) {
            $this->writeOutput($event->job, 'starting');
        });
        
        $this->app->event->listen(JobProcessed::class, function(JobProcessed $event) {
            $this->writeOutput($event->job, 'success');
        });
        
        $this->app->event->listen(JobFailed::class, function(JobFailed $event) {
            $this->writeOutput($event->job, 'failed');
            
            $this->logFailedJob($event);
        });
    }
    
    
    /**
     * Write the status output for the queue worker.
     *
     * @param Job $job
     * @param     $status
     */
    protected function writeOutput(Job $job, $status)
    {
        switch ($status) {
            case 'starting':
                $this->writeStatus($job, 'Processing', 'comment');
            break;
            case 'success':
                $this->writeStatus($job, 'Processed', 'info');
            break;
            case 'failed':
                $this->writeStatus($job, 'Failed', 'error');
            break;
        }
    }
    
    
    /**
     * Format the status output for the queue worker.
     *
     * @param Job    $job
     * @param string $status
     * @param string $type
     * @return void
     */
    protected function writeStatus(Job $job, $status, $type)
    {
        $this->output->writeln(sprintf("<{$type}>[%s][%s] %s</{$type}> %s", date('Y-m-d H:i:s'), $job->getJobId(), str_pad("{$status}:", 11), $job->getName()));
    }
    
    
    /**
     * 记录失败任务
     * @param JobFailed $event
     */
    protected function logFailedJob(JobFailed $event)
    {
        $this->getQueueFailed()
            ->log($event->connection, $event->job->getQueue(), $event->job->getRawBody(), $event->exception);
    }
}
