<?php
/**
 * Created by Anton Shedlovsky <alfaluck@gmail.com>.
 *
 * Date: 07.08.2016
 * Time: 0:24
 */

namespace Emf\MQ;

defined('BASE_PATH') || exit('No direct script access allowed');

/**
 * Class Config
 * @package Emf\MQ
 */
class Config
{
    /**
     * REDIS hostname
     * @var string
     */
    private $redis_host;

    /**
     * REDIS port
     * @var int
     */
    private $redis_port;

    /**
     * REDIS connection timeout
     * @var float
     */
    private $redis_connection_timeout;

    /**
     * REDIS key prefix
     * @var string
     */
    private $redis_key_prefix;

    /**
     * REDIS password
     * @var string
     */
    private $redis_auth;

    /**
     * REDIS database number
     * @var int
     */
    private $redis_database;

    /**
     * REDIS default expiration time
     * @var int
     */
    private $redis_expire;

    /**
     * MySQL hostname
     * @var string
     */
    private $mysql_host;

    /**
     * MySQL username
     * @var string
     */
    private $mysql_username;

    /**
     * MySQL password
     * @var string
     */
    private $mysql_password;

    /**
     * MySQL database
     * @var string
     */
    private $mysql_db_name;

    /**
     * MySQL port
     * @var int
     */
    private $mysql_port;

    /**
     * Config constructor. Gets parameters from environment variables
     */
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

    /**
     * Returns REDIS parameters
     * @return array
     */
    public function get_redis_params() : array
    {
        $params = [
            'host' => $this->redis_host,
            'port' => $this->redis_port,
            'timeout' => $this->redis_connection_timeout,
            'prefix' => $this->redis_key_prefix,
            'auth' => $this->redis_auth,
            'database' => $this->redis_database,
            'expire' => $this->redis_expire
        ];
        return $params;
    }

    /**
     * Returns MySQL parameters
     * @return array
     */
    public function get_mysql_params() : array
    {
        $params = [
            'host' => $this->mysql_host,
            'username' => $this->mysql_username,
            'password' => $this->mysql_password,
            'db_name' => $this->mysql_db_name,
            'port' => $this->mysql_port
        ];
        return $params;
    }
}