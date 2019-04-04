<?php
namespace App\Models\Repositories;

interface TmpRepositoryInterface extends BaseRepositoryInterface
{
    // Class template

    /**
     * Get user's entities.
     *
     * @param $userId
     * @return mixed
     */
    public function getByUserId($userId);

    /**
     * Store by given data.
     *
     * @param $data
     * @return mixed
     */
//    public function store($data);

    /**
     * Delete the specified entity.
     *
     * @param $id
     * @return bool
     */
//    public function delete($id);
}
