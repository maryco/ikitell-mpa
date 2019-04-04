<?php

namespace App\Models\Repositories;

/**
 * Interface BaseRepositoryInterface
 * @package App\Models\Repositories
 */
interface BaseRepositoryInterface
{
    /**
     * Make a model.
     *
     * @param $bindData
     * @return mixed
     */
    public function makeModel($bindData = null);

    /**
     * Count.
     *
     * @return mixed
     */
    public function count();
}
