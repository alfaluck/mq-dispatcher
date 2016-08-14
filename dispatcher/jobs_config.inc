<?php
/**
 * Created by Anton Shedlovsky <alfaluck@gmail.com>.
 *
 * Date: 09.08.2016
 * Time: 22:33
 */
defined('BASE_PATH') || exit('No direct script access allowed');

// Configuring jobs parameters
return [
    'signup_emails' => [            // job ID
        'class' => 'SignUpEmails',  // class is containing job logic,
                                    // must be extended from Emf\MQ\AbstractJob and implemented Emf\MQ\JobInterface
                                    // and located at core/Jobs path
        'active' => true,           // activity state of job, can be true or false
    ],
];
