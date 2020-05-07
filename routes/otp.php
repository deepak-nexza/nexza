<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/**
 * Otp Route
 */
Route::group( [ 'domain' => config('event.event_frontend_url') ], function() {
    Route::get('userotp', [
        'as' => 'user_otp',
        'uses' => '\\Nexza\\Otp\\Http\\Controllers\\OtpController@getUserOtp'
    ]);
    Route::post(
        'otp-validate',
        [
        'as' => 'otp_validate',
        'uses' => '\\Nexza\\Otp\\Http\\Controllers\\OtpController@getOtpValidate'
        ]
    );
    Route::post(
        'resend-otp',
        [
        'as' => 'resend_otp',
        'uses' => '\\Nexza\\Otp\\Http\\Controllers\\OtpController@resendOtp'
        ]
    );
//
//    Route::post(
//        'resend-otp',
//        [
//        'as' => 'resend_otp',
//        'uses' => '\\Nexza\\Otp\\Http\\Controllers\\OtpController@resendOtpCode'
//        ]
//    );
});