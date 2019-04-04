<?php
namespace App\Models\Repositories;

interface RuleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Return the specified rule.
     *
     * @param $userId
     * @param $ruleId
     * @return mixed
     */
    public function findByUserId($userId, $ruleId);

    /**
     * Get user's rules.
     *
     * @param $userId
     * @param $withDevice
     * @return mixed
     */
    public function getByUserId($userId, $withDevice = true);

    /**
     * Store by given data.
     *
     * @param $data
     * @return mixed
     */
    public function store($data);

    /**
     * Delete the specified rule.
     * NOTE: Can not delete if has related device.
     *
     * @param $ruleId
     * @param $userId
     * @return bool
     */
    public function delete($ruleId, $userId);
}
