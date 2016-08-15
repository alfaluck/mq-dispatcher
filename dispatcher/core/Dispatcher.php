<?php
/**
 * Created by Anton Shedlovsky <alfaluck@gmail.com>.
 *
 * Date: 06.08.2016
 * Time: 22:51
 */

namespace Emf\MQ;

use GuzzleHttp\Promise\Tests\Thing1;

defined('BASE_PATH') || exit('No direct script access allowed');

/**
 * Class Dispatcher
 * @package Emf\MQ
 */
class Dispatcher
{
    /**
     * Runs dispatcher
     * @param Config $config
     */
    static public function run(Config $config)
    {
        try {
            date_default_timezone_set('UTC');

            $dispatcher = new self($config);
            // Running infinitive loop until Jobs Queue becomes empty
            // or Dispatcher receives SIGTERM (triggered by `docker stop`)
            $status = $dispatcher
                ->init()
                ->start();
            $dispatcher->print_results();
            echo date('Y-m-d h:i:s') . " (UTC) >\tDispatcher stopped successfully" . PHP_EOL;
        } catch (\Throwable $throwable) {
            // handle errors and exceptions
            echo date('Y-m-d h:i:s') . " (UTC) >\tThrowable caught out of jobs loop" . PHP_EOL;
            $status = 1;
        }

        exit($status);
    }


    /**
     * Flag to stop dispatcher
     * @var bool Flag to stop dispatcher
     */
    public $stop_dispatcher = false;

    /**
     * Stores dispatcher configuration parameters
     * @var Config Dispatcher configuration
     */
    private $config;

    /**
     * Stores jobs which are active
     * @var [JobInterface] Array of all active jobs
     */
    private $jobs = [];

    /**
     * Stores result of all triggered jobs
     * @var array
     */
    private $jobs_results = [];


    /**
     * Dispatcher constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Initializes dispatcher
     * @return $this
     */
    public function init()
    {
        pcntl_signal(SIGTERM, [$this, 'handle_signal']);
        $this->fetch_jobs();
        return $this;
    }

    /**
     * Starts active jobs one by one
     * @return int
     */
    public function start()
    {
        echo date('Y-m-d h:i:s') . " (UTC) >\tDispatcher started" . PHP_EOL;
        while (!$this->stop_dispatcher) {
            if (empty($this->jobs)) {
                echo date('Y-m-d h:i:s') . " (UTC) >\tJobs queue is empty. Going to stop dispatcher" . PHP_EOL;
                $this->stop_dispatcher = true;
            }
            foreach ($this->jobs as $name => $job) {
                try {
                    $job->start();
                } catch (\Throwable $throwable) {
                    $this->handle_job_exception($name, $throwable);
                }
                if ($this->stop_dispatcher) break;
            }
            pcntl_signal_dispatch();
            $this->stop_dispatcher or sleep(1);
        }
        return 0;
    }

    /**
     * Register sysctl Signals handler
     */
    public function handle_signal()
    {
        echo date('Y-m-d h:i:s') . " (UTC) >\tSIGTERM caught" . PHP_EOL;
        $this->stop_dispatcher = true;
    }

    /**
     * Returns dispatcher configuration
     * @return Config
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Increases successful results for certain job
     * @param string $job_name
     * @return $this
     */
    public function inc_job_results(string $job_name)
    {
        !isset($this->jobs_results[$job_name]) and $this->jobs_results[$job_name] = 0;
        ++$this->jobs_results[$job_name];
        return $this;
    }

    /**
     * Echoes results of all triggered jobs
     */
    public function print_results()
    {
        if (!empty($this->jobs_results)) {
            echo PHP_EOL . date('Y-m-d h:i:s') . " (UTC) >\tJob results:" . PHP_EOL;
            foreach ($this->jobs_results as $name => $value) {
                echo date('Y-m-d h:i:s') . " (UTC) >\t{$name}: {$value}" . PHP_EOL;
            }
            echo PHP_EOL;
        }
    }

    /**
     * Dispatcher destructor
     */
    public function __destruct()
    {
        foreach ($this->jobs as $worker) {
            $worker->close_connections();
        }
    }


    /**
     * Fetches jobs data from jobs_config.inc and creates objects for each active job
     * @return $this
     * @throws \UnexpectedValueException
     */
    private function fetch_jobs()
    {
        $jobs_config = require BASE_PATH . '/jobs_config.inc';
        foreach ($jobs_config as $name => $data) {
            if ($data['active']) {
                $class_name = 'Emf\\MQ\\Jobs\\' . $data['class'];
                $this->jobs[$name] = new $class_name($this);
                if (!($this->jobs[$name] instanceof AbstractJob)) {
                    throw new \UnexpectedValueException("Wrong class '{$class_name}' for job {$name}'");
                }
            }
        }

        return $this;
    }

    /**
     * Handles exceptions that had benn thrown by jobs
     * @param string $job_id
     * @param \Throwable $throwable
     */
    private function handle_job_exception(string $job_id, \Throwable $throwable)
    {
        // Handle job exception

        // Log Exception
        echo date('Y-m-d h:i:s') . " (UTC) >\tJob '{$job_id}' had thrown an Exception:" . PHP_EOL;
        $this->log_exception($throwable);

        // How we will react on exceptions thrown by job?

        // Just remove this job from queue
        /*
        if (isset($this->jobs[$job_id])) unset($this->jobs[$job_id]);
        echo date('Y-m-d h:i:s') . " (UTC) >\tJob '{$job_id}' had been removed from the jobs queue" . PHP_EOL;
        */

        // Or it can be implemented a counter and after 10 times certain job had thrown an exception
        // then remove this job
        /*
        static $counter = [];
        $counter[$job_id] = $counter[$job_id] ?? 0;
        if (++$counter[$job_id] >= 10) {
            unset($this->jobs[$job_id]);
            $counter[$job_id] = 0;
            echo date('Y-m-d h:i:s') . " (UTC) >\tJob '{$job_id}' had been removed from the jobs queue" . PHP_EOL;
        }
        */
    }

    /**
     * Echoes exception data including nested previous exception
     * @param \Throwable $throwable
     */
    private function log_exception(\Throwable $throwable)
    {
        static $indent = '';

        $indent .= "\t";
        echo $indent . get_class($throwable) . " (#{$throwable->getCode()}): {$throwable->getMessage()}" . PHP_EOL;
        echo $indent . "In {$throwable->getFile()} at line #{$throwable->getLine()}" . PHP_EOL;
        echo $indent . "Stack trace:" . PHP_EOL;
        foreach ($throwable->getTrace() as $number => $line) {
            echo $indent . "\t#{$number} {$line['file']}({$line['line']}): {$line['function']}(" . implode(', ', $line['args']) . ")" . PHP_EOL;
        }
        if (!is_null($throwable->getPrevious())) {
            echo $indent . "Previous Throwable:" . PHP_EOL;
            $this->log_exception($throwable->getPrevious());
        }
        $indent = '';
    }
}