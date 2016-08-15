# mq-dispatcher
Message Queue Dispatcher based on Docker Containers

## Features

- runs from Docker container
- handles `docker stop` command
- runs unlimited number of Jobs
- uses REDIS as queue-server and MySQL as main data-server
- uses PHPMailer as SMTP client
- uses **Deferred-Queue** for those emails which were been failed
- uses **ENV docker variables** to configure Dispatcher parameters

## Running

### With default parameters
1) First run:

`docker run -d --name your_container_name alfaluck/mq-dispatcher:latest`

2) Stop container:

`docker stop your_container_name`

3) Next run:

`docker start your_container_name`

4) See container logs:

`docker logs your_container_name`

### With parameters saved in file:

`docker run -d --name your_container_name --env-file env_file_name.ext alfaluck/mq-dispatcher:latest`

The `env_file_name.ext` must contain, for example:

```

redis_host=localhost
redis_port=6379
redis_connection_timeout=3.5
redis_key_prefix=main_queue:
redis_auth=null
redis_database=0
redis_expire=600

mysql_host=localhost
mysql_username=root
mysql_password=null
mysql_db_name=test
mysql_port=3306

mailer_host=smtp.sparkpostmail.com
mailer_port=587
mailer_secure=tls
mailer_auth=true
mailer_username=SMTP_Injection
mailer_password=test_api_key

```

Work in progress...