<?php 
namespace App\Helpers; 
use App;
class Helper 
{ 
    /***
     * All Countries
     * 
     */
    public static  function countryList()
    {
         return App\Repositories\Models\Master\Country::getallCountryList();
    }
    
    /**
     * All states
     * 
     * @return type
     * 
     */
    public static  function stateList()
    {
         return App\Repositories\Models\Master\State::getAllStates();
    }
    
    /**
     * All states
     * 
     * @return type
     * 
     */
    public static  function getAllEvent()
    {
         return App\Repositories\Models\Master\Eventype::getAllEvent();
    }
    
    /**
     * All states
     * 
     * @return type
     * 
     */
    public static  function formatEditorData($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
}