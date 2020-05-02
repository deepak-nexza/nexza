<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'key_id',
        'user_phone',
        'user_email', 
        'user_dob', 
        'user_sex',
        'user_address',
        'user_city',
        'user_state',
        'user_zipcode', 
        'user_type',
        'user_last_login',
        'user_last_login_ip',
        'username', 
        'user_password',
        'user_date', 
        'user_status',
        'profile_pic',
        'created_on', 
        'role',
        'created_at',
        'updated_at', 
        'created_by',
        'updated_by', 
        'remember_token'
    ];
    
     /**
     * Custom primary key is set for the table
     *
     * @var integer
     */
    protected $primaryKey = 'usr_id';

    /**
     * Maintain created_at and updated_at automatically
     *
     * @var boolean
     */
    public $timestamps = true;

    /**
     * Maintain created_by and updated_by automatically
     *
     * @var boolean
     */
    public $userstamps = true;

}
