<?php
/**
 * Created by Anton Shedlovsky <alfaluck@gmail.com>.
 *
 * Date: 09.08.2016
 * Time: 22:40
 */

namespace Emf\MQ\Jobs;

use Emf\MQ\JobInterface;
use Emf\MQ\AbstractJob;
use Emf\MQ\Dispatcher;

defined('BASE_PATH') || exit('No direct script access allowed');

class SignUpEmails extends AbstractJob implements JobInterface
{
    private $max_handled_emails = 10;
    private $connections = [];

    public function __construct(Dispatcher $dispatcher)
    {
        parent::__construct($dispatcher);
    }

    public function run()
    {
        $email_id = $this->get_email_id();
        $emails_sent = 0;
        while (!$this->dispatcher->stop_dispatcher && $email_id) {
            $email_data = $this->get_email_data($email_id);
            $result = $this->send_email($email_data);
            $this->register_result($result);
            pcntl_signal_dispatch();
            if ($this->max_handled_emails >= ++$emails_sent) break;
            $this->dispatcher->stop_dispatcher or $email_id = $this->get_email_id();
        }
    }

    public function close_connections()
    {
        $this->close_redis();
        $this->close_mysql();
    }


    private function get_redis() : \Redis
    {
        if (!isset($this->connections['redis'])) {
            // create Redis connection
        }
        return $this->connections['redis'];
    }

    private function close_redis(){
        if (isset($this->connections['redis'])) {
            $this->connections['redis']->close();
        }
    }

    private function get_mysql() : \mysqli
    {
        if (!isset($this->connections['mysql'])) {
            // create MySQL connection
        }
        return $this->connections['mysql'];
    }

    private function close_mysql(){
        if (isset($this->connections['mysql'])) {
            $this->connections['mysql']->close();
        }
    }

    private function get_email_id() : int
    {
        // get email_id from REDIS and lock this record
        // while email will be sent and register
        return 0;
    }

    private function get_email_data(int $email_id) : array
    {
        // get email_data from MySQL
        return [];
    }

    private function send_email(array $email_data) : bool
    {
        // send email via provider
        return true;
    }

    private function register_result(bool $result) : bool
    {
        // register result in DB (MySQL) and erase in REDIS
        $this->dispatcher->inc_job_results('SignUpEmails');
        return true;
    }
}