<?php

namespace BusyPHP\queue\event;

use BusyPHP\queue\Job;

class JobFailed
{
    /** @var string */
    public $connection;
    
    /** @var Job */
    public $job;
    
    /** @var \Exception */
    public $exception;
    
    
    public function __construct($connection, $job, $exception)
    {
        $this->connection = $connection;
        $this->job        = $job;
        $this->exception  = $exception;
    }
}
