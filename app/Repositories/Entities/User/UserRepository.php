<?php

namespace App\B2c\Repositories\Entities\User;

use Helpers;
use App\B2c\Repositories\Models\UserLog;
use App\B2c\Repositories\Models\Appointment;
use App\B2c\Repositories\Models\ApplicationAppointment;
use App\B2c\Repositories\Models\UserTemp;
use App\B2c\Repositories\Models\LeadNotes;
use App\B2c\Repositories\Models\UserDetail;
use App\B2c\Repositories\Models\UserTicket;
use App\B2c\Repositories\Models\UserTicketLog;
use App\B2c\Repositories\Models\UserLastPassword;
use App\B2c\Repositories\Models\PasswordReset;
use App\B2c\Repositories\Contracts\UserInterface;
use App\B2c\Repositories\Models\User as UserModel;
use App\B2c\Repositories\Contracts\Traits\AuthTrait;
use App\B2c\Repositories\Models\SecurityQuestionsLog;
use App\B2c\Repositories\Models\Master\Answer as Answer;
use App\B2c\Repositories\Models\Role as RoleModel;
use App\B2c\Repositories\Models\Master\Checklist as Checklist;
use App\B2c\Repositories\Models\Master\CancelAppReason as CancelAppReasonModel;
use App\B2c\Repositories\Models\Master\CancelReasonAppLog as CancelReasonAppLog;
use App\B2c\Repositories\Models\Master\PrimeRate as PrimeRateModel;
use App\B2c\Repositories\Models\Master\PromoCode as PromoCodeModel;
use App\B2c\Repositories\Models\Master\PromoCodeLog as PromoCodeLogModel;
use App\B2c\Repositories\Models\Master\SwitchControl as SwitchControlModel;
use App\B2c\Repositories\Models\Master\SwitchControlLog as SwitchControlLogModel;
use App\B2c\Repositories\Models\UserPromoCode as UserPromoCodeModel;
use App\B2c\Repositories\Models\Master\ExchangeRate as ExchangeRateModel;
use App\B2c\Repositories\Models\ChecklistRole as ChecklistRole;
use App\B2c\Repositories\Models\ChecklistRoleLog as ChecklistRoleLog;
use App\B2c\Repositories\Factory\Repositories\BaseRepositories;
use App\B2c\Repositories\Contracts\Traits\CommonRepositoryTraits;
use App\B2c\Repositories\Models\Permission as PermissionModel;
use App\B2c\Repositories\Models\UserTempPassword as UserTempPassword;
use App\B2c\Repositories\Entities\User\Exceptions\BlankDataExceptions;
use App\B2c\Repositories\Models\PermissionRole as PermissionRole;
use App\B2c\Repositories\Entities\User\Exceptions\InvalidDataTypeExceptions;
use App\B2c\Repositories\Models\ChecklistRolesLevel;
use App\B2c\Repositories\Models\ActivityLog;
use App\B2c\Repositories\Models\SendMail;
use App\B2c\Repositories\Models\Master\DeclineDecision;
use App\B2c\Repositories\Models\Master\PrimeRateLog as PrimeRateLogModel;

class UserRepository extends BaseRepositories implements UserInterface
{

    use CommonRepositoryTraits,
        AuthTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Create method
     *
     * @param array $attributes
     */
    protected function create(array $attributes)
    {
        return UserModel::create($attributes);
    }

    /**
     * Find method
     *
     * @param mixed $id
     * @param array $columns
     */
    public function find($id, $columns = array('*'))
    {
        return (UserModel::find($id)) ? : false;
    }

    /**
     * Get All User
     *
     * @param user object $userRole
     * @return array
     */
    public function getAllLead($userRole)
    {
        $result = UserModel::getAllLead($userRole);
        return $result  ? : false;
        //return (UserModel::getAllLead($userRole)) ?  : false;
    }

    /**
     * Get Leads Notes list by Userid
     *
     * @param integer $user_id
     *
     * @return mixed
     */
    public function getLeadNotes($user_id)
    {
        return LeadNotes::getLeadNotes((int) $user_id);
    }

    /**
     * Save Leads Notes
     *
     * @param array $leadNotes
     *
     * @return mixed
     */
    public function saveLeadNotes($leadNotes = [])
    {
        return LeadNotes::saveLeadNotes($leadNotes);
    }
    /**
     * Update current lead status
     * @param int $leadid
     * @param array $dataarray
     * @return boolean
     */
    public function saveLeadStatus($arrStatus, $user_id)
    {
        $result = Appointment::saveLeadStatus($arrStatus, $user_id);
        return $result;
    }

    /**
     * Get users listing for lead assignment
     * @param int $role_id
     * @return array
     */
    public function getBackendUsersWithSpecificRole($role_id)
    {
        return UserModel::getBackendUsersWithSpecificRole($role_id);
    }

