<?php

/**
 * Otp configuration file
 */
return [
    'expire_in' => 5, // In minutes
  
    //OTP link activation in minutes
    'active_in' => 5,

    //Max limit to send OTP
    'max_limit' => 5,

    //Max limit to submit otp
    'submit_max_limit' => 5,
];
