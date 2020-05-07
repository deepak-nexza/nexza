<?php

namespace Nexza\Html\Http\Middleware;

use Form;
use Closure;
use Exception;

class RequestProtector
{

    /**
     * Request object
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Hidden protector key
     *
     * @var string
     */
    protected $hkey;

    /**
     * Protector key
     *
     * @var string
     */
    protected $pkey;

    /**
     * Segment key
     *
     * @var string
     */
    protected $skey;

    /**
     * Timestamp key
     *
     * @var string
     */
    protected $tskey;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     * @param int                      $limit
     * @param int                      $time
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->request = $request;

        if ($request->ajax() === true || $request->ajax() === false) {
            $this->hkey = Form::hkey();
            $this->pkey = Form::pkey();
            $this->skey = Form::skey();
            $this->tskey = Form::tskey();
            if ($request->method() == 'GET') {
                $this->checkGetProtector();
            } elseif ($request->method() == 'POST') {
                $this->checkPostProtector();
            }
        }

        return $next($this->request);
    }

    /**
     * @todo protect get parameters in a request
     */
    protected function checkGetProtector()
    {
        //TO-DO
    }

    /**
     * POST sanity check
     */
    protected function checkPostProtector()
    {
        // If we have the hidden protector in POST request,
        // try to decrypt it. If it fails, we reject the call
        $encrypted = $this->request->input($this->hkey) ;
        if ((isset($encrypted) && $encrypted)) {
            try {
                $this->checkQueryString();

                $data = Form::clean($encrypted);

                // Valid the request
                $this->isValidRequest($data);

                // Everything looks good,
                // update the \Illuminate\Http\Request object with the decrypted values
                $this->updateRequest($data);
            } catch (Exception $ex) {                
                abort(400);
            }
        } else {
            $randToken = $this->request->input('__randToken') ;
             $tx = $this->request->input('__tx') ;
            if ((isset($randToken) && $randToken) && (empty($tx) || empty($encrypted))) {
                abort(400);
            }
        }
    }

    /**
     * Check whether post protector exists in the query string
     */
    protected function checkQueryString()
    {
        // If we find form hidden protector is passed through URL,
        // we reject the call
        if ($this->request->query($this->hkey)) {
            abort(400);
        }
    }

    /**
     * Checks whether the request is valid
     *
     * @param mixed $data
     */
    protected function isValidRequest($data)
    {
        $segments = array_pull($data, $this->pkey . '.' . $this->skey, false);

        // Timestamp check
        if ((float) trim($this->request->input($this->tskey)) !== (float) trim($data[$this->tskey])) {
            abort(400);
        }
        
        // URL segments check
        if ($segments !== false) {
            foreach ($segments as $key => $value) {
                if ($this->request->segment($value) != $data[$this->pkey][$key]) {
                    abort(400);
                }
            }
        }

        // Form hidden value check
        foreach ($data[$this->pkey] as $key => $value) {
            if ($this->request->input($key) != $value) {
                abort(400);
            }
        }
    }

    /**
     * Update the \Illuminate\Http\Request with decoded value
     *
     * @param array $data
     */
    protected function updateRequest(array $data)
    {
        if (count($data) > 0) {
            // Remove the protector keys to keep the request size smaller
            $this->request->request->remove($this->tskey);
            $this->request->request->remove($this->hkey);
            $this->request->request->remove($this->pkey);
        }
    }
}
