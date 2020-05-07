<?php

namespace App\Repositories\Libraries;

use Exception;
use App\Repositories\Models\CmActivityLog;
use App\Repositories\Models\CmActivityMeta;

class CmLogger
{

    /**
     * Request object
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Request method
     *
     * @var string
     */
    protected $requestMethod;

    /**
     * Is AJAX request
     *
     * @var integer
     */
    protected $isAjaxRequest = 0;

    /**
     * Is a command line request
     *
     * @var integer
     */
    protected $isCliRequest = 0;

    /**
     * Controller name
     *
     * @var string
     */
    protected $controller;

    /**
     * Method name
     *
     * @var string
     */
    protected $method;

    /**
     * URI
     *
     * @var string
     */
    protected $uri;

    /**
     * Pattern of fields those values are to be masked while storing the requests
     *
     * @var array
     */
    protected $maskedFieldsPattern = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->maskedFieldsPattern = config('maskedfields.list');
    }

    /**
     * Set the curremt URI
     */
    protected function setUri()
    {
        $this->uri = $this->request->path();
    }

    /**
     * Replace password and other sensitive variables with *
     *
     * @param array $value
     * @return array
     */
    protected function filter($value)
    {
        if (count($value) <= 0) {
            return [];
        }

        $patterns = '/(' . implode('|', array_map('preg_quote', $this->maskedFieldsPattern)) . ')/iu';

        return array_map(function ($key, $val) use ($patterns) {
            if (preg_match($patterns, $key)) {
                return [$key => '*******'];
            } else {
                return [$key => $val];
            }
        }, array_keys($value), $value);
    }

    /**
     * Set controller name and method name
     */
    protected function getRouteContent()
    {
        $currentAction = $this->request->route()->getAction();
        list($controller, $method) = explode('@', $currentAction['controller']);
        $this->controller = $controller;
        $this->method = $method;
    }

    /**
     * Combine POST and GET requests and combines them in an array
     *
     * @return array
     */
    protected function collectRequests()
    {
        $all = $this->request->request->all(); // All parameters except files
        $query = $this->request->query(); // all GET parameters
        //$post = $this->request->request->all(); // for POST parameters

        $post = ['post' => $this->filter($all)];
        $get = ['get' => $this->filter($query)];

        return ($post + $get);
    }

    /**
     * Log the activity
     */
    protected function doLog()
    {
        try {
            $logger = new CmActivityLog();
            $user = $this->request->user();
            $logger->user_id = ($user) ? $user->id : null;
            $logger->session_id = $this->request->session()->getId();
            $logger->request_method = $this->requestMethod;
            $logger->is_ajax_request = $this->isAjaxRequest;
            $logger->is_cli_request = $this->isCliRequest;
            $logger->controller_name = $this->controller;
            $logger->controller_method = $this->method;
            $logger->uri = $this->uri;
            $logger->ip_address = $this->request->getClientIp();
            $logger->user_agent = $this->request->server('HTTP_USER_AGENT');

            $meta = new CmActivityMeta();
            $meta->request = serialize($this->collectRequests());

            $logger->save();
            $logger->meta()->save($meta);
        } catch (Exception $ex) {
            if (config('app.debug')) {
                throw $ex;
            }
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return boolean
     */
    public function handle($request)
    {
        $this->request = $request;
        $this->requestMethod = $request->method();
        $this->isAjaxRequest = (int) $request->ajax();
        $this->isCliRequest = (int) app()->runningInConsole();
        $this->getRouteContent();
        $this->setUri();

        $this->doLog();

        return true;
    }
}
