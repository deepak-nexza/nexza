<?php

namespace App\Repositories\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Repositories\Models\User as User;
use App\Repositories\Entities\User\Exceptions\BlankDataExceptions;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'nex_user';

    /**
     * Custom primary key is set for the table
     *
     * @var integer
     */
    protected $primaryKey = 'id';

    /**
     * Maintain created_at and updated_at automatically
     *
     * @var boolean
     */
    public $timestamps = true;

    /**
     * Identifier to set created_by or updated_by as null
     *
     * @var boolean
     */
    protected static $nullable_user = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lead_no',
        'user_level_id',
        'first_name',
        'last_name',
        'emp_id',
        'email',
        'username',
        'password',
        'loan_amt',
        'age_of_biz',
        'industry_id',
        'credit_score',
        'purpose_of_finance_id',
        'zipcode',
        'current_status',
        'block_status',
        'block_type_id',
        'blocked_at',
        'suspension_key',
        'user_type',
        'is_otp_authenticate',
        'ip_address',
        'zendesk_user_id',
        'remember_token',
        'login_attempted',
        'last_session_id',
        'last_visited_date',
        'is_password_set_onlogin',
        'prefered_comm_mode',
        'is_sms_notification',
        'lead_owner_id',
        'method_type',
        'otp_blocked',
        'is_admin',
        'assign_update_at',
        'promo_code',
        'is_approval_requested',        
        'created_by',
        'updated_by',
        'updated_at',
        'created_at',
        'cust_email',
        'contact_number',
        'biz_name',
        'is_created_from',
        'is_biz_name_update',
        'is_test_user',
        'profile_image',
        'account_number',
        'account_name',
        'ifsc_code',
        'gst_number'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden  = ['password'];
    protected $guarded = ['id'];

    /**
     * Get the user's first name.
     *
     * @param  string $value
     * @return string
     */
    public function getFirstNameAttribute($value)
    {
        if (app()->environment() != 'local') {
            return ucwords(strtolower($value), "() \t\r\n\f\v");
        } else {
            return ucwords(strtolower($value));
        }
    }

    /**
     * Get the user's last name.
     *
     * @param  string $value
     * @return string
     */
    public function getLastNameAttribute($value)
    {
        if (app()->environment() != 'local') {
            return ucwords(strtolower($value), "() \t\r\n\f\v");
        } else {
            return ucwords(strtolower($value));
        }
    }

    /**
     * A user may have multiple applications.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function applications()
    {
        return $this->hasMany('App\B2c\Repositories\Models\Application', 'user_id');
    }

    /**
     * One-to-one relation with user and application.
     *
     * @return json
     */
    public function lastApplicaction()
    {
        return $this->hasOne('App\B2c\Repositories\Models\Application');
    }

    /**
     * Get last application data w.r.t user id
     *
     * @param integer $user_id User ID
     *
     * @return array Last Application Data
     */
    public static function getLastApplication($user_id)
    {
        /**
         * Check id is not blank
         */
        if (empty($user_id)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        /**
         * Check id is not an integer
         */
        if (!is_int($user_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $arrLastApp = self::find((int) $user_id)->lastApplicaction()->orderBy('app_id', 'desc')->first();
        return ($arrLastApp ? : false);
    }


    



    /**
     * update user details
     *
     * @param integer $user_id     user id
     * @param array   $arrUserData user data
     *
     * @return boolean
     */
    public static function updateUser($user_id, $arrUserData = [])
    {
        /**
         * Check id is not blank
         */
        if (empty($user_id)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        /**
         * Check id is not an integer
         */
        if (!is_int($user_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        /**
         * Check Data is Array
         */
        if (!is_array($arrUserData)) {
            throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
        }

        /**
         * Check Data is not blank
         */
        if (empty($arrUserData)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        $rowUpdate = self::find((int) $user_id)->update($arrUserData);
        return ($rowUpdate ? true : false);
    }

    /**
     * update user password
     *
     * @param integer $user_id     user id
     * @param array   $arrUserData user password data
     *
     * @return boolean
     */
    public static function updateUserPassword($user_id, $arrUserData = [])
    {
        /**
         * Check id is not blank
         */
        if (empty($user_id)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        /**
         * Check id is not an integer
         */
        if (!is_int($user_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        /**
         * Check Data is Array
         */
        if (!is_array($arrUserData)) {
            throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
        }

        /**
         * Check Data is norolest blank
         */
        if (empty($arrUserData)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        $rowUpdate = self::find((int) $user_id)->update($arrUserData);

        return ($rowUpdate ? true : false);
    }


    /**
     * Get password by username
     * Required to check legacy password
     *
     * @param string $username
     *
     * @return mixed
     *
     * @throws BlankDataExceptions
     */
    public static function getPassword($username)
    {
        if (empty($username)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        $record = self::select('id', 'password')
            ->where('email', $username)
            ->first();

        return $record ? : false;
    }

    /**
     * Delete user data
     *
     * @param integer $user_id
     * @param array   $arrUserData user data
     *
     * @return boolean
     */
    public static function deleteUser($email)
    {
        $rowUpdate = self::where(['email'=>$email])->delete();

        return ($rowUpdate ? true : false);
    }

    /**
     * Backend user scope
     *
     * @param type $query
     *
     * @return type
     */
    public function scopeBackendUser($query)
    {
        return $query->where('users.user_type', '=', config('b2c_common.USER_BACKEND'));
    }

    /**
     * Get all backend user data
     *
     * @param integer $withadmin
     *
     * @return array User List
     */
    public static function getBackendUsersOld($withadmin = null)
    {

        if ($withadmin === 1) {
            $users = self::with('roles')->backendUser()->orderBy('id', 'desc');
        } else {
            $users = self::with('roles')->whereHas(
                'roles',
                function ($query) {
                        $query->where('is_editable', '=', 1);
                }
            )->backendUser()->orderBy('id', 'desc')->toSql();
        }
        return $users;
    }
    

    /**
     * Get the backend user detail associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function userDetail()
    {
        return $this->hasOne(UserDetail::class, 'user_id', 'id');
    }

    /**
     * Save user details
     *
     * @param  array mixed
     * @return mixed
     */
    public function addUserOtherDetail($userObject, $arrUserDetail)
    {
        $userDetail                           = new UserDetail();
        $userDetail->user_id                  = $userObject->id;
        $userDetail->lending_authority_amount = $arrUserDetail['lending_authority_amount'];
        $userDetail->reporting_manager_id = $arrUserDetail['reporting_manager_id'];
        $userDetail->region_type = $arrUserDetail['region_type'];
        $userDetail->hub_id = $arrUserDetail['hub_id'];

        return $userDetail->save();
    }

    /**
     * Update user details
     *
     * @param  array mixed
     * @return mixed
     */
    public function updateUserOtherDetail($userObject, $arrUserDetail)
    {

        $userObject->userDetail->lending_authority_amount = $arrUserDetail['lending_authority_amount'];
        $userObject->userDetail->reporting_manager_id = $arrUserDetail['reporting_manager_id'];
        $userObject->userDetail->region_type = $arrUserDetail['region_type'];
        $userObject->userDetail->hub_id = $arrUserDetail['hub_id'];


        return $userObject->userDetail->update();
    }

    /* Get UserID by Email and Type
     *
     * @param string $email
     * @param integer $usertype
     * @return integer | boollean
     */

    public static function getUserIdByEmail($email, $usertype=null, $user_id = null)
    {
        $userObj = self::where('email', $email);
        
        if($user_id != null) {
            $userObj = $userObj->where('id', $user_id);
        }
        
        $userObj = $userObj->value('id');
        
        return $userObj ? $userObj : false;
    }

    /**
     * Get Inactive User
     *
     * @param  type $inactive_days
     * @return mixed
     */
    public static function getBackendInactiveUsers($inactive_days)
    {
        $users = self::select(DB::raw("id as ID, first_name as 'First Name', last_name as 'Last Name', email as Email, DATEDIFF(NOW(),last_visited_date) as InactiveDays"))
            ->having('InactiveDays', '>=', $inactive_days)
            ->get();
        return $users ? $users : false;
    }

    /**
     * Get all backend user with role
     *
     * @return array User List
     */
    public static function getBackendUsersWithRole()
    {
        $users = self::from('users as u')
                ->select('u.username as username', 'u.id', 'ur.role_id as role_id')
                ->where('user_type', '=', 1)
                ->leftJoin('role_user as ur', 'ur.user_id', '=', 'u.id')
                ->where('block_status', '=', 0)->get();


        return $users;
    }


    /**
     * Get all backend user data list
     *
     * @return array User List
     */
    public static function getListBackendUsers()
    {
        $users = self::backendUser()->orderBy('last_name', 'asc')->select(
            DB::raw('CONCAT(last_name,", ", first_name) AS full_name'),
            'id'
        )
                ->with('roles')->whereHas(
                    'roles',
                    function ($query) {
                        $query->whereIn('id', [4, 5, 9]);
                    }
                )->get();
        return $users;
    }

    /**
     * check username exist or not
     *
     * @param  string $username
     * @return boolean
     */
    public static function checkUsernameExistance($username)
    {
        $countRow = self::where('username', $username)->count();

        return ($countRow ? false : true);
    }

    /**
     * check email exist or not
     *
     * @param  string $email
     * @return boolean
     */
    public static function checkEmailExistance($email, $old_email)
    {
        $countRow = self::where('email', $email)
                ->where(
                    function ($query) use ($old_email) {
                        if ($old_email != null) {
                            $query->where('email', '!=', $old_email);
                        }
                    }
                )->count();


        return ($countRow ? false : true);
    }

    /**
     * Get user admin list for send notification
     *
     * @return mixed
     */
    public static function getEmailNotificationUserLists()
    {
        $user = self::from('users as u')
            ->select('u.first_name', 'u.last_name', 'u.email')
            ->join('users_detail as ud', 'u.id', '=', 'ud.user_id')
            ->where('u.user_type', '=', config('b2c_common.YES'))
            ->where('ud.is_ws_notification', config('b2c_common.YES'))
            ->where('u.block_status', '=', config('b2c_common.NO'))
            ->get();

        return $user ? $user : false;
    }

    /**
     * check email exist or not
     *
     * @param  string $email
     * @return boolean
     */
    public static function checkEmailAndPassExistance($email, $pass)
    {
        $countRow = self::where('email', $email)
                ->where('password',$pass);
        return ($countRow ? false : true);
    }
    
    
    /**
     * check case owner name
     *
     * @param  string $ename
     * @return boolean
     */
     public static function getUserName($user_id)
    {   
         $res = self::select('first_name','last_name')
                        ->where('id', $user_id)
                        ->first();
         return $res;
    }
    
    
    /**
     * check case owner name
     *
     * @param  string $ename
     * @return boolean
     */
    public static function getUserData($whereCls = [], $selectArr = [])
    {
         /**
         * Check id is not an integer
         */
        if (!is_array($whereCls)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }
        if(empty($selectArr)) {
            $arrUserData = self::select('*');
        } else {
           $arrUserData = self::select($selectArr);
        }
         
        if(!empty($whereCls) && count($whereCls) > 0) {
            $arrUserData =$arrUserData->where($whereCls);
        }
        
        $arrUserData =$arrUserData->first();
        return ($arrUserData ? $arrUserData: false);
    }
    
    
    public static function findUser($id)
    {
        $data = self::where('id', $id)->first();
        return ($data ? $data : 'true');
    }
    
    /**
     * Get case owner details
     * 
     * @param array $attribute
     * @param array $select
     * @return mixed
     */
    public static  function getUserBankDetails($user_id , $app_id=null) 
    {   
        /**
         * Check Data is Array
         */
        if (!is_int($user_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }
        

        $result = self::select('bv.id', 'bv.bank_name')
                ->join('financedata_users_account as yba', 'users.id', '=', 'yba.user_id')
                ->join('financedata_banks as bv', 'bv.id', '=', 'yba.bank_id')
                ->where('users.id', $user_id)
                ->where(
                    function ($query) use ($app_id) {
                        if ($app_id != null) {
                            $query->where('yba.app_id', '=', $app_id);
                        }
                    }
                )->groupBy('bv.id')->get();
        return ($result ?: false);
    }
    
    /**
     * Get customer list
     * 
     * @return mixed
     */
    public static function getCustomerListing()
    {
        $result = self::select(
                        'users.*', \DB::raw("CONCAT(first_name,' ',last_name) AS customer_name")
                )
                ->where('user_level_id', 1);

        return ($result ? $result : false);
    }
    
    
    
    /**
     * Get business name
     *
     * @param $business_name
     * @return boolean
     * @throws InvalidDataTypeExceptions
     */
    public static function checkEmailUnique($emilUique)
    {
        $users = self:: where('email', '=', $emilUique)->first();
        return($users? $users :false );      
    }
    
    /**
     * Get business name
     *
     * @param $business_name
     * @return boolean
     * @throws InvalidDataTypeExceptions
     */
    public static function checkIfPhone($ephoneUique)
    {
        $users = self:: where('contact_number', '=',(int) $ephoneUique)->first();
        return($users? $users :false );      
    }
    
    /**
     * Check if user email exist
     * 
     * @param string $email
     * @param integer $user_id
     * @return array|boolean
     * @throws InvalidDataTypeExceptions
     */
    public static function checkUserEmail($email, $user_id)
    {
        //Check $user_id is not an integer
        if (!is_int($user_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }
        
        $resultData = self::where('email', $email);
            
        if (!empty($user_id)) {
            $resultData->where('id', '!=', $user_id);
        }

        $resultData = $resultData->count();
        return $resultData ? $resultData : false;
    }
    
    /**
     * Get user data
     *
     * @param array $attributes
     * @return array
     * @throws InvalidDataTypeExceptions
     */
    public static function getAllUsersByLevelId($attributes = [], $select = [], $userLavelId)
    {
        /**
         * Check $attributes is not array
         */
        if (!is_array($attributes)) {
            throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
        }

        $result = self::select($select);
        $splitArr = preg_split('/\s+/', $attributes['searchText']);
        $firstName = empty($splitArr[0])?$attributes['searchText']:$splitArr[0];
        $lastName = empty($splitArr[1])?$attributes['searchText']:$splitArr[1];
        if (!empty($attributes['searchText']) && $attributes['searchText']!='search') {
               $result = $result->whereRaw("concat(first_name,' ',last_name)  like '%".$attributes['searchText']."%'" );
        }
        $result = $result->whereIn('user_level_id', [$userLavelId]);
        $result = $result->where('first_name', '!=', null);
        $result = $result->where('last_name', '!=', null);
        $result = $result->orderBy('last_name', 'ASC');
        $result = $result->get();
        return ($result ? $result : false);
    }

    /**
     * update user details
     *
     * @param integer $user_id     user id
     * @param array   $arrUserData user data
     *
     * @return boolean
     */
    public static function saveUser($attributes = [], $user_id = null)
    {
        /**
         * Check id is not blank
         */
        if (empty($attributes)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        $query = self::updateOrCreate(['id' => (int) $user_id], $attributes);
        return $query ? $query : '';
    }
    
    /**
     * Get User Details base of user Id
     *
     * @param  integer $user_id
     * @return array
     * @throws BlankDataExceptions
     * @throws InvalidDataTypeExceptions
     * Since 0.1
     */
    public static function getUserDetail($user_id)
    {

        //Check id is not blank

        if (empty($user_id)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }
        //Check id is not an integer

        if (!is_int($user_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $arrUser = self::select('nex_user.*')
                ->where('nex_user.id', (int) $user_id)
                ->first();
        return ($arrUser ? $arrUser : false);
    }
    
     /**
     * Get user details
     *
     * @param integer $user_id
     * @param integer $app_id
     *
     * @return mixed Array | Boolean false
     * @throws InvalidDataTypeExceptions
     */
    public static function getUserDetails($whereArr = [], $select=[]) {
        /**
         * Check id is not an integer
         */
        if (!is_array($whereArr)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

         if(empty($select)) {
             $arrAppData = self::select('*');
         } else {
            $arrAppData = self::select($select);
         }
        $arrAppData =$arrAppData->where($whereArr)->first();
        return ($arrAppData ? $arrAppData: false);
    }
    
}
