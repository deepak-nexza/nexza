<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'privacy_mode' => [''=>'Select Privacy',
        '1'=>'Public',
        '2'=>'Private'],
    'uploadDir'=>'Eventupload',
    'url_encrypted'=>env('URL_ENCRYPTED',false),
    'nexzoa_per'=>9,
    'nexzoa_Gateway_fee'=>2.9,
    'FROM_EMAIL'=>'nexzoa@gmail.com',
    'ADMIN_ID'=>'36',
    'EVENT_STATUS'=>['1'=>'Upcoming status','2'=>'Closed status'],
    'DATA_LIMITER'=>10,
    'TAX_RATE'=>18,
    'STATUS'=>['Progress'=>1,'description'=>2,'Ticket'=>3,'Submitted'=>4,],
    'ACTIVE'=>1,

   
];
