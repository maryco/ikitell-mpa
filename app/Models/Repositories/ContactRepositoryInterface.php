<?php
namespace App\Models\Repositories;

use App\Models\Entities\Contact;

interface ContactRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Return the specified contacts.
     *
     * @param int $userId
     * @param int $contactId
     * @return ?Contact
     */
    public function findByUserId(int $userId, int $contactId): ?Contact;

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
     * @return Contact
     */
    public function store($data): Contact;

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
     * @param int $contactId
     * @return bool
     */
    public function sendVerifyRequest(int $contactId): bool;

    /**
     * Record the email_verified_at.
     *
     * @param int $id
     * @return ?Contact
     */
    public function verify(int $id): ?Contact;
}
