<?php
/**
 * Created by Anton Shedlovsky <alfaluck@gmail.com>.
 *
 * Date: 09.08.2016
 * Time: 23:03
 */

namespace Emf\MQ;

defined('BASE_PATH') || exit('No direct script access allowed');

/**
 * Class AbstractJob
 * @package Emf\MQ
 */
abstract class AbstractJob implements JobInterface
{
    /**
     * Stores reference to Dispatcher
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Flag to interrupt job
     * @var bool
     */
    protected $interrupt_job = false;


    /**
     * @inheritdoc
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @inheritdoc
     */
    final public function start()
    {
        $this->interrupt_job = false;
        while (!$this->dispatcher->stop_dispatcher && !$this->interrupt_job) {
            $result = $this->run();
            $this->need_to_interrupt($result);
            pcntl_signal_dispatch();
        }
    }


    /**
     * Checks does it necessary to interrupt job execution at current point
     * @param null|bool $result Result of just finished task
     * @return $this
     */
    protected function need_to_interrupt($result)
    {
        is_null($result) and $this->interrupt();
        return $this;
    }

    /**
     * Interrupts job execution
     * @return $this
     */
    protected function interrupt()
    {
        $this->interrupt_job = true;
        return $this;
    }
}