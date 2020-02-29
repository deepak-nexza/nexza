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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/dashboard', [
        'uses' => 'Frontend\UserController@index',
        'as'   => 'dashboard'
        ]);

    Route::get('/appointments', [
        'uses' => 'Frontend\UserController@appointments',
        'as'   => 'appointment'
        ]);

Route::get('/registeration', [
        'uses' => 'Frontend\UserController@userregister',
        'as'   => 'registeration'
        ]);

Route::get('/DoctorPanel', [
        'uses' => 'Frontend\UserController@doctorpanel',
        'as'   => 'doctor-panel'
        ]);
