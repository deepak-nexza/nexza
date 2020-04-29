<?php

namespace App\B2c\Repositories\Entities\Master;

use App\B2c\Repositories\Entities\Master\MasterRepository;
use App\B2c\Repositories\Contracts\LoanTypeInterface;
use App\B2c\Repositories\Models\Master\LoanType;



/**
 * LoanType Repository
 */
class LoanTypeRepository extends MasterRepository implements LoanTypeInterface
{
    /**
     * Class constructor
     *
     * @param void
     * @return void
     * @since 0.1
     */


    public function __construct(LoanType  $model)
    {
        $this->entity = $model;
    }
}
