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
        Route::get('/update-event',['as'=> 'update_event','uses'=>'Event\Frontend\EventController@createEvent']);
        Route::get('/upcoming-event',['as'=> 'upcoming_event','uses'=>'Event\Frontend\EventController@upcomingEvent']);
        Route::get('/past-event',['as'=> 'past_event','uses'=>'Event\Frontend\EventController@pastEvent']);
        Route::get('/event-ticket',['as'=> 'event_ticket','uses'=>'Event\Frontend\EventController@eventTicket']);
        Route::post('/save-event',['as'=> 'save_event','uses'=>'Event\Frontend\EventController@saveEvent']);
        Route::post('/update-event',['as'=> 'update_event','uses'=>'Event\Frontend\EventController@updateEvent']);
});
});
    

