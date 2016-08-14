<?php
/**
 * Created by Anton Shedlovsky <alfaluck@gmail.com>.
 *
 * Date: 07.08.2016
 * Time: 0:24
 */

namespace Emf\MQ;

defined('BASE_PATH') || exit('No direct script access allowed');

class Config
{
    private $redis_host;
    private $redis_port;
    private $redis_connection_timeout;
    private $redis_key_prefix;
    private $redis_auth;
    private $redis_database;
    private $redis_expire;

    private $mysql_host;
    private $mysql_username;
    private $mysql_password;
    private $mysql_db_name;
    private $mysql_port;

    public function __construct()
    {
        $this->redis_host = getenv('redis_host');
        $this->redis_port = intval(getenv('redis_port'));
        $this->redis_connection_timeout = floatval(getenv('redis_connection_timeout'));
        $this->redis_key_prefix = getenv('redis_key_prefix');
        $this->redis_auth = getenv('redis_auth');
        $this->redis_auth = $this->redis_auth == 'null' ? '' : $this->redis_auth;
        $this->redis_database = intval(getenv('redis_database'));
        $this->redis_expire = intval(getenv('redis_expire'));

        $this->mysql_host = getenv('mysql_host');
        $this->mysql_username = getenv('mysql_username');
        $this->mysql_password = getenv('mysql_password');
        $this->mysql_password = $this->mysql_password == 'null' ? '' : $this->mysql_password;
        $this->mysql_db_name = getenv('mysql_db_name');
        $this->mysql_port = intval(getenv('mysql_port'));
    }

    public function redis_params()
    {
        return [];
    }

    public function mysql_params()
    {
        return [];
    }
}