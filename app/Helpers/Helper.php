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
    
    /**
     * All states
     * 
     * @return type
     * 
     */
    public static  function calculateMoney($amt)
    {
        $getConfigNexamt = config('common.nexzoa_per');
        $getConfigGateamt = config('common.nexzoa_Gateway_fee');
        $nexzaAmt = ($amt * $getConfigNexamt)  / 100;
        $gateAmt = ($amt * $getConfigGateamt)  / 100;
        $customerAmt = $amt - $nexzaAmt -  $gateAmt;
        $data = ['nexza_amt'=>$nexzaAmt,'gatway_amt'=>$getConfigGateamt,'customer_total'=>$customerAmt];
        return $data;
    }
    
}