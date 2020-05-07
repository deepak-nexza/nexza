<?php

namespace App\Libraries;

use Route;
use Config;

class Routes
{
    /**
     * Routes instance
     *
     * @var App\Libraries\Routes
     */
    protected static $instance;
    
    /**
     * All bakend routes
     *
     * @var array
     */
    protected $backendRoutes = [];
    
    /**
     * All front end routes
     *
     * @var array
     */
    protected $frontendRoutes = [];
    
    /**
     * All open routes
     *
     * @var array
     */
    protected $openRoutes = [];
    
    /**
     * Class constructor
     */
    protected function __construct()
    {
        //
    }
    
    /**
     * Signleton class instance creator
     *
     * @return App\Libraries\Routes
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Read all registered route and set those in individual groups
     *
     * @param  void
     * @return void
     * @since  1.0
     * @author Biz2Credit Dev Team <info@biz2credit.in>
     */
    public function setAllRoutes()
    {
        $routeCollection = Route::getRoutes();
        
        foreach ($routeCollection as $value) {
            switch ($value->domain()) {
                case config('b2cin.backend_uri'):
                    $this->backendRoutes[] = $value->getName();
                    break;
                case config('b2cin.frontend_uri'):
                    $this->frontendRoutes[] = $value->getName();
                    break;
                default:
                    $this->openRoutes[] = $value->getName();
                    // No need to break here
            }
        }
        
        Config::set(
            'b2cin.backend_routes',
            array_values(
                array_unique(
                    $this->backendRoutes
                )
            )
        );

        Config::set(
            'b2cin.frontend_routes',
            array_values(
                array_unique(
                    $this->frontendRoutes
                )
            )
        );
        
        Config::set(
            'b2cin.open_routes',
            array_values(
                array_unique(
                    $this->openRoutes
                )
            )
        );
    }
}
