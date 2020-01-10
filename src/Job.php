<?php

declare(strict_types=1);

namespace Printing;

class Job
{
    protected $job;

    public function __construct($job)
    {
        $this->job = $job;
    }

    public function status()
    {
        return $this->isPending() ? 'printing' : 'completed';
    }

    public function isComplete()
    {
        return ! $this->isPending();
    }

    public function isPending()
    {
        // We check in the not completed
        // if it's not there we assume it was completed
        // since the completed state might get cleared
        // daily or throughout the day
        $output = shell_exec("lpstat -W not-completed");
    
        return !is_null($output) && stripos($output, $this->job) !== false;
    }

    /**
     * Get the value of job
     */ 
    public function getJob()
    {
        return $this->job;
    }

    /**
     * Set the value of job
     *
     * @return  self
     */ 
    public function setJob($job)
    {
        $this->job = $job;

        return $this;
    }
}
