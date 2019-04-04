<?php
namespace App\Models\Repositories;

use App\Models\Entities\ConcernMessage;

interface MessageRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Return the specified message template as a model.
     *
     * @param $id
     * @param $userId
     * @return ConcernMessage|null
     */
    public function findById($id, $userId);

    /**
     * Get all template via the config.
     *
     * @return array (ArrayObject)
     */
    public function getTemplate();
}
