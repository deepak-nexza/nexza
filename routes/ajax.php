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
        Route::post('/getstatelist',['as'=> 'statelist','uses'=>'Event\AjaxController@stateList']);
        Route::post('/stateindividual',['as'=> 'stateindividual','uses'=>'Event\AjaxController@stateDetails']);
        Route::post('/get-event-ticket',['as'=> 'get_event_ticket','uses'=>'Event\AjaxController@getTicketList']);
});
});
    

