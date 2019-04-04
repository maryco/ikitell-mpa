<?php

namespace App\Models\Repositories;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Create User and default Device and Rule
     *
     * @param $data
     * @return mixed
     */
    public function createUserDataSet($data);

    /**
     * Return the specified user.
     *
     * @param $userId
     * @return mixed
     */
    public function findById($userId);

    /**
     * Return user by the specified email.
     *
     * @param $email
     * @return mixed
     */
    public function findByEmail($email);

    /**
     * Update user's profile data.
     *
     * @param $data
     * @param $userId
     * @throws \Throwable
     * @return bool
     */
    public function updateProfile($data, $userId);

    /**
     * Delete the specified user.
     *
     * @param $userId
     * @return bool
     */
    public function delete($userId);
}
