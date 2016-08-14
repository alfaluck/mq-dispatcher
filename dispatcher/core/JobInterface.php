<?php
/**
 * Created by Anton Shedlovsky <alfaluck@gmail.com>.
 *
 * Date: 09.08.2016
 * Time: 22:39
 */

namespace Emf\MQ;

defined('BASE_PATH') || exit('No direct script access allowed');

/**
 * Interface JobInterface
 * @package Emf\MQ
 */
interface JobInterface
{
    /**
     * Job constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher);

    /**
     * Starts job execution
     * @return void
     */
    public function start();

    /**
     * Execution unit of job
     * @return bool|null
     */
    public function run();

    /**
     * Closes all opened connections
     * @return $this
     */
    public function close_connections();
}