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
    return view('eventfrontend.index');
});

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


Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
Route::get('/logut', 'Auth\LoginController@logout')->name('logout');

Route::post('/register', 'Auth\RegisterController@register')->name('register');


Route::get('/home', 'HomeController@index')->name('home');

