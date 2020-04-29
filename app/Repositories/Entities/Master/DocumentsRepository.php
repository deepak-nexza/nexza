<?php

namespace App\B2c\Repositories\Entities\Master;

use App\B2c\Repositories\Entities\Master\MasterRepository;
use App\B2c\Repositories\Contracts\DocumentsInterface;
use App\B2c\Repositories\Models\Master\Document;



/**
 * Documents Repository
 */
class DocumentsRepository extends MasterRepository implements DocumentsInterface
{
    /**
     * Class constructor
     *
     * @param void
     * @return void
     * @since 0.1
     */


    public function __construct(Document  $model)
    {
        $this->entity = $model;
    }
}
