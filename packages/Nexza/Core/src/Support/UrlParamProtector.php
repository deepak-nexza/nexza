<?php

namespace Nexza\Core\Support;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;

class UrlParamProtector
{

        protected $non_enc_routes = [
            'event_detail',
            'event_desc',
        ];
     
    /**
     * Session key.
     *
     * @var string
     */
    protected $sessionKey = '__url_protector__';

    /**
     * Request class.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Values those needs to be merged in request object.
     *
     * @var array
     */
    protected $valuesToBeMerged;

    /**
     * Create and returns VALID RFC 4211 COMPLIANT
     * Universally Unique IDentifiers (UUID) version 4.
     *
     * @return string
     */
    protected function getNewGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(16384, 20479),
            mt_rand(32768, 49151),
            mt_rand(0, 65535),
            mt_rand(0, 65535),
            mt_rand(0, 65535)
        );
    }

    /**
     * Create the route key.
     *
     * @param string $routeName
     * @param array $parameters
     * @return string
     */
    protected function getRouteKey($routeName, array $parameters = [])
    {
        if (count($parameters) <= 0) {
            $paramToString = '';
        } else {
            $paramToString = implode('-', array_map(
                function ($k, $v) {
                    return $k.'-'.str_replace(['.', ' '], [''], $v);
                },
                array_keys($parameters),
                array_values($parameters)
            ));
        }

        $routeKey = 'route__'.$routeName.(empty($paramToString) ? '' : '-'.$paramToString);

        return $routeKey;
    }

    /**
     * Returns a GUID for a URL parameter.
     *
     * @param string $routeName
     * @param array $parameters
     * @return string
     */
    public function protect($routeName, array $parameters)
    {
        $routeKey = $this->getRouteKey($routeName, $parameters);
        if (Session::has($this->sessionKey.'.'.$routeKey) === false) {
            $guid = Str::lower($this->getNewGuid());            
            session([$this->sessionKey . '.' . $routeKey => [
                    'guid' => $guid,
                    'loggedin_user_id' => (Auth::guest() ? 0 : Auth::user()->id),
                    'params' => $parameters,
            ]]);
        } else {
            $guid = Session::get($this->sessionKey.'.'.$routeKey.'.guid');
        }

        return $guid;
    }

    /**
     * Check whether guid passed is a valid one or not.
     *
     * @param string $guid
     * @return boolean
     */
    protected function isValidGuid($guid)
    {
        foreach (session::get($this->sessionKey) as $key => $value) {
            if (! isset($value['guid'])) {
                list($innerKey, $val) = each($value);
            } else {
                $val = $value;
            }
            if ($val['guid'] === $guid) {
                $this->valuesToBeMerged = $val['params'];
                return true;
            }
        }

        return false;
    }

     /**
     * Check whether guid passed is a valid one or not.
     *
     * @param string $guid
     * @return boolean
     */
    public function isValidGuidForPost($guid)
    {
        foreach (Session::get($this->sessionKey) as $key => $value) {
            if ($value['guid'] === $guid && Auth::user()->id === $value['loggedin_user_id']) {
                $this->valuesToBeMerged = $value['params'];
                return true;
            }
        }

        return false;
    }

    /**
     * Merge the request with our revealed values.
     */
    protected function mergeRequest()
    {
        $this->request->merge($this->valuesToBeMerged);
    }

    /**
     * Check whether a "__buffer" is correct or not.
     *
     * @param \Illuminate\Http\Request $request
     * @return boolean
     */
    public function reveal(Request &$request)
    {
        $open_call = in_array($request->segment(1), $this->non_enc_routes);
        if (Config::get('common.url_encrypted', false) === true) {
            $this->request = $request;

            $guid = ($this->request->query('__buffer') ? : false);
            if ($guid === false) {
                return false;
            }
            
            if ($this->isValidGuid($guid) === false) {                
                App::abort(400);
            }

            $this->mergeRequest();
        }
    }
}
