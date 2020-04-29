<?php

namespace App\B2c\Repositories\Entities\Master;

use App\B2c\Repositories\Entities\Master\MasterRepository;
use App\B2c\Repositories\Contracts\IndustryInterface;
use App\B2c\Repositories\Models\Master\Industry;


/**
 * Report repository class
 */
class IndustryRepository extends MasterRepository implements IndustryInterface {

    /**
     * Class constructor
     *
     * @param void
     * @return void
     * @since 0.1
     */


    public function __construct(Industry  $model)
    {
        $this->entity = $model;
    }

}
