<?php
/**
 * Created by Anton Shedlovsky <alfaluck@gmail.com>.
 *
 * Date: 06.08.2016
 * Time: 21:48
 */

define('BASE_PATH', __DIR__);

require BASE_PATH . '/vendor/autoload.php';

// Running dispatcher
Emf\MQ\Dispatcher::run(new Emf\MQ\Config());

