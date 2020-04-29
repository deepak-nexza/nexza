<?php

namespace App\B2c\Repositories\Entities\Master;

use App\B2c\Repositories\Entities\Master\MasterRepository;
use App\B2c\Repositories\Contracts\PurposeOfFinanceInterface;
use App\B2c\Repositories\Models\Master\FinancePurpose;


/**
 * Report repository class
 */
class PurposeOfFinancingRepository extends MasterRepository implements PurposeOfFinanceInterface {

    /**
     * Class constructor
     *
     * @param void
     * @return void
     * @since 0.1
     */


    public function __construct(FinancePurpose  $model)
    {
        $this->entity = $model;
    }

}
