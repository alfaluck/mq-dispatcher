<?php
/**
 * Created by Anton Shedlovsky <alfaluck@gmail.com>.
 *
 * Date: 06.08.2016
 * Time: 22:51
 */

namespace Emf\MQ;

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
            $status = $dispatcher
                ->init()
                ->start();
        } catch (\Throwable $throwable) {
            // handle errors and exceptions
            echo date('Y-m-d h:i:s') . " (UTC) >\tThrowable caught" . PHP_EOL;
            $status = 1;
        }

        $dispatcher->print_results();
        echo date('Y-m-d h:i:s') . " (UTC) >\tDispatcher stopped successfully" . PHP_EOL;
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
            foreach ($this->jobs as $worker) {
                $worker->start();
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
}