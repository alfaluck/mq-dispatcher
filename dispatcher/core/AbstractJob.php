<?php
/**
 * Created by Anton Shedlovsky <alfaluck@gmail.com>.
 *
 * Date: 09.08.2016
 * Time: 23:03
 */

namespace Emf\MQ;

defined('BASE_PATH') || exit('No direct script access allowed');

abstract class AbstractJob implements JobInterface
{
    protected $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }
}