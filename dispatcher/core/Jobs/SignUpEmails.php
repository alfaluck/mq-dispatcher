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

defined('BASE_PATH') || exit('No direct script access allowed');

/**
 * Class SignUpEmails
 * @package Emf\MQ
 */
class SignUpEmails extends AbstractJob implements JobInterface
{
    /**
     * Limit of handled emails after it will be reached job must be interrupted
     * @var int
     */
    private $max_handled_emails = 10;

    /**
     * Handled jobs counter
     * @var int
     */
    private $emails_handled = 0;

    /**
     * Opened connections storage
     * @var array
     */
    private $connections = [];


    /**
     * @inheritdoc
     */
    public function run()
    {
        if ($email_id = $this->get_email_id()) {
            $email_data = $this->get_email_data($email_id);
            $result = $this->send_email($email_data);
            if ($result) {
                $this->register_result($email_id);
            } else {
                $this->make_deferred($email_id);
            }
            ++$this->emails_handled >= $this->max_handled_emails and $result = null;
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function close_connections()
    {
        $this->close_redis();
        $this->close_mysql();
        return $this;
    }


    /**
     * @inheritdoc
     */
    protected function interrupt() {
        $this->interrupt_job = true;
        $this->emails_handled = 0;
    }


    /**
     * Creates if needed and returns opened REDIS connection
     * @return \Redis
     */
    private function get_redis() : \Redis
    {
        if (!isset($this->connections['redis'])) {
            // create Redis connection
        }
        return $this->connections['redis'];
    }

    /**
     * Closes opened REDIS connection
     */
    private function close_redis(){
        if (isset($this->connections['redis'])) {
            $this->connections['redis']->close();
        }
    }

    /**
     * Creates if needed and returns opened MySQL connection
     * @return \mysqli
     */
    private function get_mysql() : \mysqli
    {
        if (!isset($this->connections['mysql'])) {
            // create MySQL connection
        }
        return $this->connections['mysql'];
    }

    /**
     * Closes opened MySQL connection
     */
    private function close_mysql(){
        if (isset($this->connections['mysql'])) {
            $this->connections['mysql']->close();
        }
    }

    /**
     * Fetches top stored email id to send from the REDIS common and deferred queues.
     * Returns email_id or 0 if queue is empty
     * @return int
     */
    private function get_email_id() : int
    {
        // get email_id from REDIS and lock this record
        // while email will be sent and register
        // if there is some email ids in deferred queue
        // then this emails will be handled in first order
        return 0;
    }

    /**
     * Gets email data from MySQL DB and prepares to handle it by send_email method
     * @param int $email_id
     * @return array
     */
    private function get_email_data(int $email_id) : array
    {
        // get email_data from MySQL
        return [];
    }

    /**
     * Sends email
     * @param array $email_data
     * @return bool
     */
    private function send_email(array $email_data) : bool
    {
        // send email via provider
        return true;
    }

    /**
     * Moves current email_id to deferred queue
     * @param int $email_id
     * @return $this
     */
    private function make_deferred(int $email_id)
    {
        return $this;
    }

    /**
     * Registers successfully sent email
     * @param int $email_id
     * @return $this
     */
    private function register_result(int $email_id)
    {
        // register result in DB (MySQL) and erase in REDIS
        $this->dispatcher->inc_job_results('SignUpEmails');
        return $this;
    }
}