    /**
     * Insert pre register data
     *
     * @param array $attributes
     */
    public function updateUserTempData(array $attributes, $session_id = null)
    {
        /**
         * Check Data is Array
         */
        if (!is_array($attributes)) {
            throw new InvalidDataTypeExceptions('Please send an array');
        }

        /**
         * Check Data is not blank
         */
        if (empty($attributes)) {
            throw new BlankDataExceptions('No Data Found');
        }

        return UserTemp::updateUser($session_id, $attributes);
    }

    /**
     * Get User prefilled data based on
     * session id
     *
     * @param string $session_id
     */
    public function getUserDataBySessionId($session_id)
    {
        return UserTemp::getUserDataBySessionId($session_id);
    }


    /**
     * Update method
     *
     * @param array $attributes
     */
    public function update(array $attributes, $id)
    {
        $result = UserModel::updateUser((int) $id, $attributes);
        return $result ? : false;
    }

    /**
     * Delete method
     *
     * @param mixed $ids
     */
    protected function destroy($ids)
    {
        //
    }

    /**
     * Validating and parsing data passed thos this method
     *
     * @param array $attributes
     * @param mixed $user_id
     *
     * @return New record ID that was added
     *
     * @since 0.1
     */
    public function save($attributes = [], $user_id = null)
    {
        /**
         * Check Data is Array
         */
        if (!is_array($attributes)) {
            throw new InvalidDataTypeExceptions('Please send an array');
        }

        /**
         * Check Data is not blank
         */
        if (empty($attributes)) {
            throw new BlankDataExceptions('No Data Found');
        }

        return is_null($user_id) ? $this->create($attributes) : $this->update($attributes, $user_id);
    }

    /**
     * Save appointment details
     */
    public function saveAppointment($attributes, $user_id = null)
    {
        return Appointment::saveAppointment($attributes, $user_id);
    }


    /**
     * Save appointment details
     */
    public function saveAppointmentForApplication($attributes, $appointment_id=null)
    {
        return ApplicationAppointment::saveAppointment($attributes, $appointment_id);
    }


    /**
     * Save appointment details
     */
    public function getAppointmentDetails($user_id)
    {
        return Appointment::getAppointmentDetails($user_id);
    }

    /**
     * Get a user model by email
     *
     * @param string $email
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getUserByEmail($email)
    {
        $result = UserModel::where('email', $email)->orWhere('emp_id', $email)->first();

        return $result ? : false;
    }

    /**
     * Get a user model by soe id
     *
     * @param string $username
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getUserByUsername($username)
    {
        $result = UserModel::where('username', $username)->first();
        return $result ? : false;
    }

    /**
     * Get a user model by id
     *
     * @param integer $user_id
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getUserDetail($user_id)
    {
        $result = UserModel::getUserDetail((int) $user_id);

        return $result ? : false;
    }

    /**
     * Update user information
     *
     * @param integer $user_id
     *
     * @param array   $attributes
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function updateUser($user_id, $attributes = [])
    {
        /**
         * Check Data is Array
         */
        if (!is_array($attributes)) {
            throw new InvalidDataTypeExceptions('Please send an array');
        }

        /**
         * Check Data is not blank
         */
        if (empty($attributes)) {
            throw new BlankDataExceptions('No Data Found');
        }

        $result = UserModel::updateUser((int) $user_id, $attributes);

