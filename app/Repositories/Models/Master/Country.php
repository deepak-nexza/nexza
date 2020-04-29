<?php namespace App\Repositories\Models\Master;

use App\Repositories\Factory\Models\BaseModel;
use App\B2c\Repositories\Entities\User\Exceptions\BlankDataExceptions;
use App\B2c\Repositories\Entities\User\Exceptions\InvalidDataTypeExceptions;

class Country extends BaseModel
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'nex_mst_countries';

    /**
     * Custom primary key is set for the table
     *
     * @var integer
     */
    protected $primaryKey = 'countries_id';

    /**
     * Maintain created_at and updated_at automatically
     *
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Maintain created_by and updated_by automatically
     *
     * @var boolean
     */
    public $userstamps = false;

        /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_name',
        'is_active',
        'countries_iso_code_2',
        'countries_iso_code_3'
    ];
    
    /**
     * One-to-many relation with State
     *
     * @return json
     *
     * @since 0.1
     */
    public function states()
    {
        return $this->hasMany('App\B2c\Repositories\Models\Master\State');
    }

    /**
     * Get all state
     *
     * @param integer $country_id Country id
     *
     * @return string state data as object
     *
     * @since 0.1
     *
     * @author Rajeev Sharma
     */
    public static function getAllStates($country_id)
    {
      
        /**
         * Check id is not an integer
         */
        if (!is_int($country_id)) {
            throw new InvalidDataTypeExceptions(trans('error_message.invalid_data_type'));
        }

        $list = self::find((int) $country_id);
        
        /**
         * Check Data is not blank
         */
        if (empty($list)) {
            throw new BlankDataExceptions(trans('error_message.no_data_found'));
        }

        $arrState = $list->states()->where('is_active', config('b2c_common.ACTIVE'))->orderBy('name', 'asc');
        $states = $arrState->lists("name", "id");
        return ($states ? : false);
    }
    
    /**
     * Get all state
     *
     * @param integer $country_id Country id
     *
     * @return string state data as object
     *
     * @since 0.1
     *
     * @author Rajeev Sharma
     */
    public static function getallCountryList($attributes = [], $select = [])
    {
        /**
         * Check $attributes is not array
         */
        if (!is_array($attributes)) {
            throw new InvalidDataTypeExceptions(trans('error_message.send_array'));
        }

        $result = self::select('*')->where('is_active', 1)->get();
        return ($result ?: false);
    }
}
