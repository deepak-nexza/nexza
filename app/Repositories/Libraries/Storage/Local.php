<?php

namespace App\Repositories\Libraries\Storage;

use Exception;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Libraries\Storage\Factory;

class Local extends Factory
{

    /**
     * Set the storage driver
     *
     * @return \App\Repositories\Libraries\Storage\Local
     */
    protected function setStorage()
    {
        $this->store = Storage::disk('local');
    }

    /**
     * Prepare the file for download
     *
     * @param string $file
     * @return boolean
     */
    public function prepare($file)
    {
        try {
            if (!$this->store->has($file)) {
                return false;
            }

            return $this->store->getAdapter()->getPathPrefix().$file;
        } catch (Exception $ex) {
            return $this->error($ex);
        }
    }
}
