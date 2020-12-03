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
        Route::get('/logout',['as'=> 'logout','uses'=>'Auth\LoginController@logout']);
});
});
    
       Route::get('/login', 'Auth\LoginController@getLogin')->name('login');
Route::post('/login', 'Auth\LoginController@postLogin')->name('login');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');
 
Route::get('/create-profile', 'Auth\RegisterController@createProfile')->name('create_profile');
Route::post('/save-profile', 'Auth\RegisterController@saveProfile')->name('save_profile');

    


Route::get('/', 'GuestController@index')->name('/');
Route::get('event-detail', 'GuestController@eventDetails')->name('event_detail');

Route::get('/home', 'HomeController@index')->name('home');


//
//Route::get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
//Route::get('/login', 'Auth\LoginController@showLoginForm')->name('login');
//Route::get('/logut', 'Auth\LoginController@logout')->name('logout');

Route::post('/register', 'Auth\RegisterController@register')->name('register');
//
//

Route::get('password/reset', 'Auth\PasswordController@showLinkRequestForm')->name('password.request');
Route::post('password/email', 'Auth\PasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/reset/{token}/{id}', 'Auth\PasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\PasswordController@reset')->name('password.update');

Route::get('/profile', 'HomeController@openProfile')->name('profile');
//Route::get('/update-profile', 'HomeController@updateProfile')->name('update_profile');
Route::post('/search', 'HomeController@search')->name('search');
Route::get('/search', 'HomeController@search')->name('search');
Route::post('/searchlist', 'HomeController@searchlist')->name('searchlist');
Route::get('/event-detail/{any}', 'HomeController@eventDetailPage')->name('event_detail');
Route::post('/toSearch', 'HomeController@toSearchList')->name('search_list');
Route::get('/about', 'GuestController@aboutUs')->name('about');
Route::get('/contact', 'GuestController@contactus')->name('contact');
Route::get('/event-gallery', 'GuestController@event_gallery')->name('event_gallery');
Route::get('/privacy-policy', 'GuestController@privacy_policy')->name('privacy_policy');
Route::get('disclaimer/', 'GuestController@disclaimer')->name('disclaimer');
Route::get('t-c/', 'GuestController@tandc')->name('t_c');


///booking

Route::get('/book/{name}', 'GuestController@bookEvent')->name('book_event');
Route::post('/book/{name}/candidates', 'GuestController@bookEventCandidates')->name('book_event_candidates');
Route::post('/saveCandidate', 'GuestController@saveCandidate')->name('save_candidate');
Route::post('/pay/{event}', 'GuestController@payNow')->name('pay');
Route::get('/thankyou', 'GuestController@thankyou')->name('thankyou');

Route::get('paywithrazorpay', 'RazorpayController@payWithRazorpay')->name('paywithrazorpay');
// Post Route For Makw Payment Request
Route::get('payment-success', 'RazorpayController@payment')->name('payment_success');
Route::post('payment', 'RazorpayController@payment')->name('payment');
Route::post('dopayment', 'RazorpayController@dopayment')->name('dopayment');
Route::get('receipt/{order}/success', 'RazorpayController@receipt')->name('receipt');

