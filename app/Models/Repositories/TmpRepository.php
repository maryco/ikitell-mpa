<?php
namespace App\Models\Repositories;

class TmpRepository implements TmpRepositoryInterface
{
    public function makeModel($bindData = null)
    {
//        $model = new Rule();
//        if ($bindData) {
//            $model->mergeData($bindData);
//        }
//
//        return $model;
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

    /**
     * @param $userId
     * @return mixed|void
     */
    public function getByUserId($userId)
    {
        // TODO: Implement getByUserId() method.
    }
}

