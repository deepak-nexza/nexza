<?php

namespace App\B2c\Repositories\Entities\Master;

use App\B2c\Repositories\Entities\Master\MasterRepository;
use App\B2c\Repositories\Contracts\BusinessEntityInterface;
use App\B2c\Repositories\Models\Master\BusinessEntity;

/**
 * Business enitity repository
 */
class BusinessEnitityRepository extends MasterRepository implements BusinessEntityInterface
{
    /**
     * Class constructor
     *
     * @param void
     * @return void
     * @since 0.1
     */


    public function __construct(BusinessEntity  $model)
    {
        $this->entity = $model;
    }
}
