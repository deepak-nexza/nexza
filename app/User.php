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
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
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

        $arrUser = self::select('users.*', 'mst_lead_status.lead_status','app.app_id')
                ->where('users.id', (int) $user_id)
                ->leftjoin('app', 'app.app_user_id', '=', 'users.id')
                ->leftjoin('mst_lead_status', 'mst_lead_status.id', '=', 'users.current_status')
                ->first();
        return ($arrUser ? $arrUser : false);
    }

    /**
     * Get All User
     *
     * @param user object $userRole
     *
     * @return array $users
     */
    public static function getAllLead($userRole)
    {
            $users = self::
                From('users as u')
                ->select(
                    'u.id',
                    'u.lead_no',
                    'u.first_name',
                    'u.last_name',
                    'u.email',
                    'app.app_phone',
                    'app.app_date',
                    'mst_lead_status.lead_status',
                    'mst_appointment_time.name as time',
                    'u.created_at'
                )
                ->leftJoin('mst_lead_status', 'u.current_status', '=', 'mst_lead_status.id')
                ->leftJoin('app', 'u.id', '=', 'app.user_id')
                ->leftJoin('mst_appointment_time', 'app.app_time', '=', 'mst_appointment_time.id')
                ->where('u.user_type', config('b2c_common.USER_FRONTEND'))
                ->orderBy('u.id', 'desc');
                if ($userRole->is_default_ml != 1) {

                if ($userRole->id == config('b2c_common.ROLE_CENTRAL_SALES_TEAM')) {
                    $users->Where(function ($query) {
                           $query->where('u.lead_owner_id', Auth::user()->id);
                           $query->orWhere(function ($query) {
                               $query->whereNull('u.lead_owner_id');
                           });
                       });
                } else {
                    $users->join(
                    'shareapp', function ($join) {
                    $join->on('u.id', '=', 'shareapp.user_id')
                        ->where('shareapp.to_id', '=', Auth::user()->id)
                        ->where('shareapp.is_owner', '=', config('b2c_common.YES'));
                }
                )->groupBy('u.id');
            }
            }

        return $users;
    }


    /**
     * Get backend user list w.r.t role for lead/case assignment process
     *
     * @param int $role_id
     *
     * @return mixed
     */
    public static function getBackendUsersWithSpecificRole($role_id)
    {
        //Check id is not blank

        if (empty($role_id)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }
        //Check id is not an integer

        if (!is_int($role_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $arrUsers = self::from('users')
                ->select(
                    'users.id', 'users.first_name', 'users.last_name','users.email', 'mst_hubs.'.app()->getLocale().'_hubs as hub_name', 'users_detail.region_type'
                )
                ->leftJoin('users_detail', 'users_detail.user_id', '=', 'users.id')
                ->leftJoin('mst_hubs', 'mst_hubs.id', '=', 'users_detail.hub_id')
                ->where('users.user_type', '=', 1)
                ->where('users.block_status', 0)
                ->with('roles')->whereHas(
                    'roles',
                    function ($query) use($role_id){
                        $query->where('id', $role_id);
                    }
                )
                ->get();

        return ( $arrUsers ? : false );
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
     * A user may have multiple roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Assign the given role to the user.
     *
     * @param string $role
     *
     * @return mixed
     */
    public function assignRole($role_id)
    {
        return $this->roles()->sync(array($role_id));
    }

    /**
     * Determine if the user has the given role.
     *
     * @param  mixed $role
     * @return boolean
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        return !!$role->intersect($this->roles)->count();
    }

    /**
     * Determine if the user may perform the given permission.
     *
     * @param Permission $permission
     *
     * @return boolean
     */
    public function hasPermission(Permission $permission)
    {
        return $this->hasRole($permission->roles);
    }

    /**
     * Get Roles by user id
     *
     * @param $user_id user id
     *
     * @return object roles
     */
    public static function getUserRoles($user_id)
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

        $arrRoles = self::find($user_id)->roles;

        return ($arrRoles ? : false);
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
    public static function deleteUser($user_id, $arrUserData)
    {
        $rowUpdate = self::find((int) $user_id)->update($arrUserData);

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
     * Get all backend user data
     *
     *
     * @return  array User List
     *
     * @since 0.1
     *
     */
    public static function getBackendUsers()
    {
        $users = self::select(
            'users.id',
            'users.first_name',
            'users.emp_id',
            'users.email',
            'roles.name as display_name',
            'users.created_at',
            'users.last_name',
            'users.block_status'
        )
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->where('roles.is_editable', '=', config('b2c_common.YES'))
            ->backendUser();
        return ($users ? $users : false);
    }

    /**
     * Get backend user data w.r.t id
     *
     * @param integer $user_id
     *
     * @return array User List
     */
    public static function getBackendUser($user_id)
    {
        $users = self::with('roles')->backendUser()
            ->where('id', $user_id)
            ->get();

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

    public static function getUserIdByEmail($email, $usertype, $user_id = null)
    {
        $userObj = self::where('email', $email);
        $userObj = $userObj->where('user_type', $usertype);
        
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

    /*
     * Get Last Lead number
     */
    public static function getLastLeadNo($year)
    {
        $arrLeadResult = self::select("lead_no", "created_at")
                        ->orderBy('id', 'desc')
                        ->whereNotNull('lead_no')
                        ->where('user_type', config('b2c_common.USER_FRONTEND'))
            ->whereYear('created_at', '=', $year)
            ->first();
        return $arrLeadResult ?: false;
    }

    /**
     * Get reporting manager
     *
     * @param integer $role_id
     * @return array
     */
    public static function getReportingManager($role_id)
    {
        /**
         * Check id is not blank
         */
        if (empty($role_id)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        /**
         * Check id is not an integer
         */
        if (!is_int($role_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $rowData = Role::getRole($role_id);
        $parent_role_id = isset($rowData->parent_role_id) ? $rowData->parent_role_id : null;
        if($parent_role_id !== null){
           $user = self::from('users')
            ->select('users.first_name', 'users.last_name', 'users.id')
            ->join('users_detail as ud', 'users.id', '=', 'ud.user_id')
            ->where('users.user_type', '=', config('b2c_common.YES'))
            ->where('users.block_status', '=', config('b2c_common.NO'))
            ->with('roles')->whereHas(
                    'roles',
                    function ($query) use($parent_role_id) {
                        $query->where('id', $parent_role_id);
                    }
                )->get();

        return $user ? $user : false;
        }
    }

    /**
    * Get co listing for application assignment
    *
    * @param integer $role_id
    * @param integer $region_type
    *
    * @return array
    */
    public static function getCOSupervisors($role_id, $region_type)
    {
        //Check id is not blank

        if (empty($role_id)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }
        //Check id is not an integer

        if (!is_int($role_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $arrUsers = self::from('users')
                ->select(
                    'users.id', 'users.first_name', 'users.last_name'
                )
                ->leftJoin('users_detail', 'users_detail.user_id', '=', 'users.id')
                ->where('users.user_type', '=', 1)
                ->where('users.block_status', 0)
                ->where(function($query) use($region_type){
                    if($region_type != '' && $region_type != 4){
                        $query->where('users_detail.region_type', $region_type);
                    }
                })->with('roles')->whereHas(
                    'roles',
                    function ($query) use($role_id){
                        $query->where('id', $role_id);
                    }
                )
                ->get();

        return ( $arrUsers ? : false );
    }

    /**
     * Get backend user(co) list w.r.t CO Supervisor and SBLU Supervisior for case assignment process
     *
     * @param int $role_id
     * @param int $reporting_manager_id
     *
     * @return mixed
     */
    public static function getSubordinatesForSupervisior($role_id, $reporting_manager_id)
    {
        //Check id is not blank

        if (empty($role_id)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }
        //Check id is not an integer

        if (!is_int($role_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $arrUsers = self::from('users')
                ->select(
                    'users.id', 'users.first_name', 'users.last_name'
                )
                ->leftJoin('users_detail', 'users_detail.user_id', '=', 'users.id')
                ->where('users_detail.reporting_manager_id', '=', $reporting_manager_id)
                ->where('users.user_type', '=', 1)
                ->where('users.block_status', 0)
                ->with('roles')->whereHas(
                    'roles',
                    function ($query) use($role_id){
                        $query->where('id', $role_id);
                    }
                )
                ->get();

        return ( $arrUsers ? : false );
    }


    /**
     * Get all backend user id w.r.t a parent role
     *
     * @param integer $role_id
     *
     * @return mixed
     */
    public static function getUsersListForParentRole($role_id)
    {
        $role_data = Role::getRole($role_id);
        $data = $role_data->child()->get();
        $childRoleId = Helpers::getAllRoleForParentRole($data);

        $users = self::backendUser()->select('*')
                ->with('roles')->whereHas(
                    'roles',
                    function ($query) use($childRoleId){
                        $query->whereIn('id', $childRoleId);
                    }
                )->get();

        return $users ? : false;
    }


    /**
     * Count all registered users
     *
     * @param object $userRole
     * @param string $fromDate
     * @param string $toDate
     *
     * @return integer
     */
    public static function countLeads($userRole, $fromDate, $toDate)
    {
       if ($userRole->is_default_ml == 1) {
            $users = self::join('sharelead', 'users.id', '=', 'sharelead.user_id')
                //->leftJoin('app', 'users.id', '=', 'app.user_id')
                ->where('users.lead_no', '!=', '')
                ->where('users.block_status', '=', 0)
                //->whereRaw(self::convertTz('sharelead.created_at') . ' >= ?', [$fromDate])
                //->whereRaw(self::convertTz('sharelead.created_at') . ' <= ?', [$toDate])
                ->whereRaw(self::convertTz('users.created_at') . ' >= ?', [$fromDate])
                ->whereRaw(self::convertTz('users.created_at') . ' <= ?', [$toDate])
                //->whereNull('users.id')
                ->where('users.current_status', '=', config('b2c_common.NEW_LEAD_STATUS'))
                ->distinct('sharelead.user_id')
                ->count('sharelead.user_id');
        } else {
            $usersArr  = [];
            $usersArr  = self::getUserListByParentRoleId($userRole->id);
            $callback  = function ($out, $e) {
                $out[] = $e['id'];
                return $out;
            };
            $userIdsArr = array_reduce($usersArr, $callback, []);

            $users = 0;
            if (count($userIdsArr) > 0) {
            $users = self::join('sharelead', 'users.id', '=', 'sharelead.user_id')
                  //->leftJoin('app', 'users.id', '=', 'app.user_id')
                  ->where('users.lead_no', '!=', '')
                  ->where('users.block_status', '=', 0)
                  ->whereIn('sharelead.to_id', $userIdsArr)
                  //->whereRaw(self::convertTz('sharelead.created_at') . ' >= ?', [$fromDate])
                  //->whereRaw(self::convertTz('sharelead.created_at') . ' <= ?', [$toDate])
                  ->whereRaw(self::convertTz('users.created_at') . ' >= ?', [$fromDate])
                  ->whereRaw(self::convertTz('users.created_at') . ' <= ?', [$toDate])
                  //->whereNull('users.id')
                  ->where('users.current_status', '=', config('b2c_common.NEW_LEAD_STATUS'))
                  ->distinct('sharelead.user_id')
                  ->count('sharelead.user_id');
            }
        }

        return ($users > 0 ) ? $users : 0;
    }


    /**
     * Get all backend user by role id
     *
     * @return array User List
     */
    public static function getUsersByRoleId($role_id)
    {
        $users = self::from('users as u')
                ->where('user_type', '=', 1)
                ->leftJoin('role_user as ur', 'ur.user_id', '=', 'u.id')
                ->where('block_status', '=', 0)
                ->where('role_id', '=', $role_id)
                ->get();

        return $users;
    }


    /**
     * Get user's ids w.r.t role
     *
     * @param integer $role_id
     *
     * @return array
     */
    public static function getUserListByParentRoleId($role_id)
    {
        $users      = self::getUsersByRoleId($role_id)->toArray();
        $childUsers = self::getUsersListForParentRole($role_id)->toArray();
        $users = array_merge($users, $childUsers);
        return $users;
    }

    /**
     * Get Users By Hub Id
     *
     * @param int $hub_id
     * @return mixed
     */
    public static function getHubUsers($hub_id)
    {
        $users = self::from('users as u')
                ->select('u.id')
                ->join('users_detail as ud', 'u.id', '=', 'ud.user_id')
                ->where('u.user_type', '=', 1)
                ->where('u.block_status', '=', 0)
                ->where('ud.hub_id', '=', $hub_id)
                ->lists('u.id');
        return isset($users[0]) ? $users->toArray() : [];
    }

    /**
     * Get all sales team unblocked user
     *
     * @return mixed array of user
     */
    public static function getCentralSalesTeamUserLists() {

        $users = self::select('first_name', 'last_name', 'email')
                ->join('role_user','role_user.user_id','=','users.id')
                ->where('role_user.role_id', config('b2c_common.ROLE_CENTRAL_SALES_TEAM'))
                ->where('user_type', '=', config('b2c_common.USER_BACKEND'))
                ->where('block_status', config('b2c_common.NO'))
                ->get();

        return $users ?: false;
    }
    /**
     * Get user by specific id
     *
     * @param integer $role_id
     * @return array
     */
    public static function getAllUserByRole($role_id)
    {
        //Check id is not an integer

        if (!is_int($role_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $users = self::join('role_user', 'role_user.user_id', '=', 'users.id')
            ->where('role_user.role_id', $role_id)
            ->select('users.id')
            ->get();

        return $users;
    }

    /**
     * Check backend lead access
     *
     * @param integer $loggedin_id
     * @param integer $user_id
     * @param object $userRole
     *
     * @return boolean
     */
    public static function checkLeadAccess($loggedin_id, $user_id, $userRole)
    {
        if ($userRole->id == config('b2c_common.ROLE_CENTRAL_SALES_TEAM')) {
            $users = self::
                From('users as u')
                ->select('u.lead_owner_id')
                ->where('u.id', '=', $user_id)
                ->first();
            return (($users['lead_owner_id'] == $loggedin_id) || ($users['lead_owner_id'] === null)) ? true : false;
        } else {
            $users = self::From('shareapp')
                    ->where('shareapp.user_id', '=', $user_id)
                    ->where('shareapp.to_id', '=', $loggedin_id)
                    ->where('shareapp.is_owner', '=', config('b2c_common.YES'))->count();

            return ($users > 0) ? true : false;
        }
    }
    
    public static function getAllApps()
    {
        
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
    
    
    /**
     * Get user data
     *
     * @param array $attributes
     * @return array
     * @throws InvalidDataTypeExceptions
     */
    public static function getBackendUserList($attributes = [], $select = [])
    {
        /**
         * Check $attributes is not array
         */
        if (!is_array($attributes)) {
            throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
        }

        if (empty($select)) {
            $select = ['*'];
        }

        $result = self::select($select);
        
        if (isset($attributes['user_level_id'])) {
            $result = $result->whereIn('user_level_id', $attributes['user_level_id']);
        }
        if (isset($attributes['is_admin'])) {
            $result = $result->where('is_admin', $attributes['is_admin']);
        } else {
            //$result = $result->where('is_admin', null);
        }
        if (isset($attributes['block_status'])) {
            $result = $result->where('block_status', $attributes['block_status']);
        }
        if($attributes['current_owner'] == Auth::user()->id) {
            $result = $result->where('id', '<>', Auth::user()->id);
        }
        $result = $result->get();

        return ($result ? $result : false);
    }
    
    /**
     * Get user data
     *
     * @param array $attributes
     * @return array
     * @throws InvalidDataTypeExceptions
     */
    public static function getAllBackendUserData($attributes = [], $select = [])
    {
       
        /**
         * Check $attributes is not array
         */
        if (!is_array($attributes)) {
            throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
        }

        if (empty($select)) {
            $select = ['*'];
        }

        $result = self::select($select);
        
        if (isset($attributes['user_level_id'])) {
            $result = $result->whereIn('user_level_id', $attributes['user_level_id']);
        }
        
        if (isset($attributes['block_status'])) {
            $result = $result->where('block_status', $attributes['block_status']);
        }
        
        if (isset($attributes['is_admin'])) {
            $result = $result->where('is_admin', $attributes['is_admin']);
        }
        
        if (isset($attributes['biz_name'])) {
            $result = $result->where('biz_name', 'LIKE', '%'.$attributes['biz_name'].'%')->groupBy('biz_name');
        }
        
        if (isset($attributes['not_logged_in'])) {
            $result = $result->where('id', '<>', Auth::user()->id);
        }
        $result = $result->get();
        return ($result ? $result : false);
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
    
    /**
     * check employee id exist or not
     *
     * @param  string $email
     * @return boolean
     */
    public static function checkEmpIdExit($emp_id)
    {
        $countRow = self::where('emp_id', $emp_id)->get();
        return (count($countRow)>0 ? 'false' : 'true');
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
     * Get user data
     *
     * @param array $attributes
     * @return array
     * @throws InvalidDataTypeExceptions
     */
    public static function getAllusersWithUserLevelID($attributes = [], $select = [])
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
        $result = $result->whereIn('user_level_id', [config('b2c_common.BACKEND_USERLEVEL'),config('b2c_common.RM_USERLEVEL')]);
        $result = $result->where('first_name', '!=', null);
        $result = $result->where('last_name', '!=', null);
        $result = $result->orderBy('last_name', 'ASC');
        $result = $result->get();
        return ($result ? $result : false);
    }
    
    
    /**
     * Get business name
     *
     * @param $business_name
     * @return boolean
     * @throws InvalidDataTypeExceptions
     */
    public static function checkBusinessName($business_name)
    {
        $users = self:: where('biz_name', '=', $business_name)->count();
        return($users >=1 ? true:false );      
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
}