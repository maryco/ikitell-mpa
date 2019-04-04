<?php
namespace App\Models\Repositories;

interface ContactRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Return the specified contacts.
     *
     * @param $userId
     * @param $contactId
     * @return mixed
     */
    public function findByUserId($userId, $contactId);

    /**
     * Return the contacts by email.
     *
     * @param $email
     * @return mixed
     */
    public function findByEmail($email);

    /**
     * Return user's contacts.
     *
     * @param $userId
     * @param $onlyAvailable
     * @return mixed
     */
    public function getByUserId($userId, $onlyAvailable = false);

    /**
     * Store by given data
     *
     * @param $data
     * @return mixed
     */
    public function store($data);

    /**
     * Delete the specified contacts
     *
     * @param $contactId
     * @param $userId
     * @return mixed
     */
    public function delete($contactId, $userId);

    /**
     * Send verify request and record send_verified_at.
     *
     * @param $contactId
     * @return mixed
     */
    public function sendVerifyRequest($contactId);

    /**
     * Record the email_verified_at.
     *
     * @param $id
     * @return mixed
     */
    public function verify($id);
}
