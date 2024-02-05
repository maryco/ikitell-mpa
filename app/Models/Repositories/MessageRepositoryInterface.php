<?php
namespace App\Models\Repositories;

use App\Models\Entities\ConcernMessage;
use ArrayObject;

interface MessageRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Return the specified message template as a model.
     *
     * @param int $id
     * @param int $userId
     * @return ConcernMessage|null
     */
    public function findById(int $id, int $userId): ?ConcernMessage;

    /**
     * Get all template via the config.
     *
     * @return array<int, ArrayObject>
     */
    public function getTemplate(): array;
}
