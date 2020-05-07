<?php
/*
  |--------------------------------------------------------------------------
  | Sage Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for Esign.
  | It's a breeze. Simply tell Laravel the URIs it should respond to
  | and give it the controller to call when that URI is requested.
  |
 */
/**
 * Otp Route
 */
Route::group(['domain' => config('b2cin.frontend_uri')], function () {
        Route::group(['middleware' => ['auth','acl']], function () {
            Route::get('userotp', [
                'as' => 'user_otp',
                'uses' => '\\Nexza\\Otp\\Http\\Controllers\\Otp@getUserOtp'
            ]);
        });
});
