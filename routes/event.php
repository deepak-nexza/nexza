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
        Route::post('/update-event',['as'=> 'update_event','uses'=>'Event\Frontend\EventController@updateEvent']);
        Route::post('/save-event',['as'=> 'save_event','uses'=>'Event\Frontend\EventController@saveEvent']);
        Route::post('/save-event-ticket',['as'=> 'save_event_ticket','uses'=>'Event\Frontend\EventController@saveEventTicket']);
        Route::get('/update-event-ticket',['as'=> 'update_event_ticket','uses'=>'Event\Frontend\EventController@updateEventTicket']);
        Route::get('/list-event-ticket',['as'=> 'list_event_ticket','uses'=>'Event\Frontend\EventController@eventTicketlist']);
        Route::get('/change-password', ['as'=> 'password.confirm','uses'=>'GuestController@chagnePassword']);
        Route::post('/change-password', ['as'=> 'password.confirm','uses'=>'GuestController@savePassword']);
        Route::post('/update-profile', ['as'=> 'update_profile','uses'=>'Event\Frontend\EventController@updateProfile']);
        Route::get('/home', 'Event\Frontend\EventController@index')->name('home');
        Route::get('/delEve', 'Event\Frontend\EventController@eventSoftDel')->name('delete_event');
        Route::get('/ticket-close', 'Event\Frontend\EventController@closeTicket')->name('closeTicket');
        Route::post('/check-ticket', 'Event\Frontend\EventController@checkTicket')->name('check_ticket');
        Route::post('/add-event-category', 'Event\Frontend\EventController@saveEveCategory')->name('save_event_category');
        Route::get('/add-event-category', 'Event\Frontend\EventController@saveEveCategory')->name('save_event_category');
        Route::get('/event-category-list', 'Event\Frontend\EventController@eventCategory_list')->name('eventCategory_list');
        Route::get('/delete-event-category', 'Event\Frontend\EventController@deleteEventCategory')->name('del_eve_list');
        Route::get('/event-category', 'Event\Frontend\EventController@eventCategory')->name('eve_cat');
        Route::get('/event-description', 'Event\Frontend\EventController@eventdescription')->name('event_desc');
        Route::post('/save-description', 'Event\Frontend\EventController@saveDesc')->name('save_desc');
        Route::post('/submit-event', 'Event\Frontend\EventController@submitEvent')->name('submit_event');
       
});
});
    

