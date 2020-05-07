<?php

Route::group(['domain' => config('b2cin.backend_uri')], function () {
        Route::group(['prefix' => 'parameter'], function () {
        });
});
