<?php
/**
 * Created by Anton Shedlovsky <alfaluck@gmail.com>.
 *
 * Date: 09.08.2016
 * Time: 22:39
 */

namespace Emf\MQ;

defined('BASE_PATH') || exit('No direct script access allowed');

interface JobInterface
{
    public function __construct(Dispatcher $dispatcher);

    public function run();
    public function close_connections();
}