        return $result ? : false;
    }

    /**
     * Update user password
     *
     * @param integer $user_id
     *
     * @param array   $attributes
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function updateUserPassword($user_id, $attributes = [])
    {
        $result = UserModel::updateUserPassword((int) $user_id, $attributes);
        return $result ? : false;
    }

    /**
     * Get All Backend User
     *
     * @param  mixed $withadmin
     * @return array
     */
    public function getBackendUsers($withadmin = null)
    {
        return (UserModel::getBackendUsers($withadmin)) ? : false;
    }

   /**
     * Get All Backend User
     *
     */
    public function getBackendUserData()
    {
        return (UserModel::getBackendUsers()) ? : false;
    }


    /**
     * Get a backend user by id
     *
     * @param integer $user_id
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getBackendUser($user_id)
    {
        $user = UserModel::getBackendUser((int) $user_id);

        if (empty($user)) {
            return false;
        }

        return $user[0];
    }
    /**
     * Checks block status of a user
     *
     * @param Eloquent Model $userObject
     *
     * @return boolean
     *
     * @since 0.1
     */

    /**
     * Detach user role
     */
    public function assignRole($userObject, $role_id)
    {
        return $userObject->assignRole($role_id);
    }

    /**
     * Delete user
     *
     * @return boolean
     */
    public function deleteUser($user_id, $attributes)
    {
        $result = UserModel::deleteUser((int) $user_id, $attributes);

        return $result ? true : false;
    }

    /**
     * Checks block status of a user
     *
     * @param Eloquent Model $userObject
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function isBlocked($userObject = null)
    {
        $user = is_null($userObject) ? $this->getAuthUserData() : $userObject;

        return ($user && $user->block_status == config('b2c_common.YES')) ? true : false;
    }

    /**
     * Get All Permissions
     *
     * @param void
     *
     * @return Permission
     *
     * @since 0.1
     */
    public function getPermissionList()
    {
        return PermissionModel::getPermissionList();
    }
    /* Get All Permissions
     *
     * @param void()
     *
     * @return object permissions
     *
     * @since 0.1
     */

    public function getPermissions()
    {
        $arrPermissions = PermissionModel::getAllPermissions();
        return $arrPermissions;
    }

    /**
     * Get all children of permmision
     *
     * @param type $permission_idgetChildByPermissionId
     *
     * @return permissions object
     */
    public function getChildByPermissionId($permission_id)
    {
        return PermissionModel::getChildByPermissionId($permission_id);
    }

    /**
     * Give permission to role
     *
     * @param array $attributes
     *
     * @return object
     */
    public function givePermissionTo($roleid, $permission)
    {

        $role   = RoleModel::where('id', $roleid)->first();
        $result = $role->assignRolePermission($permission);

        return $result ? : false;
    }

    /**
     * Give permission to Rule
     *
     * @param int $attributes
     *
     * @return object
     */
    public function dettachPermissionTo($roleid)
    {
        $role   = RoleModel::where('id', $roleid)->first();
        $result = $role->dettachPermissionTo($role);
        return $result ? : false;
    }

    /**
     * A permission can be applied to roles.
     *
     * @param integer $route_id
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getPermissionRoles($route_id)
    {
        $result = PermissionModel::getPermissionRoles($route_id);
        return $result ? : false;
    }

    /**
     * Delete Rule
     *
     * @param array $attributes
     *
     * @return object
     */
    public function addRole($attributes = [], $role_id = [])
    {
        $result = RoleModel::addRole($attributes, $role_id);

        return $result ? : false;
    }

    /**
     * Get All Roles List
     *
     * @param void
     *
     * @return object roles
     *
     * @since 0.1
     */
    public function getRoleLists()
    {
        return RoleModel::getRoleLists();
    }

    /**
     * Get All Roles for a logged in user
     *
     * @param void
     *
     * @return mixed Roles | null
     *
     * @since 0.1
     */
    public function getRolesOld($userObject)
    {
        return $userObject->roles()->get();
    }
    
        /* Get All Roles
     * 
     * @param void()
     * 
     * @return object roles
     * 
     * @since 0.1
     */

    public function getRoles()
    {
        return RoleModel::getAllRoles();
    }

    /**
     * Get a user role by id
     *
     * @param integer $role_id
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getRoleByID($role_id)
    {
        $result = RoleModel::where('id', $role_id)->first();

        return $result ? : false;
    }

    /**
     * Whether a user has a role or not
     *
     * @param  mixed $role
     * @return boolean
     *
     * @since 0.1
     */
    public function hasRole($role)
    {
        $authUser = $this->getAuthUserData();
        return ($authUser !== false) ? $authUser->hasRole($role) : false;
    }

    /**
     * Get All Routes from Permission
     *
     * @param void
     *
     * @return Permission
     *
     * @since 0.1
     */
    public function getRoute()
    {
        return PermissionModel::getRoute();
    }

    /**
     * Add Role
     *
     * @param array $attributes
     *
     * @return object
     */
    public function addRoute($attributes = [], $route_id = [])
    {
        $result = PermissionModel::addRoute($attributes, $route_id);

        return $result ? : false;
    }

    /**
     * Get a route by id
     *
     * @param integer $route_id
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getRouteByID($route_id)
    {
        $result = PermissionModel::where('id', $route_id)->first();

        return $result ? : false;
    }

    /**
     * Delete route
     *
     * @param type $route_id
     *
     * @return boolean
     */
    public function deleteRoute($route_id)
    {
        $result = PermissionModel::where('id', $route_id)->delete();
        return $result ? : false;
    }

    /**
     * Check role assign to any user
     *
     * @param integer $role_id
     *
     * @return integer
     */
    public function checkRoleAssigntoUser($role_id)
    {
        $result = RoleModel::checkRoleAssigntoUser($role_id);

        return $result ? : 0;
    }

    /**
     * Delete Role
     *
     * @param integer $role_id
     *
     * @return boolean
     */
    public function deleteRole($role_id)
    {
        $role = RoleModel::where('id', $role_id)->delete();
    }

    /**
     * Get User Last Application
     *
     * @param integer $user_id User ID
     */
    public function getLastApplication($user_id)
    {
        return UserModel::getLastApplication((int) $user_id);
    }

    /**
     * save user detail
     *
     * @param  object $userObject
     * @param  array  $arrUserDetail
     * @return type
     */
    public function saveUserDetail($userObject, $arrUserDetail)
    {
        return $userObject->addUserOtherDetail($userObject, $arrUserDetail);
    }

    /**
     * Update user detail.
     *
     * @param  object $userObject
     * @param  array  $arrUserDetail
     * @return type
     */
    public function updateUserOtherDetail($userObject, $arrUserDetail)
    {
        return $userObject->updateUserOtherDetail($userObject, $arrUserDetail);
    }

    /**
     * Save and update Need Help Information method.
     *
     * @param array $attributes Help Data
     * return array
     */
    public function saveTicketInfo($attributes = [])
    {
        //Check Data is Array
        if (!is_array($attributes)) {
            throw new InvalidDataTypeExceptions('Please send an array');
        }

        //Check Data is not blank
        if (empty($attributes)) {
            throw new BlankDataExceptions('No Data Found');
        }

        return UserTicket::create($attributes);
    }

    /**
     * Get Ticket Information
     *
     * @param  int $ticket_id
     * @return array
     */
    public function getTicketInfoByZendId($ticket_id)
    {
        return UserTicket::getTicketInfoByZendId($ticket_id);
    }

    /**
     * Get Ticket Information
     *
     * @param  int $ticket_id
     * @return array
     */
    public function getTicketInfoById($ticket_id)
    {
        return UserTicket::getTicketInfoById($ticket_id);
    }

    /**
     * Get users Tickets
     *
     * @param  int $user_id
     * @return array
     */
    public function getTicketsIdByUserId($user_id)
    {
        return UserTicket::getTicketsIdByUserId($user_id);
    }

    /**
     * Save Ticket Log Information method.
     *
     * @param array $attributes Help Data
     * @param int   $help_id    Application Id return array return array
     * return array
     */
    public function saveTicketLogInfo($attributes = [])
    {
        //Check Data is Array
        if (!is_array($attributes)) {
            throw new InvalidDataTypeExceptions('Please send an array');
        }

        //Check Data is not blank
        if (empty($attributes)) {
            throw new BlankDataExceptions('No Data Found');
        }

        return UserTicketLog::create($attributes);
    }

    /**
     * Save User Password
     *
     * @param  array $userinfo
     * @return mixed
     */
    public function savePassword($userinfo)
    {
        return UserLastPassword::savePassword($userinfo);
    }

    /**
     * Get UserID by Email and Type
     *
     * @param  string  $email
     * @param  integer $usertype
     * @return integer | boolean
     */
    public function getUserIdByEmail($emailOrEmplID, $usertype, $user_id = null)
    {
        return UserModel::getUserIdByEmail($emailOrEmplID, $usertype, $user_id);
    }

    /**
     * Get All User Saved Passwords
     *
     * @param  integer $user_id
     * @return mixed
     */
    public function getAllPasswordForUser($user_id)
    {
        return UserLastPassword::getAllPasswordForUser((int) $user_id);
    }

    /**
     * Get Last Record for a User
     *
     * @param  type $user_id
     * @return mixed
     */
    public function getLastRecord($user_id)
    {
        return UserLastPassword::getLastRecord((int) $user_id);
    }

    /**
     *
     * @param integer $inactive_days
     * @param integer $usertype
     * @return type
     */
    public function getBackendInactiveUsers($inactive_days)
    {
        return UserModel::getBackendInactiveUsers($inactive_days);
    }

    /**
     * Get Permissions by $role_id
     *
     * @param integer $role_id
     *
     * @return arrray
     */
    public function getPermissionByRoleID($role_id)
    {
        return PermissionRole::getPermissionByRoleID((int) $role_id);
    }

    /**
     * Give Role details
     *
     * @param int $role_id
     *
     * @return object
     */
    public function getRole($role_id)
    {

        $role = RoleModel::getRole($role_id);

        return $role ? : false;
    }



    /**
     * Give Role details
     *
     * @param int $role_id
     *
     * @return object
     */
    public function getPermissionByID($permission_id)
    {
        $role = PermissionModel::getPermissionByID($permission_id);
        return $role ? : false;
    }

    /**
     * Give permission to role
     *
     * @param array $attributes
     *
     * @return object
     */
    public function getBackendRole()
    {

        $role = RoleModel::getBackendRole();

        return $role ? : false;
    }


    /**
     * Get all backend user data list
     *
     * @return array User List
     */
    public function getListBackendUsers()
    {
        return UserModel::getListBackendUsers();
    }

    /**
     * check username exist or not
     *
     * @param  string $username
     * @return boolean
     */
    public function checkUsernameExistance($username)
    {
        return UserModel::checkUsernameExistance($username);
    }

    /**
     * check Email exist or not
     *
     * @param  string $email
     * @return boolean
     */
    public function checkEmailExistance($email, $old_email)
    {
        return UserModel::checkEmailExistance($email, $old_email);
    }

    /**
     * Save User Temp Password
     *
     * @param  array $userInfoTemp
     * @return mixed
     */
    public function saveUserTempPassword($userInfoTemp, $user_id)
    {
        return UserTempPassword::saveUserTempPassword($userInfoTemp, $user_id);
    }

    /**
     * Check if token is valid or not against email id
     *
     * @param  string $email
     * @param  string $token
     * @return integer
     */
    public function checkTokenValidForUser($email, $token)
    {
        return PasswordReset::checkTokenValidForUser($email, $token);
    }

    /**
     * Check token expiration on page open
     *
     * @param type $token
     */
    public function checkTokenExiration($token)
    {
        $tokenData = Helpers::getPasswordResetTokenInfo($token);
        if (!empty($tokenData['opened_at'])) {
            $expirationTime = strtotime($tokenData['opened_at']) + config('auth.password.page_expire')*60;
            if ($expirationTime < time()) {
                Helpers::deletePasswordResetToken($token);
            }
        }
    }


     /* Get User Previous Log
     *
     * @param int $user_id
     * @param time $created_time
     * @param array $user
     */
    public function getuserPreviousLog($user_id, $created_time)
    {
        return UserLog::getuserPreviousLog($user_id, $created_time);
    }

    /**
     * Get Last Lead number
     *
     * @param $year
     */
    public function getLastLeadNo($year)
    {
        return UserModel::getLastLeadNo($year);
    }


    /**
     * Get Checklist Roles in hierarchy order
     *
     * @return array
     */
    public function getChecklistRoles($checklist_role_level=null)
    {
        return RoleModel::getChecklistRoles($checklist_role_level);
    }


    /**
     * Get Checklist Roles in hierarchy order
     *
     * @return array
     */
    public function getChecklistRolesLevel()
    {
        return ChecklistRolesLevel::getChecklistRolesLevel();
    }

    /**
     * Get reporting manager
     *
     * @param integer $role_id
     * @return array
     */
    public function getReportingManager($role_id)
    {
        return UserModel::getReportingManager((int) $role_id);
    }

    /**
     * Get reporting manager hub and region
     *
     * @param integer $role_id
     * @return array
     */
    public function getReportingManagerHubAndRegion($user_id)
    {
        return UserDetail::getReportingManagerHubAndRegion((int) $user_id);
    }


   /**
    * Get co listing for application assignment
    *
    * @param integer $role_id
    * @param integer $region_type
    *
    * @return type
    */
    public function getCOSupervisors($role_id, $region_type)
    {
        return UserModel::getCOSupervisors((int) $role_id, $region_type);
    }

    /**
     * Get bb w.r.t. bb supervisor
     *
     * @param integer $role_id
     * @param integer $reporting_manager_id
     *
     * @return array
     */
    public function getSubordinatesForSupervisior($role_id, $reporting_manager_id)
    {
        return UserModel::getSubordinatesForSupervisior((int) $role_id, (int) $reporting_manager_id);
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
    public function countLeads($userRole, $fromDate, $toDate)
    {
        $result = UserModel::countLeads($userRole, $fromDate, $toDate);
        return $result;
    }


    /**
     * Get all backend user by role id
     *
     * @return array User List
     */
    public function getUsersByRoleId($role_id)
    {
        return UserModel::getUsersByRoleId($role_id);
    }


    /**
     * Get user's ids w.r.t role
     *
     * @param integer $role_id
     *
     * @return array
     */
    public function getUserListByParentRoleId($role_id)
    {
        return UserModel::getUserListByParentRoleId($role_id);
    }


    /**
     * Get Users By Hub Id
     *
     * @param int $hub_id
     * @return mixed
     */
    public static function getHubUsers($hub_id)
    {
        return UserModel::getHubUsers($hub_id);
    }
    /**
     * Get Users By Hub Id
     *
     * @param int $role_id
     * @return array
     */
    public static function getAllUserByRole($role_id)
    {
        return UserModel::getAllUserByRole((int) $role_id);
    }


    /**
     * Get Checklist Roles Level By Role Id
     * @return mixed
     */
    public function getChecklistRolesLevelByRoleId($role_id)
    {
        return ChecklistRolesLevel::getChecklistRolesLevelByRoleId($role_id);
    }

    /**
     * Get Checklist Roles Level By Role Id
     * @return mixed
     */
    public function getAllChecklist()
    {
        return Checklist::getAllChecklist();
    }

    /**
     * Save checklist role
     * @return mixed
     */
    public function saveChecklistRoles($role_id, $arrChecklistRoles)
    {
        return ChecklistRole::saveChecklistRoles($role_id, $arrChecklistRoles);
    }

    /**
     * Delete checklist by role id.
     *
     * @param int $role_id
     * @return array
     */
    public function deleteChecklistRolesById($role_id)
    {
        return ChecklistRole::deleteChecklistRolesById($role_id);
    }

    /**
     * Get Checklist Roles Level By Role Id
     * @return mixed
     */
    public function getChecklistRolesById($role_id)
    {
        return ChecklistRole::getChecklistRolesById($role_id);
    }

    /**
     * Save checklist logs
     * @return mixed
     */
    public function saveChecklistRolesLog($role_id, $arrChecklistRoles)
    {
        return ChecklistRoleLog::saveChecklistRolesLog($role_id, $arrChecklistRoles);
    }

    /**
     * Get Checklist Roles Level By Role Id
     * @return mixed
     */
    public function getChecklistRolesLevelId()
    {
        return ChecklistRolesLevel::getChecklistRolesLevelId();
    }

    /**
     * Get all zendesk Tickets
     *
     * @return array
     */
    public function getAllTickets()
    {
        return UserTicket::getAllTickets();
    }
    
    /**
     * Get a user details by email and password
     *
     * @param string $email and @password string
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function checkUserDetails($email, $password = "")
    {
        
        $result = UserModel::where('email', $email)->first();
        $status = false;
        if (isset($result->password) && \Illuminate\Support\Facades\Hash::check($password, $result->password)) {
             $status =  $result;
        } else {
            $status = false;
        }
          return $status;
    }
    
        /**
     * Get a user details by email and password
     *
     * @param string $email and @password string
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function checkBackendUserDetails($email, $password = "")
    {
        $result = UserModel::where('email', $email)->orWhere('emp_id', $email)->where('user_type', 1)->first();
        $status = false;
        if (isset($result->password) && \Illuminate\Support\Facades\Hash::check($password, $result->password)) {
             $status =  $result;
        } else {
            $status = false;
        }
          return $status;
    }
    
     /**
     * Check whether a user is a front-end user or not
     *
     * @param  User $userObject
     * @return boolean
     */
    public function isFrontendUser($userObject = null)
    {
        $user = is_null($userObject) ? $this->getAuthUserData() : $userObject;

        $roles = $user->roles()->frontLoginAllowed()->get();

        return count($roles) > 0 ? true : false;
    }
    
    /**
     * Get a user details by email 
     *
     * @param string $email 
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function checkEmailExist($email)
    {

        $result = UserModel::where('email', $email)->first();
        return $result;
    }
    
     /**
     * Get only application user details by user id
     *
     * @param integer $userId
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getAppUserByEmail($userId)
    {
         $result = UserModel::where('id', $userId)->where('user_level_id',1)->first();
         return $result ? : false;
    }
    
     /**
     * Get OTP authentication by email ID
     *
     * @param integer $emailID
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function userOTPAuthentication($emailId, $location = null, $password=null)
    {
        if($location) {
            
            $result = UserModel::where('email', $emailId)->where('is_otp_authenticate',0)->first();
                if(isset($result) && !empty($result->password)) {
                      return Helpers::check($password, $result->password);
                } else {
                    return false;
                }
        } else {
            $result = UserModel::where('email', $emailId)->first();
            return $result ? : false;
        }
         
    }

    /**
     * Check whether a user is a back-end user or not
     *
     * @param  User $userObject
     * @return boolean
     */
    public function isBackendUser($userObject = null)
    {
        $user = is_null($userObject) ? $this->getAuthUserData() : $userObject;
        if ($user) {
            $roles = $user->roles()->adminLoginAllowed()->get();
            return count($roles) > 0 ? true : false;
        }
    }
    
    /**
     * Get Login log for a user.
     *
     * @param int $user_id
     * @param int $count
     * @param string $order
     * @return mixed \App\B2c\Repositories\Models\ActivityLog|boolean
     */
    public function getLoginLog($user_id, $count = 1, $order = 'desc')
    {
        return ActivityLog::getLoginLog($user_id, $count, $order);
    }

/**
     * check Email and pass exist or not
     *
     * @param  string $email
     * @return boolean
     */
    public function checkEmailAndPassExistance($email, $pass)
    {
        return UserModel::checkEmailExistance($email, $pass);
    }
    
        /**
     * Create once token
     *
     * @return string
     */
    public function createTokenOnce()
    {
        $rand = mt_rand(10000, mt_getrandmax());
        return hash_hmac('sha256', env('APP_KEY'), $rand);
    }

    /**
     * Reset token_once to null
     *
     * @param int $userId
     */
    public function removeTokenOnce($userId)
    {
        $this->updateUser((int) $userId, ['token_once' => null]);
    }

    /**
     * Get user object by token
     *
     * @param  string $tokenOnce
     * @return mixed
     */
    public function getUserByTokenOnce($tokenOnce)
    {
        $data = UserModel::where('token_once', $tokenOnce)->first();

        if ($data) {
            $this->removeTokenOnce($data->id);
            return $data;
        } else {
            return false;
        }
    }
    
    /**
     * Check token expiration on page open
     *
     * @param type $token
     */
    public function checkTokenExpiration($token)
    {
        $tokenData = Helpers::getPasswordResetTokenInfo($token);
        if (!empty($tokenData['opened_at'])) {
            $expirationTime = strtotime($tokenData['opened_at']) + config('auth.password.page_expire') * 60;
            if ($expirationTime < time()) {
                Helpers::deletePasswordResetToken($token);
            }
        }
    }
    
     /**
     * Get OTP authentication by user ID
     *
     * @param integer $userlID
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function checkAuthenticateUser($userId)
    {
             $result = UserModel::where('id', $userId)->where('is_otp_authenticate',1)->first();
             return $result ? : false;
    }

    /**
     * Delete route
     *
     * @param type $route_id
     *
     * @return boolean
     */
    public function deleteRouteFromPermissionRole($route_id)
    {
        $result = PermissionRole::where('permission_id', $route_id)->delete();
    }
     
    /**
     * Get a user role by id
     *
     * @param integer $user_id
     *
     * @return $result
     *
     * @since 0.1
     */
    public function getUserRolesById($user_id)
    {
        $result = RoleModel::getUserRolesById($user_id);
        return $result ? : false;
    }
    
    /**
     * Get User detail
     * 
     * @param array $whereCls
     * @param array $selectArr
     * @return mixed
     */
    public function getUserData($whereCls = [], $selectArr = [])
    {
        return UserModel::getUserData($whereCls, $selectArr);
    }
    
    /**
     * Get User detail
     * 
     * @param array $whereCls
     * @param array $selectArr
     * @return mixed
     */
    public function getBackendUserList($attributes = [], $select = [])
    {
        return UserModel::getBackendUserList($attributes, $select);
    }
    
    /**
     * Get backend User detail
     * 
     * @param array $whereCls
     * @param array $selectArr
     * @return mixed
     */
    public function getAllBackendUserData($attributes = [], $select = [])
    {
        return UserModel::getAllBackendUserData($attributes, $select);
    }
    
    /**
     * Get user email by 
     *
     * @param string $username
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getUserDetails($whereArr = [] , $select = [])
    {
        return UserModel::getUserDetails($whereArr, $select);
    }
    
    /**
     * check Employee Id exist or not
     *
     * @param  string $email
     * @return boolean
     */
    public function checkEmpIdExit($emp_id)
    {
        return UserModel::checkEmpIdExit($emp_id);
    }
    
    /* Get All Active Prime Rate
     * 
     * @param void()
     * 
     * @return object roles
     * 
     * @since 0.1
     */

    public function getPrimeRate()
    {
        return PrimeRateModel::getAllPrimeRates();
    }
    
    /**
     * Add Prime Rate
     *
     * @param array $attributes
     *
     * @return object
     */
    public function addPrimeRate($attributes = [])
    {
        return PrimeRateModel::addPrimeRate($attributes);
    }
    
    /**
     * 
     */
    public function updatePrimeStatus($primeRateId, $dataArr){
        return PrimeRateModel::updatePrimeStatus($primeRateId, $dataArr);
    }
    
    /**
     * get active primary interest rate
     * 
     */
    public function getActivePrimeRate(){
        return PrimeRateModel::getActivePrimeRate();
    }
    
    /**
     * get user bank details
     * 
     */
    public function getUserBankDetails($user_id, $app_id){
        return UserModel::getUserBankDetails((int) $user_id, $app_id);
    }
    
    /* Get All Exchange Rate
     * 
     * @param void()
     * 
     * @return object roles
     * 
     * @since 0.1
     */

    public function getExchangeRate()
    {
        return ExchangeRateModel::getAllExchangeRates();
    }
    
    /**
     * Add Exchange Rate
     *
     * @param array $attributes
     *
     * @return object
     */
    public function addExchangeRate($attributes = [])
    {
        return ExchangeRateModel::addExchangeRate($attributes);
    }
    
    /**
     * 
     */
    public function updateExchangeStatus($exchangeRateId, $dataArr){
        return ExchangeRateModel::updateExchangeStatus($exchangeRateId, $dataArr);
    }
    
    /**
     * get active Exchange rate
     * 
     */
    public function getActiveExchangeRate(){
        return ExchangeRateModel::getActiveExchangeRate();
    }
    
    /**
     * Get customer listing
     * 
     * @return mixed
     */
    public function getCustomerList()
    {
       return UserModel::getCustomerListing();
    }
    
     /**
     * Save primary Owner Business Information
     *
     * @param mixed $id
     * @param array $columns
     * @since 0.1
     */
    public function savePrimaryOwnerInfo( $user_id , $owner_data )
    {
        return UserModel::updateUser( (int)$user_id , $owner_data );
    }
    
      /**
     * Search for all app creator
     *
     * @param mixed $id
     * @param array $columns
     * @since 0.1
     */
     public function getAllusersWithUserLevelID($attributes = [], $select = [])
    {
        return UserModel::getAllusersWithUserLevelID($attributes, $select);
    }
    
     /**
     * Add Auto Decision
     *
     * @param array $attributes
     *
     * @return object
     */
    public function saveAutoDecision($attributes = [])
    {
        return DeclineDecision::saveAutoDecision($attributes);
    }
    
    /**
     * get auto decision data
     * 
     */
    public function getAutoDecision(){
        return DeclineDecision::getAutoDecision();
    }
    
    /**
     * Get business name
     * 
     * @param array $whereCls
     * @param array $selectArr
     * @return mixed
     */
    public function checkBusinessName($businessName)
    {
        return UserModel::checkBusinessName($businessName);
    }
    
    /**
     * Get Cancel Reason
     * @param void
     * @return object roles
     * @since 0.1
     */
    public function getCancelReason()
    {
        return CancelAppReasonModel::getAllCancelReason();
    }
    
     /**
    /* Get All Promo Codes
     * 
     * @param void()
     * 
     * @return object roles
     * 
     * @since 0.1
     */

    public function getPromoCode()
    {
        return PromoCodeModel::getAllPromoCodes();
    }
    
    public function addCancelReason($attributes = [])
    {
        return CancelAppReasonModel::addCancelReason($attributes);
    }
    
     /**
     * Get Cancel Reason  by id
     *
     * @param integer $cancel_reason_id
     *
     * @return mixed
     */
    public function getCancelReasonByID($cancel_reason_id)
    {
        return CancelAppReasonModel::getCancelReason((int) $cancel_reason_id);
    }
    
    /**
    * update Cancel Reason  by status
    *
    * @param integer $cancel_reason_id
    *
    * @return mixed
    */
    public function updateCancelReason($cancel_reason_id,$column)
    {
        return CancelAppReasonModel::updateCancelReason((int) $cancel_reason_id,$column);
    }
    
    public function updatePrimeRate($primeRateData, $id)
    {
        return PrimeRateModel::updatePrimeRate($primeRateData, $id);
    }
    
    /**
     * Add Prime Rate Log
     *
     * @param array $attributes
     *
     * @return object
     */
    public function addCancelReasonLog($attributes = [])
    {
        return CancelReasonAppLog::addCancelReasonLog($attributes);
    }
    
    public function addPromoCode($attributes = [], $promo_code_id = [])
    {
        $result = PromoCodeModel::addPromoCode($attributes, $promo_code_id);
        
        return $result ? : false;
    }
    
    /**
     * Get a promo code by id
     *
     * @param integer $promo_code_id
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getPromoCodeByID($promo_code_id)
    {
        $result = PromoCodeModel::where('id', $promo_code_id)->first();
        return $result ? : false;
    }
    
    /**
     * Add Promo Code Log
     *
     * @param array $attributes
     *
     * @return object
     */
    public function addPromoCodeLog($attributes = [])
    {
        $result = PromoCodeLogModel::addPromoCodeLog($attributes);
        
        return $result ? : false;
    }
    
    /**
     * Get a promo code by id
     *
     * @param integer $promo_code_id
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getUserPromoCode($app_id, $app_user_id)
    {
        $result = UserPromoCodeModel::where('app_id', $app_id)->where('app_user_id',$app_user_id)->first();
        return $result ? : false;
    }
    
    /**
     * Update Switch Control
     *
     * @param array $attributes,$switchControlData
     *
     * @return object
     */
    public function updateSwitchControl($switchControlData, $attributes = [])
    {
        return SwitchControlModel::updateSwitchControl($switchControlData, $attributes);
    }
    
    /**
     * Get a switch control by module name
     *
     * @param integer $module_name
     *
     * @return boolean
     *
     * @since 0.1
     */
    public function getSwitchControlByModule($module_name)
    {
        $result = SwitchControlModel::where('module_name', $module_name)->first();
        return $result ? $result : false;
    }
    
    /**
     * Save Switch Control Log
     *
     * @param array $switchControlLogData
     *
     * @return object
     */
    public function createSwitchControlLog($switchControlLogData)
    {
        return SwitchControlLogModel::createSwitchControlLog($switchControlLogData);
    }
    
    public function addPrimeRateLog($attributes = [])
    {
        return PrimeRateLogModel::addPrimeRateLog($attributes);
    }
    
    /**
     * Search for all app creator
     *
     * @param mixed $id
     * @param array $columns
     * @since 0.1
     */
     public function getAllUsersByLevelId($attributes = [], $select = [], $userLavelId)
    {
        return UserModel::getAllUsersByLevelId($attributes, $select, $userLavelId);
    }
}