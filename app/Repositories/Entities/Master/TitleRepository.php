<?php

namespace App\B2c\Repositories\Entities\Master;

use App\B2c\Repositories\Entities\Master\MasterRepository;
use App\B2c\Repositories\Contracts\TitleInterface;
use App\B2c\Repositories\Models\Master\Title;



/**
 * Title Repository
 */
class TitleRepository extends MasterRepository implements TitleInterface
{
    /**
     * Class constructor
     *
     * @param void
     * @return void
     * @since 0.1
     */


    public function __construct(Title  $model)
    {
        $this->entity = $model;
    }
}
