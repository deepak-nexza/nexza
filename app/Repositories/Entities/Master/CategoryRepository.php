<?php

namespace App\B2c\Repositories\Entities\Master;

use App\B2c\Repositories\Entities\Master\MasterRepository;
use App\B2c\Repositories\Contracts\CategoryInterface;
use App\B2c\Repositories\Models\Master\Category;



/**
 * Category sRepository
 */
class CategoryRepository extends MasterRepository implements CategoryInterface
{
    /**
     * Class constructor
     *
     * @param void
     * @return void
     * @since 0.1
     */


    public function __construct(Category $model)
    {
        $this->entity = $model;
    }
}
