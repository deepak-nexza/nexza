<?php
namespace App\Repositories\Providers;


use Illuminate\Support\ServiceProvider;
use Validator;
use Helpers;
use App\Repositories\Libraries\Validations\Files\Mimes;
use App\Repositories\Libraries\Validations\Unique\Unique;
use App\Repositories\Libraries\Validations\Email\BlockedEmails;
use App\Repositories\Libraries\Validations\Zipcode\CheckZipcode;
use App\Repositories\Libraries\Validations\Password\CheckPassword;
use App\Repositories\Libraries\Validations\Product\CheckValidProduct;

class HealthDocServiceProvider extends ServiceProvider
{

    /**

     * Bootstrap the application services.

     *

     * @return void

     */

    public function boot()

    {
 // Custom email validator
        Validator::extend('checkdomain', function ($attribute, $value, $parameters) {
            return BlockedEmails::isEmailAllowed($value);
        });
        Validator::extend('unique_multiple', function ($attribute, $value, $parameters, $validator) {
            return Unique::isUnique($attribute, $value, $parameters, $validator);
        });
        Validator::extend('uniquPhone', function ($attribute, $value, $parameters, $validator) {
            return Unique::isUniquePhone($attribute, $value, $parameters, $validator);
        });
        Validator::extend('uniqueData', function ($attribute, $value, $parameters, $validator) {
            return Unique::emailExists($attribute, $value, $parameters, $validator);
        });
        Validator::extend('checkmime', function ($attribute, $value, $parameters, $validator) {
            return (new Mimes($attribute, $value, $parameters, $validator))->isValid();
        });
        Validator::extend('checkpassword', function ($attribute, $value, $parameters, $validator) {
            return (new CheckPassword($attribute, $value, $parameters, $validator))->isValid();
        });
        Validator::extend('old_password', function ($attribute, $value, $parameters, $validator) {
                return Hash::check($value, current($parameters));
        });
        Validator::extend('checkzipcode', function ($attribute, $value, $parameters, $validator) {
            return (new CheckZipcode($attribute, $value, $parameters, $validator))->isValid();
        });
        
        Validator::extend('after_equal', function($attribute, $value, $parameters, $validator) {
            $end = Carbon::createFromFormat('m-d-Y', $value);
            $from = Carbon::createFromFormat('m-d-Y', $parameters[0]);
            $return_status = true;
            if(!empty($parameters[0])) {
                if($from > $end) {
                    $return_status = false;
                }
            }
            return $return_status;
        });
        
        Validator::extend('checkvalidproduct', function ($attribute, $value, $parameters, $validator) {
            return CheckValidProduct::isValid($attribute, $value, $parameters, $validator);
        });
        Validator::extend('pastdate', function($attribute, $value, $parameters, $validator) {     
            if(app()->getLocale()=='fr') {
                $end = Carbon::createFromFormat('d-m-Y', $value);
            }
            else {
               $end = Carbon::createFromFormat('m-d-Y', $value);   
            }                  
            return $end->isPast($end);            
        });        
        Validator::extend('checkauthemail', function($attribute, $value, $parameters, $validator) {  
            return ($value!=$parameters[0]);
        });

        Validator::extend(
            'isvalidchar',
            function ($attribute, $value, $parameters, $validator) {
//                if (!request()->has('__randToken')) {
//                    abort(400);
//                }
//                
//                if (empty(request()->get('__randToken'))) {
//                     abort(400);
//                }
                if (is_array($value)) {
                    foreach ($value as $val) {
                        if (!preg_match('/[\>\<\~\[\]]/', $val)) {
                            return true;
                        }
                        return false;
                    }
                } else {
                    if (!preg_match('/[\>\<\~\[\]]/', $value)) {
                        return true;
                    }
                    return false;
                }
            }
        );
    }


    /**

     * Register the application services.

     *

     * @return void

     */

    public function register()

    {
        
        $this->app->bind('App\Repositories\User\UserInterface', 'App\Repositories\User\UserRepository');
        $this->app->bind('App\Repositories\Event\EventInterface', 'App\Repositories\Event\EventRepository');
        $this->app->bind('App\Repositories\Stats\StatsInterface', 'App\Repositories\Stats\StatsRepository');

    }

}