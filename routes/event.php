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
Route::group( 
    [ 'domain' => config('event.event_frontend_url') ], function() {
        Route::group( 
            [ 'middleware' => ['auth']], function() { 
        Route::get('/myaccount',['as'=> 'myaccount','uses'=>'Event\Frontend\EventController@myaccount']);
        Route::get('/create-event',['as'=> 'create_event','uses'=>'Event\Frontend\EventController@createEvent']);
});
});
    

