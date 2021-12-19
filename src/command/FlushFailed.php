<?php

namespace BusyPHP\queue\command;

use BusyPHP\queue\InteractsWithFailed;
use think\console\Command;

class FlushFailed extends Command
{
    use InteractsWithFailed;
    
    protected function configure()
    {
        $this->setName('queue:flush')->setDescription('Flush all of the failed queue jobs');
    }
    
    
    public function handle()
    {
        $this->getQueueFailed()->flush();
        
        $this->output->info('All failed jobs deleted successfully!');
    }
}
