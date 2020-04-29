<?php

namespace App\B2c\Repositories\Factory\Models\Relations;

use Illuminate\Database\Eloquent\Relations\BelongsToMany as BaseBelongsToMany;

class BelongsToMany extends BaseBelongsToMany
{
    /**
     * The custom pivot table column for the created_at timestamp.
     *
     * @var string
     */
    protected $pivotCreatedBy;

    /**
     * The custom pivot table column for the updated_at timestamp.
     *
     * @var string
     */
    protected $pivotUpdatedBy;

    /**
     * Specify that the pivot table has creation and update userstamps.
     *
     * @param  mixed  $createdBy
     * @param  mixed  $updatedBy
     * @return \App\B2c\Repositories\Factory\Models\Relations\BelongsToMany
     */
    public function withUserstamps($createdBy = null, $updatedBy = null)
    {
        $this->pivotCreatedBy = $createdBy;
        $this->pivotUpdatedBy = $updatedBy;

        return $this->withPivot($this->createdBy(), $this->updatedBy());
    }

    /**
     * Get the name of the "created by" column.
     *
     * @return string
     */
    public function createdBy()
    {
        return $this->pivotCreatedBy ? : $this->parent->getCreatedByColumn();
    }

    /**
     * Get the name of the "updated by" column.
     *
     * @return string
     */
    public function updatedBy()
    {
        return $this->pivotUpdatedBy ? : $this->parent->getUpdatedByColumn();
    }

    /**
     * Get the related model's updated by column name.
     *
     * @return string
     */
    public function getRelatedFreshUpdate()
    {
        $columns = parent::getRelatedFreshUpdate();

        $columns[$this->related->getUpdatedByColumn()] = $this->related->getUserId();

        return $columns;
    }

    /**
     * Set the creation and update userstamps on an attach record.
     *
     * @param  array  $record
     * @param  bool   $exists
     * @return array
     */
    protected function setUserstampsOnAttach(array $record, $exists = false)
    {
        $userId = $this->parent->getUserId();

        if (!$exists && $this->hasPivotColumn($this->createdBy())) {
            $record[$this->createdBy()] = $userId;
        }

        if ($this->hasPivotColumn($this->updatedBy())) {
            $record[$this->updatedBy()] = $userId;
        }

        return $record;
    }

    /**
     * Create a new pivot attachment record.
     *
     * @param  int   $id
     * @param  bool  $timed
     * @return array
     */
    protected function createAttachRecord($id, $timed)
    {
        $record = parent::createAttachRecord($id, $timed);

        return $this->setUserstampsOnAttach($record);
    }
}
