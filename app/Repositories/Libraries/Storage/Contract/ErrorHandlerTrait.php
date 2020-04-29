<?php

namespace App\B2c\Repositories\Libraries\Storage\Contract;

use Illuminate\Support\Facades\Log;

trait ErrorHandlerTrait
{

    /**
     * Exception that was occured during a process
     *
     * @var object
     */
    protected $exception;

    /**
     * Last error occured
     *
     * @var string
     */
    protected $lastError;

    /**
     * Get HTTP status code from exception
     *
     * @var integer
     */
    protected $statusCode;

    /**
     * Sets the status code returned from a request
     */
    protected function setStatusCode()
    {
        if (method_exists($this->exception, 'getStatusCode')) {
            $this->statusCode = $this->exception->getStatusCode();
        } else {
            $this->statusCode = $this->exception->getCode();
        }
    }

    /**
     * Set the last error
     */
    protected function setLastError()
    {
        $this->lastError = $this->exception->getMessage();
    }

    /**
     * Get the last error
     *
     * @return string
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Get the entire exception class
     *
     * @return object
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Get the status code
     *
     * @return integer
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set the last error occured and log it
     *
     * @param Exception $ex
     * @return boolean
     */
    protected function error($ex)
    {
        $this->exception = $ex;
        $this->setLastError();
        $this->setStatusCode();
        Log::alert($ex->getMessage());
        return false;
    }
}
