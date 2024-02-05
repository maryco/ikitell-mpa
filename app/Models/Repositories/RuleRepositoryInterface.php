<?php
namespace App\Models\Repositories;

use App\Models\Entities\Rule;
use Illuminate\Database\Eloquent\Collection;

interface RuleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Return the specified rule.
     *
     * @param int $userId
     * @param int $ruleId
     * @return Rule|null
     */
    public function findByUserId(int $userId, int $ruleId): ?Rule;

    /**
     * Get user's rules.
     *
     * @param int $userId
     * @param bool $withDevice
     * @return Collection
     */
    public function getByUserId(int $userId, bool $withDevice = true): Collection;

    /**
     * Store by given data.
     *
     * @param $data
     * @return Rule
     */
    public function store($data): Rule;

    /**
     * Delete the specified rule.
     * NOTE: Can not delete if has related device.
     *
     * @param int $ruleId
     * @param int $userId
     * @return bool
     */
    public function delete(int $ruleId, int $userId): bool;
}
